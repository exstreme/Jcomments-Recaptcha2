<?php
/**
 * JComments plugin for MijoShop support
 *
 * @version 3.0
 * @package JComments
 * @author Mijosoft LLC
 * @copyright (C) 2009-2013 Mijosoft LLC, mijosoft.com
 * @copyright (C) 2009-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_mijoshop extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$helper = JPATH_ROOT . '/components/com_mijoshop/mijoshop/mijoshop.php';
		if (is_file($helper)) {
			require_once($helper);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('product_id, name');
			$query->from('#__mijoshop_product_description');
			$query->where('product_id = ' . (int) $id);

			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$info->title = $row->name;
				$info->link = MijoShop::get('router')->route('index.php?route=product/product&product_id='.$id);
			}
		}

		return $info;
	}
}