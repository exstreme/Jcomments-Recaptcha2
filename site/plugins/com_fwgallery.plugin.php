<?php
/**
 * JComments plugin for FW Gallery (http://fastw3b.net)
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_fwgallery extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, name, user_id FROM #__fwg_files WHERE id = ' . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$_Itemid = self::_getItemid('image');
			$_Itemid = (intval($_Itemid) ? '&amp;Itemid=' . $_Itemid : '');

			$info->title = $row->name;
			$info->userid = $row->user_id;
			$info->link = JRoute::_('index.php?option=com_fwgallery&view=image&id=' . $row->id . ':' . JFilterOutput::stringURLSafe($row->name) . $_Itemid . '#fwgallerytop');
		}

		return $info;
	}

	protected static function _getItemid($view = 'galleries', $id = 0, $default = 0)
	{
		$item = null;
		$menu = JMenu::getInstance('site');

		if ($id && $items = $menu->getItems('link', 'index.php?option=com_fwgallery&view=' . $view)) {
			foreach ($items as $menuItem) {
				if ((is_string($menuItem->params) && preg_match('/id\='.$id.'\s/ms', $menuItem->params)) || (is_object($menuItem->params) && $id == $menuItem->params->get('id'))) {
					$item = $menuItem;
					break;
				}
			}
        	}
		
		if ($item === null) {
			$item = $menu->getItems('link', 'index.php?option=com_fwgallery&view=galleries', true);
		}

        	if ($item) {
        		return $item->id;
		} elseif ($default) {
			return $default;
		} elseif ($item = $menu->getActive()) {
			return $item->id;
		}
        }
}