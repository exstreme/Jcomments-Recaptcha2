<?php
/**
 * JComments plugin for DJ-Catalog2 objects support (http://dj-extensions.com)
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_djcatalog2 extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$routerHelper = JPATH_ROOT.'/components/com_djcatalog2/helpers/route.php';
		if (is_file($routerHelper)) {
			require_once($routerHelper);

			$db = JFactory::getDBO();

			$query = $db->getQuery(true);
			$query->select('a.id, a.alias, a.name, a.created_by');
			$query->from('#__djc2_items AS a');
			$query->select('c.id AS category_id, c.alias AS category_alias');
			$query->join('LEFT', '#__djc2_categories AS c ON c.id = a.cat_id');
			$query->where('a.id = ' . (int) $id);
			
			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;
				$catslug = $row->category_alias ? ($row->category_id . ':' . $row->category_alias) : $row->category_id;

				$info->title = $row->name;
				$info->category_id = $article->category_id;
				$info->userid = $row->created_by;
				$info->link = JRoute::_(DJCatalogHelperRoute::getItemRoute($slug, $catslug));
			}
		}

		return $info;
	}
}