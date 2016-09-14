<?php
/**
 * JComments plugin for DocMan objects support
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_docman extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT dmname, id FROM #__docman WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		static $_Itemid = null;

		if (!isset($_Itemid)) {
			$needles = array('gid' => (int) $id);
			if ($item = self::_findItem($needles)) {
				$_Itemid = $item->id;
			} else {
				$_Itemid = '';
			}
		}

		include_once(JPATH_SITE.DS.'includes'.DS.'application.php');

		$link = 'index.php?option=com_docman&task=doc_details&gid=' . $id;

		if ($_Itemid != '') {
			$link .= '&Itemid=' . $_Itemid;
		};

		$router = JPATH_SITE . DS . 'components' . DS . 'com_docman' . DS . 'router.php';
		if (is_file($router)) {
			include_once($router);
		}
		$link = JRoute::_($link);

		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT dmsubmitedby FROM #__docman WHERE id = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}

	protected static function _findItem($needles)
	{
		$component = JComponentHelper::getComponent('com_docman');

		$menus = JApplication::getMenu('site');
		$items = $menus->getItems('componentid', $component->id);
		$user = JFactory::getUser();
		$access = (int)$user->get('aid');

		foreach ($needles as $needle => $id) {
			if (is_array($items)) {
				foreach ($items as $item) {
					if ($item->published == 1 && $item->access <= $access) {
						return $item;
					}
				}
			}
		}

		return false;
	}

}