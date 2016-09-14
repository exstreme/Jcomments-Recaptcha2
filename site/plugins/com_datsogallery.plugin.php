<?php
/**
 * JComments plugin for DatsoGallery objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_datsogallery extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$db = JFactory::getDbo();
		$db->setQuery('SELECT id, catid, imgtitle, owner_id FROM #__datsogallery WHERE id = ' . $id);
		$row = $db->loadObject();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_datsogallery');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->imgtitle;
			$info->userid = $row->owner_id;
			$info->link = JRoute::_('index.php?option=com_datsogallery&func=detail&catid=' . $row->catid . '&id=' . $id . $Itemid);
		}

		return $info;
	}
}