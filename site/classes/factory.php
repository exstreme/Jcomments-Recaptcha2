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
 * JComments Factory class
 */
class JCommentsFactory
{
	/**
	 * Returns a reference to the global {@link JApplication} object, only creating it if it does not already exist.
	 *
	 * @param mixed $id A client identifier or name.
	 * @param array $config An optional associative array of configuration settings.
	 * @param string $prefix
	 *
	 * @deprecated  3.0  Use JFactory::getApplication() instead.
	 * @return JApplication
	 */
	public static function getApplication($id = null, $config = array(), $prefix = 'J')
	{
		return JFactory::getApplication($id, $config, $prefix);
	}

	/**
	 * Returns a reference to the global {@link JDocument} object, only creating it if it does not already exist.
	 *
	 * @deprecated  3.0  Use JFactory::getDocument() instead.
	 * @return JDocument
	 */
	public static function getDocument()
	{
		return JFactory::getDocument();
	}

	/**
	 * Returns a reference to the global {@link JUser} object, only creating it if it does not already exist.
	 *
	 * @param int $id An user identifier
	 *
	 * @deprecated  3.0  Use JFactory::getUser() instead.
	 * @return JUser
	 */
	public static function getUser($id = null)
	{
		return JFactory::getUser($id);
	}

	/**
	 * Returns a reference to the global {@link JDatabase} object, only creating it if it does not already exist.
	 *
	 * @deprecated  3.0  Use JFactory::getDbo() instead.
	 * @return JDatabase
	 */
	public static function getDBO()
	{
		return JFactory::getDBO();
	}

	/**
	 * Returns the global {@link JLanguage} object, only creating it if it does not already exist.
	 *
	 * @deprecated  3.0  Use JFactory::getLanguage() instead.
	 * @return JLanguage object
	 */
	public static function getLanguage()
	{
		return JFactory::getLanguage();
	}

	/**
	 * Returns a reference to the global {@link JCache} object, only creating it if it does not already exist.
	 *
	 * @param string $group The cache group name
	 * @param string $handler The handler to use
	 * @param string $storage The storage method
	 *
	 * @deprecated  3.0  Use JFactory::getCache() instead.
	 * @return JCache A function cache object
	 */
	public static function getCache($group = '', $handler = 'callback', $storage = null)
	{
		return JFactory::getCache($group, $handler, $storage);
	}

	/**
	 * Gets the date as in MySQL datetime format
	 *
	 * @param $time mixed|string The initial time for the JDate object
	 * @param int $tzOffset The timezone offset.
	 *
	 * @deprecated  3.0  Use JFactory::getDate() instead.
	 * @return string A date in MySQL datetime format
	 */
	public static function getDate($time = 'now', $tzOffset = 0)
	{
		return JFactory::getDate($time);
	}

	/**
	 * Returns a reference to the global {@link JCommentsSmiles} object, only creating it if it does not already exist.
	 *
	 * @deprecated  3.0  Use JCommentsFactory::getSmilies instead
	 * @return JCommentsSmilies
	 */
	public static function getSmiles()
	{
		return JCommentsFactory::getSmilies();
	}

	public static function getSmilies()
	{
		static $instance = null;

		if (!is_object($instance)) {
			$instance = new JCommentsSmilies();
		}

		return $instance;
	}

	/**
	 * Returns a reference to the global {@link JCommentsBBCode} object, only creating it if it does not already exist.
	 *
	 * @return JCommentsBBCode
	 */
	public static function getBBCode()
	{
		static $instance = null;

		if (!is_object($instance)) {
			$instance = new JCommentsBBCode();
		}

		return $instance;
	}

	/**
	 * Returns a reference to the global {@link JCommentsCustomBBCode} object, only creating it if it does not already exist.
	 *
	 * @return JCommentsCustomBBCode
	 */
	public static function getCustomBBCode()
	{
		static $instance = null;

		if (!is_object($instance)) {
			$instance = new JCommentsCustomBBCode();
		}

		return $instance;
	}

