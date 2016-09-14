<?php
/**
 * JComments plugin for PhocaDownload
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_phocadownload extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$db = JFactory::getDBO();
		$query = 'SELECT id, title, access '
			. ' , CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug'
			. ' FROM #__phocadownload_categories'
			. ' WHERE id = '.$id
			;
		$db->setQuery($query);
		$row = $db->loadObject();
			
		if (!empty($row)) {
			$Itemid = self::getItemid('com_phocadownload');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->title;
			$info->access = $row->access;
			$info->link = JRoute::_('index.php?option=com_phocadownload&view=category&id=' . $row->slug . $Itemid);
		}

		return $info;
	}
}