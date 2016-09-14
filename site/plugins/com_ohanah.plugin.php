<?php
/**
 * JComments plugin for Ohanah
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_ohanah extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT title, created_by FROM #__ohanah_events WHERE ohanah_event_id = ' . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_ohanah');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->title;
			$info->userid = $row->created_by;
			$info->link = JRoute::_('index.php?option=com_ohanah&task=view&id='.$id.$Itemid);

		}

		return $info;
	}
}