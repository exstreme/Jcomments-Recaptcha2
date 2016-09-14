<?php
/**
 * JComments plugin for Mosets tree support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_mtree extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT link_id, link_name, user_id FROM #__mt_links WHERE link_id = ' . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_mtree');
			$Itemid = $Itemid > 0 ? '&Itemid=' . $Itemid : '';

			$info->title = $row->link_name;
			$info->userid = $row->user_id;
			$info->link = JRoute::_('index.php?option=com_mtree&amp;task=viewlink&amp;link_id=' . $id . $Itemid);
		}

		return $info;
	}
}