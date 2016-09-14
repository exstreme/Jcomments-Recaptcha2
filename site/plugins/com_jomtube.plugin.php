<?php
/**
 * JComments plugin for JomTube support
 *
 * @version 2.3
 * @package JComments
 * @author July07
 * @copyright (C) 2012 by July07
 * @copyright (C) 2012-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jomtube extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, video_title, user_id FROM #__jomtube_videos WHERE id = '.$id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_jomtube');
			$Itemid = $Itemid > 0 ? '&amp;Itemid='.$Itemid : '';

			$info->title = $row->video_title;
			$info->userid = $row->user_id;
			$info->link = JRoute::_('index.php?option=com_jomtube&amp;view=video&amp;id='.$id.$Itemid);
		}

		return $info;
	}
}