<?php
/**
 * JComments plugin for RSEvents! objects support
 *
 * @version 1.0
 * @package JComments
 * @author Oregon
 * @copyright (C) 2011 by Oregon
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_rsevents extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, title, created_by FROM #__rsevents WHERE id = ' . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_rsevents');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->title;
			$info->userid = $row->created_by;
			$info->link = JRoute::_('index.php?option=com_rsevents&view=events&layout=show&cid='.$row->id);
		}

		return $info;
	}
}