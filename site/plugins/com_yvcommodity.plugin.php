<?php
/**
 * JComments plugin for yvCommodity objects support
 *
 * @version 2.3
 * @package JComments
 * @author Victor Yunoshev (yv-soft@ukr.net)
 * @copyright (C) 2011-2013 by Victor Yunoshev
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_yvcommodity extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$yvc = JPATH_SITE.'/administrator/components/com_yvcommodity/init.yvcommodity.php';
		if (is_file($yvc)) {
			require_once($yvc);
			include_once(_YVC_ABS_PATH . '/yvcommodity.class.php');
			include_once(_YVC_ADMIN_ABS_PATH . '/helper.yvcommodity.php');

			$db = yvHelper::getDBO();
			$query = $db->getQuery(true);

			$query->select('a.id, a.title, a.created_by, a.access, a.alias, a.cat_id');
			$query->from($db->quoteName('#__yvc') . ' AS a');
			$query->select('c.alias AS category_alias');
			$query->join('LEFT', $db->quoteName('#__yvc_catg') . ' AS c ON c.cat_id = a.cat_id');
			$query->where('a.id = ' . (int) $id);

			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$row->slug = $row->alias ? ($row->id.':'.$row->alias) : $row->id;
				$row->catslug = $row->category_alias ? ($row->cat_id.':'.$row->category_alias) : $row->cat_id;

				$info->category_id = $row->cat_id;
				$info->title = $row->title;
				$info->access = $row->access;
				$info->userid = $row->created_by;
				$info->link = JRoute::_( "index.php?option=com_yvcommodity&amp;Itemid=". yvHelper::get_yvc_Itemid(). "&amp;view=commodity&amp;id=". $row->slug. "&amp;cid=". $row->catslug);
			}
		}

		return $info;
	}
}