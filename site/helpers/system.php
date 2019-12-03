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
 * JComments System Plugin Helper
 */
class JCommentsSystemPluginHelper
{
	public static function getBaseUrl()
	{
		return JURI::root(true);
	}

	public static function getCoreJS()
	{
		return JURI::root(true) . '/components/com_jcomments/js/jcomments-v2.3.js?v=12';
	}

	public static function getAjaxJS()
	{
		return JURI::root(true) . '/components/com_jcomments/libraries/joomlatune/ajax.js?v=4';
	}

	public static function getCSS($isRTL = false, $template = '')
	{
		$app = JFactory::getApplication('site');

		if (empty($template)) {
			$config = JCommentsCfg::getInstance();
			$template = $config->get('template');
		}

		$cssName = $isRTL ? 'style_rtl.css' : 'style.css';
		$cssFile = $cssName . '?v=3002';

		$cssPath = JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_jcomments/'.$template.'/'.$cssName;
		$cssUrl = JURI::root(true).'/templates/'.$app->getTemplate().'/html/com_jcomments/'.$template.'/'.$cssFile;

		if (!is_file($cssPath)) {
			$cssPath = JPATH_SITE . '/components/com_jcomments/tpl/'.$template.'/'.$cssName;
			$cssUrl = JURI::root(true) . '/components/com_jcomments/tpl/'.$template.'/'.$cssFile;
			if ($isRTL && !is_file($cssPath)) {
				$cssUrl = '';
			}
		}

		return $cssUrl;
	}
	
	public static function isAdmin($app) {
		if (version_compare(JVERSION, '4.0', 'lt')){
			return $app->isAdmin();
		} else {
			return $app->isClient('administrator');
		}
	}
	public static function isSite($app) {
		if (version_compare(JVERSION, '4.0', 'lt')){
			return $app->isSite();
		} else {
			return $app->isClient('site');
		}
	}
}