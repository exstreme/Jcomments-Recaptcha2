<?php
/**
 * JComments plugin for JoomShopping support (http://www.webdesigner-profi.de/joomla-webdesign/joomla-shop)
 *
 * @version 2.3
 * @package JComments
 * @author MAXXmarketing GmbH
 * @copyright (C) 2012 webdesigner-profi.de. All rights reserved.
 * @copyright (C) 2012-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jshopping extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$functions = JPATH_ROOT . '/components/com_jshopping/lib/functions.php';
		if (is_file($functions)) {
			require_once(JPATH_ROOT . '/components/com_jshopping/lib/factory.php');
			require_once($functions);

			$titleField = 'name_' . JFactory::getLanguage()->getTag();

			$db = JFactory::getDBO();
			$query = "SELECT p.`$titleField`, p.access, c.category_id "
				. " FROM #__jshopping_products AS p"
				. " JOIN #__jshopping_products_to_categories AS c ON p.product_id = c.product_id"
				. " WHERE p.product_id = " . $id
				;
		
			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$info->category_id = $row->category_id;
				$info->title = $row->$titleField;
				$info->access = $row->access;
				$info->link = SEFLink('index.php?option=com_jshopping&controller=product&task=view&category_id='.$row->category_id.'&product_id='.$id);
			}
		}

		return $info;
	}
}