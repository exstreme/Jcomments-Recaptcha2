<?php
/**
 * JComments plugin for Joomla com_poll component
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_poll extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id, title, alias, access FROM #__polls WHERE id = " . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$_Itemid = self::getItemid('com_poll');
			$_Itemid = $_Itemid > 0 ? '&Itemid=' . $_Itemid : '';

			$info->title = $row->title;
			$info->access = $row->access;
			$info->link = JRoute::_('index.php?option=com_poll&id='. $id . ':' . $row->alias . $_Itemid);
		}

		return $info;
	}
}