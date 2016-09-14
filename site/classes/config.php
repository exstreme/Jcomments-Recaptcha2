<?php
/**
 * JComments - Joomla Comment System
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

/**
 * Configuration loader class
 */
class JCommentsCfg
{
	/**
	 * Associative array of configuration variables
	 * @var array
	 */
	var $_params = array();
	/**
	 * Last loaded language name
	 * @var string
	 */
	public $_current = '';

	/**
	 * Returns a reference to a JCommentsCfg object.
	 *
	 * @param string $language The language code.
	 * @param string $component The component name.
	 * @return JCommentsCfg
	 */
	public static function getInstance($language = '', $component = '')
	{
		static $instance = null;

		if ($language == '' && JCommentsMultilingual::isEnabled()) {
			$language = JCommentsMultilingual::getLanguage();
		}

		if (!is_object($instance)) {
			$instance = new JCommentsCfg();
			$instance->load($language, $component);
		} else {
			if ($language != $instance->_current && $instance->_current == '') {
				if ($language != '') {
					$instance->load($language, $component);
				}
			}
		}

		return $instance;
	}

	/**
	 * Returns params names
	 *
	 * @return array
	 */
	public function getKeys()
	{
		return array_keys($this->_params);
	}

	public function get($name, $default = '')
	{
		$legacy = array('smiles' => 'smilies', 'smiles_path' => 'smilies_path', 'enable_smiles' => 'enable_smilies');
		if (isset($legacy[$name])) {
			$name = $legacy[$name];
		}

		return (isset($this->_params[$name]) && $this->_params[$name] !== null && $this->_params[$name] !== '') ? $this->_params[$name] : $default;
	}

	/**
	 * Fetches and returns a given variable as integer.
	 * This is currently only a proxy function for get().
	 *
	 * @param string $name        Variable name
	 * @param int $default    Default value if the variable does not exist
	 * @return int    Requested variable
	 */
	public function getInt($name, $default = 0)
	{
		return (int)$this->get($name, $default);
	}

	/**
	 * Sets a configuration variable
	 *
	 * @param    string $name The name of the variable
	 * @param    mixed $value The value of the variable to set
	 * @return    void
	 */
	public function set($name, $value)
	{
		$this->_params[$name] = $value;
	}

	/**
	 * Checks if value exists in list
	 *
	 * @param    string $name The name of the variable
	 * @param    mixed $value The value of the variable to set
	 * @return    boolean
	 */
	public function check($name, $value)
	{
		$v = $this->get($name);
		$va = explode(',', $v);
		if (is_array($va)) {
			return (in_array($value, $va));
		}

		return false;
	}

	/**
	 * Sets a configuration variable
	 *
	 * @param    string $language The language code to use.
	 * @param    mixed $component The component name to use
	 * @return    array An array of loaded configuration variables
	 */
	public static function _load($language = '', $component = '')
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jcomments_settings'));
		$query->where($db->quoteName('lang') . ' = ' . $db->Quote($db->escape($language, true)));
		$query->where($db->quoteName('component') . ' = ' . $db->Quote($db->escape($component, true)));
		$db->setQuery($query);

		$data = $db->loadObjectList();

		if (count($data) == 0) {
			$data = self::_load();
		}

		return $data;
	}

	/**
	 * Load configuration from DB and stores it into field _params
	 *
	 * @param string $language The language to use.
	 * @param string $component The component name.
	 * @return void
	 */
	public function load($language = '', $component = '')
	{
		$cache = JFactory::getCache('com_jcomments');
		$params = (array)$cache->get('JCommentsCfg::_load', array($language, $component));

		foreach ($params as $param) {
			$this->_params[$param->name] = $param->value;
		}

		if ($this->get('smilies_path') == '') {
			$this->set('smilies_path', '/components/com_jcomments/images/smilies/');
		}

		if ($this->get('enable_notification') == 0 || $this->check('notification_type', 2) == false) {
			$this->set('can_report', '');
		}

		if (!extension_loaded('gd') || !function_exists('imagecreatefrompng')) {
			if ($this->get('captcha_engine', 'kcaptcha') != 'recaptcha') {
				$this->set('enable_captcha', '');
			}
		}

		$this->_current = $language;

		unset($params);
	}
}