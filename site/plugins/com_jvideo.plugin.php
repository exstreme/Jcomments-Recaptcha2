<?php
/**
 * JComments plugin for JVideo (http://jvideo.infinovision.com/)
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jvideo extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, video_title, user_id FROM #__jvideos WHERE id = ' . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_jvideo');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->video_title;
			$info->access = 0;
			$info->userid = $row->user_id;
			$info->link = JRoute::_('index.php?option=com_jvideo&view=watch&id='.$id.$Itemid);
		}

		return $info;
	}
}