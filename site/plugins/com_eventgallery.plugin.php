<?php
/**
 * JComments plugin for EventGallery (http://www.svenbluege.de/) support
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_eventgallery extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id, title, file, folder, userid FROM #__eventgallery_file WHERE id = " . $id);
		$row = $db->loadObject();
 
		$info = new JCommentsObjectInfo();
 
		if (!empty($row)) {
			$Itemid = self::getItemid('com_eventgallery');
			$Itemid = $_Itemid > 0 ? '&Itemid=' . $Itemid : '';
 
			$info->title = $row->title;
			$info->userid = $row->created_by;
			$info->link = JRoute::_('index.php?option=com_eventgallery&view=singleimage&folder='. $row->folder . '&file=' . $row->file . $Itemid);
		}
 
		return $info;
	}
}