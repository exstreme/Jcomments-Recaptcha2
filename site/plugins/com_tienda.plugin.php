<?php
/**
 * JComments plugin for Tienda objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_tienda extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();

		$query = "SELECT a.product_id as id, a.product_name as title"
			. "\n , c.category_id"
			. "\n FROM #__tienda_products AS a"
			. "\n LEFT JOIN #__tienda_productcategoryxref AS b ON b.product_id = a.product_id"
			. "\n LEFT JOIN #__tienda_categories AS c ON b.category_id = c.category_id"
			. "\n WHERE a.product_id = " . intval($id)
			;

		$db->setQuery( $query, 0, 1);
		$product = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($product)) {
			$routerHelper = JPATH_ROOT.'/administrator/components/com_tienda/helpers/route.php';
			if (is_file($routerHelper)) {
				require_once($routerHelper);

				$info->category_id = $product->category_id;
				$info->title = $product->title;
				$info->access = 0;
				$info->userid = 0;
				$info->link = JRoute::_(TiendaHelperRoute::product($id, $product->category_id));
			}
		}

		return $info;
	}
}