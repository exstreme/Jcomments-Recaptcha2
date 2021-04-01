<?php
/**
 * JComments plugin for HikaShop (http://www.hikashop.com/) support
 *
 * @version 1.0
 * @package JComments
 * @author Hikari Team (hikari.software@gmail.com)
 * @copyright (C) 2011 by Hikari Team (http://www.hikashop.com/)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_hikashop extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT a.product_name as name, a.product_id as id, b.product_name as parent_name FROM #__hikashop_product AS a LEFT JOIN #__hikashop_product AS b ON a.product_parent_id=b.product_id WHERE a.product_id = ' . $id );
		$obj = $db->loadObject();
		$name = @$obj->name;
		
		if (empty($name)) {
			$name = @$obj->parent_name;
		}

		if (empty($name)) {
			$name = $id;
		}

		return $name;
	}

	function getObjectLink($id)
	{
		$Itemid = self::getItemid('com_hikashop');
		$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

		$link = JRoute::_('index.php?option=com_hikashop&amp;ctrl=product&amp;task=show&amp;cid=' . $id . $Itemid);
		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT a.product_vendor_id as created_by, a.product_id as id, b.product_vendor_id as parent_created_by FROM #__hikashop_product AS a LEFT JOIN #__hikashop_product AS b ON a.product_parent_id=b.product_id WHERE a.product_id = ' . $id );
		$obj = $db->loadObject();
		$id = @$obj->created_by;

		if (empty($id)) {
			$id = @$obj->parent_created_by;
		}

		if (!empty($id)) {
			$db->setQuery( 'SELECT user_cms_id FROM #__hikashop_user WHERE user_id = ' . $id );
			$id = $db->loadResult();
		}

		if (empty($id)) {
			$app = JFactory::getApplication();
			if (JCommentsSystemPluginHelper::isAdmin($app)) {
				$user =& JFactory::getUser();
				$id = $user->id;
			}
		}
		return (int)$id;
	}
}