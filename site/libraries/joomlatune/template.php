<?php
/**
 *
 * Template class
 *
 * @version 1.0
 * @package JoomlaTune.Framework
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

/**
 * JoomlaTune base template class
 *
 * @abstract
 *
 */
class JoomlaTuneTemplate
{
	/**
	 * Class constructor
	 *
	 */
	function __construct()
	{
		$this->_vars = array();
	}

	/**
	 * Render template into string
	 *
	 * @abstract Implement in child classes
	 * @return string
	 */
	function render()
	{
	}

	/**
	 * Sets global variables
	 *
	 * @param array $value array list of global variables
	 * @return void
	 */
	function setGlobalVars(&$value)
	{
		$this->_globals =& $value;
	}

	/**
	 * Fetches and returns a given variable.
	 *
	 * @param string $name Variable name
	 * @param mixed $default Default value if the variable does not exist
	 * @return mixed Requested variable
	 */
	function getVar($name, $default = null)
	{
		if (isset($this->_vars[$name])) {
			// fetch variable from local variables list
			return $this->_vars[$name];
		} else {
			if (isset($this->_globals[$name])) {
				// fetch variable from global variables list
				return $this->_globals[$name];
			} else {
				// return default value
				return $default;
			}
		}
	}

	/**
	 * Set a template variable, creating it if it doesn't exist
	 *
	 * @param string $name The name of the variable
	 * @param mixed $value The value of the variable
	 * @return void
	 */
	function setVar($name, $value)
	{
		$this->_vars[$name] = $value;
	}
}

/**
 *
 * JoomlaTune template renderer class
 *
 */
class JoomlaTuneTemplateRender
{

	var $_root = null;
	var $_default = null;
	var $_uri = null;
	var $_globals = null;
	var $_templates = null;

	/**
	 * Class constructor
	 */
	function __construct()
	{
		$this->_globals = array();
		$this->_templates = array();

		//set root template directory
		$this->setRoot(dirname(__FILE__) . '/tpl');
	}

	/**
	 * Returns a reference to the global JoomlaTuneTemplateRender object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return JoomlaTuneTemplateRender A template object
	 */
	public static function getInstance()
	{
		static $instance = null;

		if (!is_object($instance)) {
			$instance = new JoomlaTuneTemplateRender();
		}
		return $instance;
	}

	/**
	 * Sets root base for the template
	 *
	 * The parameter depends on the reader you are using.
	 *
	 * @param string $path root base of the templates
	 * @return void
	 */
	function setRoot($path)
	{
		$this->_root = $path ? $path : dirname(__FILE__) . '/tpl';
	}

	/**
	 * Gets name of root base for the templates
	 *
	 * @return string
	 */
	function getRoot()
	{
		return $this->_root;
	}

	/**
	 * Sets default base for the template
	 *
	 * @param string $path default base of the templates
	 * @return void
	 */
	function setDefaultRoot($path)
	{
		$this->_default = $path ? $path : $this->getRoot();
	}

	/**
	 * Gets name of default base for the templates
	 *
	 * @return string
	 */
	function getDefaultRoot()
	{
		return $this->_default;
	}

	/**
	 * Sets base url for the template images and css
	 *
	 * @param string $uri The base url of the templates
	 * @return void
	 */
	function setBaseURI($uri)
	{
		$this->_uri = $uri;
	}

	/**
	 * Gets name of root base for the templates
	 *
	 * @return string
	 */
	function getBaseURI()
	{
		return $this->_uri;
	}

	/**
	 * Load template class
	 *
	 * @param string $template name of the template
	 * @return boolean
	 */
	function load($template)
	{
		$templateFileName = $this->getRoot() . '/' . $template . '.php';

		if (!is_file($templateFileName)) {
			$templateFileName = $this->getDefaultRoot() . '/' . $template . '.php';
		}

		if (is_file($templateFileName)) {
			ob_start();
			include_once($templateFileName);
			ob_end_clean();

			$templateClass = 'jtt_' . $template;

			if (!class_exists($templateClass)) {
				$this->raiseError('Template class not found in: ' . $template);
				return false;
			}

			$tmpl = new $templateClass;
			if (!($tmpl instanceof JoomlaTuneTemplate)) {
				unset($tmpl);
				$this->raiseError('Incorrect template: ' . $template);
				return false;
			}

			ob_start();
			$tmpl->setGlobalVars($this->_globals);
			$this->_templates[$template] =& $tmpl;
			ob_end_clean();

			return true;
		}
		return false;
	}

	/**
	 * Adds a global variable
	 *
	 * Global variables are valid in all templates of this object.
	 *
	 * @param string $name name of the global variable
	 * @param mixed $value value of the variable
	 * @return void
	 * @see addVar()
	 */
	function addGlobalVar($name, $value)
	{
		$this->_globals[strtolower($name)] = ( string )$value;
	}

	/**
	 * Add a variable to a template
	 *
	 * @param string $template name of the template
	 * @param string $name name of the variable
	 * @param mixed $value value of the variable
	 * @return void
	 * @see addGlobalVar()
	 */
	function addVar($template, $name, $value)
	{
		$this->_templates[$template]->_vars[$name] = $value;
	}

	/**
	 * Add a object variable to a template
	 *
	 * @param string $template name of the template
	 * @param string $name name of the variable
	 * @param mixed $value value of the variable
	 * @return void
	 * @see addVar(), addGlobalVar()
	 */
	function addObject($template, $name, $value)
	{
		$this->_templates[$template]->_vars[$name] = $value;
	}

	/**
	 * Fetches and returns a given variable from template.
	 *
	 * @param string $template name of the template
	 * @param string $name name of the variable
	 * @return mixed
	 */
	function getVar($template, $name)
	{
		if (!$this->exists($template)) {
			$this->raiseError('Unknown template: ' . $template);
			return null;
		}

		ob_start();
		$result = $this->_templates[$template]->getVar($name);
		ob_end_clean();

		return $result;
	}

	/**
	 * Renders template and return result as string
	 *
	 * @param string $template name of the template
	 * @return string
	 * @see displayTemplate()
	 */
	function renderTemplate($template)
	{
		if (!$this->exists($template)) {
			$this->raiseError('Unknown template: ' . $template);
			return null;
		}

		ob_start();
		$this->_templates[$template]->render();
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	/**
	 * Renders template and displays output
	 *
	 * @param string $template name of the template
	 * @return void
	 * @see renderTemplate()
	 */
	function displayTemplate($template)
	{
		echo $this->renderTemplate($template);
	}

	/**
	 * Frees a template
	 *
	 * All memory consumed by the template will be freed.
	 *
	 * @param string $template name of the template
	 * @return void
	 * @see freeAllTemplates()
	 */
	function freeTemplate($template)
	{
		unset($this->_templates[$template]);
	}

	/**
	 * Frees all templates
	 *
	 * All memory consumed by the templates will be freed.
	 *
	 * @return void
	 * @see freeTemplate()
	 */
	function freeAllTemplates()
	{
		$this->_templates = array();
		$this->_globals = array();
	}

	/**
	 * Checks if template exists
	 *
	 * @param string $name name of the template
	 * @return boolean true, if template exists (loaded), false otherwise
	 * @see load()
	 */
	function exists($name)
	{
		return isset($this->_templates[$name]);
	}

	/**
	 * Displays error-message and die
	 *
	 * @param string $message error message
	 * @return void
	 */
	function raiseError($message)
	{
		die('JoomlaTuneTemplateError: ' . $message);
	}
}