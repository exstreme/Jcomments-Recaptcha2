<?php
/**
 * JComments plugin for ImproveMyCity support
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_improvemycity extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		if (is_file(JPATH_ROOT.'/components/com_improvemycity/improvemycity.php')) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id, a.title, a.userid, a.catid');
			$query->from('#__improvemycity AS a');
			$query->where('a.id = ' . (int) $id);

			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$Itemid = self::getItemid('com_improvemycity');
				$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

				$info->category_id = $row->catid;
				$info->title = $row->title;
				$info->userid = $row->userid;
				$info->link = JRoute::_('index.php?option=com_improvemycity&view=issue&issue_id=' . (int) $id . $Itemid);
			}
		}

		return $info;
	}
}