	/**
	 * Returns a reference to the global {@link JEventDispatcher} object, only creating it if it does not already exist.
	 *
	 * @return JEventDispatcher
	 */
	public static function getEventDispatcher()
	{
		if (version_compare(JVERSION, '3.0', 'ge')) {
			$dispatcher = JEventDispatcher::getInstance();
		} else {
			$dispatcher = JDispatcher::getInstance();
		}

		return $dispatcher;
	}

	/**
	 * Returns a reference to the global {@link JCommentsCfg} object, only creating it if it does not already exist.
	 *
	 * @param $language string Language
	 *
	 * @return JCommentsCfg
	 */
	public static function getConfig($language = '')
	{
		return JCommentsCfg::getInstance($language);
	}

	/**
	 * Returns a reference to the global {@link JoomlaTuneTemplateRender} object, only creating it if it does not already exist.
	 *
	 * @param int $object_id
	 * @param string $object_group
	 * @param bool $needThisUrl
	 *
	 * @return JoomlaTuneTemplateRender
	 */
	public static function getTemplate($object_id = 0, $object_group = 'com_content', $needThisUrl = true)
	{
		global $Itemid;

		ob_start();

		$app = JFactory::getApplication();
		$language = JFactory::getLanguage();
		$config = JCommentsFactory::getConfig();

		$templateName = $config->get('template');

		if (empty($templateName)) {
			$templateName = 'default';
			$config->set('template', $templateName);
		}

		include_once(JCOMMENTS_LIBRARIES . '/joomlatune/template.php');

		$templateDefaultDirectory = JCOMMENTS_SITE . '/tpl/' . $templateName;
		$templateDirectory = $templateDefaultDirectory;
		$templateUrl = JURI::root() . 'components/com_jcomments/tpl/' . $templateName;

		$templateOverride = JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/com_jcomments/' . $templateName;
		if (is_dir($templateOverride)) {
			$templateDirectory = $templateOverride;
			$templateUrl = JURI::root() . 'templates/' . $app->getTemplate() . '/html/com_jcomments/' . $templateName;
		}

		$tmpl = JoomlaTuneTemplateRender::getInstance();
		$tmpl->setRoot($templateDirectory);
		$tmpl->setDefaultRoot($templateDefaultDirectory);
		$tmpl->setBaseURI($templateUrl);
		$tmpl->addGlobalVar('siteurl', JURI::root());
		$tmpl->addGlobalVar('charset', 'utf-8');
		$tmpl->addGlobalVar('ajaxurl', JCommentsFactory::getLink('ajax', $object_id, $object_group));
		$tmpl->addGlobalVar('smilesurl', JCommentsFactory::getLink('smilies', $object_id, $object_group));

		if ($config->getInt('enable_rss') == 1) {
			$tmpl->addGlobalVar('rssurl', JCommentsFactory::getLink('rss', $object_id, $object_group));
		}

		$tmpl->addGlobalVar('template', $templateName);
		$tmpl->addGlobalVar('template_url', $templateUrl);
		$tmpl->addGlobalVar('itemid', $Itemid ? $Itemid : 1);
		$tmpl->addGlobalVar('direction', $language->isRTL() ? 'rtl' : 'ltr');

		$lang = $language->getTag();
		$domain = ($lang == 'ru-RU' || $lang == 'uk-UA' || $lang == 'be-BY') ? 'ru' : 'com';

		$tmpl->addGlobalVar('support', '<a href="http://www.joomlatune.' . $domain . '" title="JComments" target="_blank" rel="noopener">JComments</a>');
		$tmpl->addGlobalVar('comment-object_id', $object_id);
		$tmpl->addGlobalVar('comment-object_group', $object_group);

		if ($needThisUrl == true) {
			$tmpl->addGlobalVar('thisurl', JCommentsObjectHelper::getLink($object_id, $object_group, $lang));
		}

		ob_end_clean();

		return $tmpl;
	}

	/**
	 * Returns a reference to the global {@link JCommentsACL} object,
	 * only creating it if it doesn't already exist.
	 *
	 * @return JCommentsACL
	 */
	public static function getACL()
	{
		static $instance = null;

		if (!is_object($instance)) {
			$instance = new JCommentsACL();
		}

		return $instance;
	}

