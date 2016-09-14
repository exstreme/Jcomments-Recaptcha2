<?php
/**
 * JComments plugin for RedShop support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_redshop extends JCommentsPlugin
{

	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT p.product_id, p.product_name, c.category_id'
			. ' FROM #__redshop_product AS p '
			. ' LEFT JOIN #__redshop_product_category_xref AS pcx ON pcx.product_id = p.product_id '
			. ' LEFT JOIN #__redshop_category AS c ON c.category_id = pcx.category_id '
			. ' WHERE p.product_id = ' . $id
			;

		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::_findItem($id, $row->category_id);
			$Itemid = $Itemid > 0 ? '&amp;Itemid='.$Itemid : '';

			$info->category_id = $row->category_id;
			$info->title = $row->product_name;
			$info->link = JRoute::_('index.php?option=com_redshop&view=product&pid='.$row->product_id.'&cid='.$row->category_id.$Itemid);
		}

		return $info;
	}

	protected static function _findItem($product_id, $category_id)
	{
		$component = JComponentHelper::getComponent('com_redshop');
		$menus	= JApplication::getMenu('site');
		$field = (JCOMMENTS_JVERSION == '1.5') ? 'componentid' : 'component_id';
		$items	= $menus->getItems($field, $component->id);
		$user 	= JFactory::getUser();
		$access = (int)$user->get('aid');

		$count = count($items);

		if ($count == 1) {
			return $items[0]->id;
		} else if ($count > 1) {
			foreach($items as $item) {
				if ($item->access <= $access) {
					if (isset($item->query) && isset($item->query['view'])) {
						if ($item->query['view'] == 'category' && isset($item->query['cid'])) {
							if ($item->query['cid'] = $category_id) {
								return $item->id;
							}
						}
					}
				}
			}

			$db = JFactory::getDBO();
			$db->setQuery('SELECT category_id FROM #__redshop_product_category_xref WHERE product_id = ' . $product_id);
			$categories = $db->loadResultArray();

			foreach($items as $item) {
				if ($item->access <= $access) {
					if (isset($item->query) && isset($item->query['view'])) {
						if ($item->query['view'] == 'category' && isset($item->query['cid'])) {
							if (in_array($item->query['cid'], $categories)) {
								return $item->id;
							}
						}
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