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

jimport('joomla.plugin.plugin');
define('JCOMMENTS_SITE', JPATH_ROOT . '/components/com_jcomments');
define('JCOMMENTS_HELPERS', JCOMMENTS_SITE . '/helpers');
include_once(JCOMMENTS_HELPERS . '/system.php');

/**
 * System plugin for attaching JComments CSS & JavaScript to HEAD tag
 */
class plgSystemJComments extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array $config  An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if (!isset($this->params)) {
			$this->params = new JRegistry('');
		}

		// small hack to allow CAPTCHA display even if any notice or warning occurred
		$app = JFactory::getApplication('site');
		$option = $app->input->get('option');
		$task = $app->input->get('task');
		if ($option == 'com_jcomments' && $task == 'captcha') {
			@ob_start();
		}

		if (isset($_REQUEST['jtxf'])) {
			if ($this->params->get('disable_error_reporting', 0) == 1) {
				// turn off all error reporting for AJAX call
				@error_reporting(E_NONE);
			}
		}
	}





	function onAfterRender()
	{
		$app = JFactory::getApplication();
		if ($this->params->get('clear_rss', 0) == 1) {
			$option = $app->input->get('option');
			if ($option == 'com_content') {
				$document = JFactory::getDocument();
				if ($document->getType() == 'feed') {
					if (version_compare(JVERSION, '4.0', 'lt')){
						$buffer = JResponse::getBody();
					} else {
						$buffer = $app->getBody();
					}
					$buffer = preg_replace('#{jcomments\s+(off|on|lock)}#is', '', $buffer);
					if (version_compare(JVERSION, '4.0', 'lt')){
						JResponse::setBody($buffer);
					} else {
						$app->setBody($buffer);
					}
				}
			}
		}

		if ((defined('JCOMMENTS_CSS') || defined('JCOMMENTS_JS')) && !defined('JCOMMENTS_SHOW')) {
			if ($app->getName() == 'site') {
				if (version_compare(JVERSION, '4.0', 'lt')){
					$buffer = JResponse::getBody();
				} else {
					$buffer = $app->getBody();
				}
				$regexpJS = '#(\<script(\stype=\"text\/javascript\")? src="[^\"]*\/com_jcomments\/[^\>]*\>\<\/script\>[\s\r\n]*?)#ismU';
				$regexpCSS = '#(\<link rel="stylesheet" href="[^\"]*\/com_jcomments\/[^>]*>[\s\r\n]*?)#ismU';

				$jcommentsTestJS = '#(JCommentsEditor|new JComments)#ismU';
				$jcommentsTestCSS = '#(comment-link|jcomments-links)#ismU';

				$jsFound = preg_match($jcommentsTestJS, $buffer);
				$cssFound = preg_match($jcommentsTestCSS, $buffer);

				if (!$jsFound) {
					// remove JavaScript if JComments isn't loaded
					$buffer = preg_replace($regexpJS, '', $buffer);
				}

				if (!$cssFound && !$jsFound) {
					// remove CSS if JComments isn't loaded
					$buffer = preg_replace($regexpCSS, '', $buffer);
				}

				if ($buffer != '') {
					if (version_compare(JVERSION, '4.0', 'lt')){
						JResponse::setBody($buffer);
					} else {
						$app->setBody($buffer);
					}
				}
			}
		}

		return true;
	}

	function onAfterRoute()
	{
		$legacyFile = JPATH_ROOT . '/components/com_jcomments/jcomments.legacy.php';

		if (!is_file($legacyFile)) {
			return;
		}

		include_once($legacyFile);

		$app = JFactory::getApplication('site');
		$app->getRouter();
		$document = JFactory::getDocument();

		if ($document->getType() == 'html') {
			if (JCommentsSystemPluginHelper::isAdmin($app)) {
				$document->addStyleSheet(JURI::root(true) . '/administrator/components/com_jcomments/assets/css/icon.css?v=2');
				JFactory::getLanguage()->load('com_jcomments.sys', JPATH_ROOT . '/administrator', 'en-GB', true);
				if (version_compare(JVERSION, '4.0', 'lt')){
					$option = JAdministratorHelper::findOption();
				} else {
					$option = $app->findOption();
				}
				$task = $app->input->get('task');
				//$type = $app->input->post('type', '', 'post');

				// remove comments if content item deleted from trash
				if ($option == 'com_trash' && $task == 'delete' && $type == 'content') {
					$cid = $app->input->post->get('cid', array(), 'array');
					JArrayHelper::toInteger($cid, array(0));
					include_once(JPATH_ROOT . '/components/com_jcomments/jcomments.php');
					JCommentsModel::deleteComments($cid, 'com_content');
				}
			} else {
				$option = $app->input->get('option');

				if ($option == 'com_content' || $option == 'com_alphacontent' || $option == 'com_multicategories') {
					include_once(JPATH_ROOT . '/components/com_jcomments/jcomments.class.php');
					include_once(JPATH_ROOT . '/components/com_jcomments/helpers/system.php');

					// include JComments CSS
					if ($this->params->get('disable_template_css', 0) == 0) {
						$document->addStyleSheet(JCommentsSystemPluginHelper::getCSS());
						$language = JFactory::getLanguage();
						if ($language->isRTL()) {
							$rtlCSS = JCommentsSystemPluginHelper::getCSS(true);
							if ($rtlCSS != '') {
								$document->addStyleSheet($rtlCSS);
							}
						}
					}

					if (!defined('JCOMMENTS_CSS')) {
						define('JCOMMENTS_CSS', 1);
					}

					// include JComments JavaScript library
					$document->addScript(JCommentsSystemPluginHelper::getCoreJS());
					if (!defined('JOOMLATUNE_AJAX_JS')) {
						$document->addScript(JCommentsSystemPluginHelper::getAjaxJS());
						define('JOOMLATUNE_AJAX_JS', 1);
					}

					if (!defined('JCOMMENTS_JS')) {
						define('JCOMMENTS_JS', 1);
					}
				}
			}
		}
	}


	function onJCommentsShow($object_id, $object_group, $object_title)
	{
		$coreFile = JPATH_ROOT . '/components/com_jcomments/jcomments.php';

		if (is_file($coreFile)) {
			include_once($coreFile);
			echo JComments::show($object_id, $object_group, $object_title);
		}
	}

	function onJCommentsCount($object_id, $object_group)
	{
		$coreFile = JPATH_ROOT . '/components/com_jcomments/jcomments.php';

		if (is_file($coreFile)) {
			include_once($coreFile);
			echo JComments::getCommentsCount($object_id, $object_group);
		}
	}
}