	/**
	 * Returns a reference to the global {@link JoomlaTuneAjaxResponse} object,
	 * only creating it if it doesn't already exist.
	 *
	 * @return JoomlaTuneAjaxResponse
	 */
	public static function getAjaxResponse()
	{
		static $instance = null;

		if (!is_object($instance)) {
			$instance = new JoomlaTuneAjaxResponse('utf-8');
		}

		return $instance;
	}

	public static function getCmdHash($cmd, $id)
	{
		return md5($cmd . $id . JPATH_ROOT . JFactory::getApplication()->getCfg('secret'));
	}

	public static function getCmdLink($cmd, $id)
	{
		$hash = JCommentsFactory::getCmdHash($cmd, $id);
		$liveSite = trim(str_replace('/administrator', '', JURI::root()), '/');
		$liveSite = str_replace(JURI::root(true), '', $liveSite);
		$link = $liveSite . JRoute::_('index.php?option=com_jcomments&amp;task=cmd&amp;cmd=' . $cmd . '&amp;id=' . $id . '&amp;hash=' . $hash . '&amp;format=raw');

		return $link;
	}

	/**
	 * Returns link for canceling the user's subscription for notifications about new comments
	 *
	 * @param $hash
	 *
	 * @return string
	 * @deprecated    Use JCommentsNotificationHelper::getUnsubscribeLink instead.
	 */
	public static function getUnsubscribeLink($hash)
	{
		return JCommentsNotificationHelper::getUnsubscribeLink($hash);
	}

	public static function getLink($type = 'ajax', $object_id = 0, $object_group = '', $lang = '')
	{
		$app = JFactory::getApplication();
		$config = JCommentsFactory::getConfig();

		switch ($type) {
			case 'rss':
				$link = 'index.php?option=com_jcomments&amp;task=rss&amp;object_id=' . $object_id . '&amp;object_group=' . $object_group . '&amp;format=raw';
				if (JCommentsSystemPluginHelper::isAdmin($app)) {
					$link = JURI::root(true) . '/' . $link;
				} else {
					$link = JRoute::_($link);
				}

				return $link;
				break;

			case 'noavatar':
				return JURI::root() . 'components/com_jcomments/images/no_avatar.png';
				break;

			case 'smiles':
			case 'smilies':
				return JUri::root(true) . '/' . trim(str_replace('\\', '/', $config->get('smilies_path')), '/') . '/';
				break;

			case 'captcha':
				mt_srand((double)microtime() * 1000000);
				$random = mt_rand(10000, 99999);

				return JRoute::_('index.php?option=com_jcomments&amp;task=captcha&amp;format=raw&amp;ac=' . $random);
				break;

			case 'ajax':
				$config = JCommentsFactory::getConfig();

				// support alternate language files
				$lsfx = ($config->get('lsfx') != '') ? ('&amp;lsfx=' . $config->get('lsfx')) : '';

				// support additional param for multilingual sites
				if (!empty($lang)) {
					$lang = '&amp;lang=' . $lang;
				}

				$link = JRoute::_('index.php?option=com_jcomments&amp;tmpl=component' . $lang . $lsfx);

				// fix to prevent cross-domain ajax call
				if (isset($_SERVER['HTTP_HOST'])) {
					$httpHost = (string)$_SERVER['HTTP_HOST'];
					if (strpos($httpHost, '://www.') !== false && strpos($link, '://www.') === false) {
						$link = str_replace('://', '://www.', $link);
					} else if (strpos($httpHost, '://www.') === false && strpos($link, '://www.') !== false) {
						$link = str_replace('://www.', '://', $link);
					}
				}

				return $link;
				break;

			default:
				return '';
				break;
		}
	}

	/**
	 * Convert relative link to absolute (add http:// and site url)
	 *
	 * @param string $link The relative url.
	 *
	 * @return string
	 */
	public static function getAbsLink($link)
	{
		if (version_compare(JVERSION, '4.0', 'ge')) {
			$uri = JUri::getInstance();
		} else {
			$uri = JFactory::getURI();
		}

		$url = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		if (strpos($link, $url) === false) {
			$link = $url . $link;
		}

		return $link;
	}
}
