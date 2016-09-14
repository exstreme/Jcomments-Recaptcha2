<?php
/**
 * JComments plugin for DJ Catalog objects support (http://design-joomla.ru)
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_djcatalog extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$routerHelper = JPATH_ROOT.'/components/com_djcatalog/helpers/route.php';
		if (is_file($routerHelper)) {
			require_once($routerHelper);

			$db = JFactory::getDBO();
			$db->setQuery('SELECT id, cat_id, name FROM #__djcat_items WHERE id = '.$id);
			$row = $db->loadObject();

			if (!empty($row)) {
				$info->title = $row->name;
				$info->link = JRoute::_(DJCatalogHelperRoute::getItemRoute($row->id, $row->cat_id));
			}
		}

		return $info;
	}
}