<?php
/**
 * JComments plugin for JDownloads objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jdownloads extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$query = "SELECT file_id as id, file_title as title, submitted_by as owner, cat_id"
			. " FROM #__jdownloads_files"
			. " WHERE file_id = " . $id;
		$db->setQuery($query);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$db->setQuery("SELECT id FROM #__menu WHERE link = 'index.php?option=com_jdownloads&view=viewcategory&catid=".$row->cat_id."' and published = 1");
			$Itemid = $db->loadResult();

			if (!$Itemid) {
				$Itemid = self::getItemid('com_jdownloads');
			}
			
			$Itemid = $Itemid > 0 ? '&amp;Itemid='.$Itemid : '';

			$info->category_id = $row->cat_id;
			$info->title = $row->title;
			$info->userid = $row->owner;
			$info->link = JRoute::_('index.php?option=com_jdownloads'.$Itemid.'&amp;view=viewdownload&amp;catid='.$row->cat_id.'&amp;cid='.$id);
		}

		return $info;
	}
}