<?php
/**
 * JComments plugin for DigiFolio projects support
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_digifolio extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id, name, alias, created_by, access');
		$query->from('#__digifolio_projects');
		$query->where('id = ' . (int)$id);
		$db->setQuery($query);

		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_digifolio');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$slug = $row->alias ? ($row->id.':'.$row->alias) : $row->id;

			$info->title = $row->name;
			$info->access = $row->access;
			$info->userid = $row->created_by;
			$info->link = JRoute::_('index.php?option=com_digifolio&amp;view=project&amp;id=' . $slug . $Itemid);
		}

		return $info;
	}
}