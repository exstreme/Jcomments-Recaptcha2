<?php
/**
 * JComments plugin for Akeeba Release System (https://www.akeebabackup.com/)
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_ars extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT r.id, r.category_id, r.version, r.access, r.created_by, c.title'
			. ' FROM #__ars_categories AS c '
			. ' JOIN #__ars_releases AS r ON c.id = r.category_id '
			. ' WHERE r.id = ' . $id
			;

		$db->setQuery($query);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::_findItem(array('category_id' => $row->category_id, 'release_id' => $row->id));
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->category_id = $row->category_id;
			$info->title = $row->title . ' ' . $row->version;
			$info->access = $row->access;
			$info->userid = $row->created_by;
			$info->link = JRoute::_('index.php?option=com_ars&view=release&id='.$row->id.$Itemid);

		}

		return $info;
	}

	protected static function _findItem($needles)
	{
		$component = JComponentHelper::getComponent('com_ars');

		$menus	= JApplication::getMenu('site');
		$items	= $menus->getItems('componentid', $component->id);
		$user 	= JFactory::getUser();
		$access = (int)$user->get('aid');

		foreach($items as $item) {
			if ($item->published == 1 && $item->access <= $access) {
				if (isset($item->query) && isset($item->query['view'])) {
					if ($item->query['view'] == 'release') {
						$params = ($item->params instanceof JRegistry) ? $item->params : $menus->getParams($item->id);
						if ($params->get('relid',0) == $needles['release_id']) {
							return $item->id;
						}
					}
				}
			}
		}

		foreach($items as $item) {
			if ($item->published == 1 && $item->access <= $access) {
				if (isset($item->query) && isset($item->query['view'])) {
					if ($item->query['view'] == 'category') {
						$params = ($item->params instanceof JRegistry) ? $item->params : $menus->getParams($item->id);
						print_r($params);
						if ($params->get('catid',0) == $needles['category_id']) {
							return $item->id;
						}
					}
				}
			}
		}

		foreach($items as $item) {
			if ($item->published == 1 && $item->access <= $access) {
				if (isset($item->query) && isset($item->query['view'])) {
					if ($item->query['view'] == 'browse') {
						return $item->id;
					}
				}
			}
		}

		$active = $menus->getActive();
		if ($active) {
			return $active->id;
		}

		return false;
	}
}