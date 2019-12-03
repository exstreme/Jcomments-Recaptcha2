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
 * JComments Multilingual support
 */
class JCommentsMultilingual
{
	public static function isEnabled()
	{
		static $enabled = null;

		if (!isset($enabled)) {
			$app = JFactory::getApplication();

			if (JCommentsSystemPluginHelper::isSite($app)) { 
				$enabled = $app->getLanguageFilter();
			}
			 else {
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select('enabled');
				$query->from($db->quoteName('#__extensions'));
				$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
				$query->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
				$query->where($db->quoteName('element') . ' = ' . $db->quote('languagefilter'));
				$db->setQuery($query);

				$enabled = $db->loadResult();
			}

			JFactory::getConfig()->set('multilingual_support', $enabled);

			if ($enabled) {
				$enabled = JCommentsFactory::getConfig()->get('multilingual_support', $enabled);
			}
		}

		return $enabled;
	}

	public static function getLanguage()
	{
		static $language = null;

		if (!isset($language)) {
			$language = JFactory::getLanguage()->getTag();
		}

		return $language;
	}

	public static function getLanguages()
	{
		// TODO: JoomFish support
		return JLanguageHelper::getLanguages();
	}
}