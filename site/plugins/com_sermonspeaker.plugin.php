<?php
/**
 * JComments plugin for SermonSpeaker support (http://www.sermonspeaker.net/)
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_sermonspeaker extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$db = JFactory::getDBO();
		$query = 'SELECT id, sermon_title, created_by '
			. ' FROM #__sermon_sermons'
			. ' WHERE id = '.$id
			;
		$db->setQuery($query);
		$row = $db->loadObject();
			
		if (!empty($row)) {
			$Itemid = self::getItemid('com_sermonspeaker');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->sermon_title;
			$info->access = 0;
			$info->userid = $row->created_by;
			$info->link = JRoute::_('index.php?option=com_sermonspeaker&view=sermon&id=' . $row->id . $Itemid);
		}

		return $info;
	}
}