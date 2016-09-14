<?php
/**
 * JComments plugin for Seyret support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_seyret extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, title, addedby FROM #__seyret_items WHERE id = '.$id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_seyret');
			$Itemid = $Itemid > 0 ? '&amp;Itemid='.$Itemid : '';

			$info->title = $row->title;
			$info->userid = $row->addedby;
			$info->link = JRoute::_('index.php?option=com_seyret&amp;task=videodirectlink&amp;id='.$id.$Itemid);
		}

		return $info;
	}
}