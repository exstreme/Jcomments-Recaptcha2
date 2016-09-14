<?php
/**
 * JComments plugin for Joomla com_weblinks component
 *
 * @version 1.4
 * @package JComments
 * @author Tommy Nilsson (tommy@architechtsoftomorrow.com)
 * @copyright (C) 2011 Tommy Nilsson (http://www.architechtsoftomorrow.com)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_weblinks extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT title, id FROM #__categories WHERE section = "com_weblinks" and id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT alias FROM #__categories WHERE section = "com_weblinks" and id = ' . $id );
		$alias = $db->loadResult();
		
		$link = 'index.php?option=com_weblinks&view=category&id='. $id.':'.$alias;

		require_once(JPATH_SITE.DS.'includes'.DS.'application.php');

		$component = JComponentHelper::getComponent('com_weblinks');
		$menus = JApplication::getMenu('site');
		$items = $menus->getItems('componentid', $component->id);

		if (count($items)) {
			$link .= "&Itemid=" . $items[0]->id;
		}

		$link = JRoute::_($link);

		return $link;
	}
}