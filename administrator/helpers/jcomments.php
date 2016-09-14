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

class JCommentsHelper
{
	public static function addSubmenu($vName)
	{
		if (version_compare(JVERSION, '3.0', 'ge')) {
			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_COMMENTS'),
				'index.php?option=com_jcomments&view=comments',
				$vName == 'comments'
			);
			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_SETTINGS'),
				'index.php?option=com_jcomments&view=settings',
				$vName == 'settings'
			);
			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_SMILIES'),
				'index.php?option=com_jcomments&view=smilies',
				$vName == 'smilies'
			);
			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_SUBSCRIPTIONS'),
				'index.php?option=com_jcomments&view=subscriptions',
				$vName == 'subscriptions'
			);
			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_CUSTOM_BBCODE'),
				'index.php?option=com_jcomments&view=custombbcodes',
				$vName == 'custombbcodes'
			);
			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_BLACKLIST'),
				'index.php?option=com_jcomments&view=blacklists',
				$vName == 'blacklists'
			);
			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_MAILQ'),
				'index.php?option=com_jcomments&view=mailq',
				$vName == 'mailq'
			);

			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_IMPORT'),
				'index.php?option=com_jcomments&view=import',
				$vName == 'import'
			);
			JHtmlSidebar::addEntry(
				JText::_('A_SUBMENU_ABOUT'),
				'index.php?option=com_jcomments&view=about',
				$vName == 'about'
			);
		} elseif (version_compare(JVERSION, '2.5', 'ge')) {
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_COMMENTS'),
				'index.php?option=com_jcomments&view=comments',
				$vName == 'comments'
			);
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_SETTINGS'),
				'index.php?option=com_jcomments&view=settings',
				$vName == 'settings'
			);
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_SMILIES'),
				'index.php?option=com_jcomments&view=smilies',
				$vName == 'smilies'
			);
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_SUBSCRIPTIONS'),
				'index.php?option=com_jcomments&view=subscriptions',
				$vName == 'subscriptions'
			);
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_CUSTOM_BBCODE'),
				'index.php?option=com_jcomments&view=custombbcodes',
				$vName == 'custombbcodes'
			);
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_BLACKLIST'),
				'index.php?option=com_jcomments&view=blacklists',
				$vName == 'blacklists'
			);
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_MAILQ'),
				'index.php?option=com_jcomments&view=mailq',
				$vName == 'mailq'
			);
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_IMPORT'),
				'index.php?option=com_jcomments&view=import',
				$vName == 'import'
			);
			JSubMenuHelper::addEntry(
				JText::_('A_SUBMENU_ABOUT'),
				'index.php?option=com_jcomments&view=about',
				$vName == 'about'
			);
		}
	}

	public static function getActions()
	{
		$user = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_jcomments';

		$actions = JAccess::getActionsFromFile(JPATH_COMPONENT_ADMINISTRATOR . '/access.xml');
		if (is_array($actions)) {
			foreach ($actions as $action) {
				$result->set($action->name, $user->authorise($action->name, $assetName));
			}
		} else {
			$actions = array('core.admin', 'core.manage', 'core.create', 'core.delete', 'core.edit', 'core.edit.state');
			foreach ($actions as $action) {
				$result->set($action, $user->authorise($action, $assetName));
			}
		}

		return $result;
	}

	public static function getGroups()
	{
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN ' . $db->quoteName('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' GROUP BY a.id, a.title, a.lft, a.rgt' .
			' ORDER BY a.lft ASC'
		);

		try {
			$options = $db->loadObjectList();
		} catch (RuntimeException $e) {
			JError::raiseNotice(500, $e->getMessage());

			return null;
		}

		foreach ($options as &$option) {
			$option->text = str_repeat('- ', $option->level) . $option->text;
		}

		return $options;
	}

	public static function getSmiliesPath()
	{
		$config = JCommentsFactory::getConfig();

		$smiliesPath = $config->get('smilies_path', 'components/com_jcomments/images/smilies/');
		$smiliesPath = str_replace(array('//', '\\\\'), '/', $smiliesPath);
		$smiliesPath = trim($smiliesPath, '\\/') . '/';

		return $smiliesPath;
	}
}