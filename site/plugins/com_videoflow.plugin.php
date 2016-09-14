<?php
/**
 * JComments plugin for VideoFlow Multimedia System
 *
 * @version 2.3
 * @package VideoFlow
 * @author Kirungi Fred Fideri (fideri@fidsoft.com)
 * @copyright (C) 2011 by Kirungi Fred Fideri (http://www.fidsoft.com)
 * @copyright (C) 2012-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_videoflow extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();

		$query = "SELECT id, title, userid FROM #__vflow_data WHERE published = 1 AND id = " . (int) $id;
		$db->setQuery($query, 0, 1);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$_Itemid = self::getItemid('com_videoflow');

			$info->title = empty($row->title) ? 'VideoFlow Multimedia Content - No Title' : $row->title;
			$info->access = 0;
			$info->userid = $row->userid;
			$info->link = JRoute::_("index.php?option=com_videoflow&amp;task=play&amp;id=" . $id . "&amp;Itemid=" . $_Itemid);
		}

		return $info;
	}
}