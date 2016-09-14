<?php
/**
 * JComments plugin for VirtueMart objects support
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_virtuemart extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		jimport('joomla.filesystem.file');

		$info = new JCommentsObjectInfo();
		$configHelper = JPATH_ADMINISTRATOR.'/components/com_virtuemart/helpers/config.php';

		if (JFile::exists($configHelper)) {
			if (!class_exists('VmConfig')) {
				require_once($configHelper);
			}
	
			VmConfig::loadConfig();

			$db = JFactory::getDBO();
			$db->setQuery('SELECT product_name, created_by FROM #__virtuemart_products_' . VMLANG . ' WHERE virtuemart_product_id =' . $id);
			$row = $db->loadObject();
			
			if (!empty($row)) {
				$db->setQuery('SELECT virtuemart_category_id FROM #__virtuemart_product_categories WHERE virtuemart_product_id =' . $id);
				$categoryId = $db->loadResult();

				$info->title = $row->product_name;
				$info->userid = $row->created_by;
				$info->link = AllEventsHelperRoute::getEventRoute($id);
				$info->link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $id . '&virtuemart_category_id=' . $categoryId);
			}
		}

		return $info;
	}
}