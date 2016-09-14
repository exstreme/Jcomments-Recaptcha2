<?php
/**
 * JComments plugin for aPoll component (http://www.afactory.org)
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_apoll extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT p.id, p.title, p.access '
			. ', CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(":", p.id, p.alias) ELSE p.id END as slug'
			. ' FROM #__apoll_polls AS p'
			. ' WHERE p.id = '.$id
			;
		$db->setQuery($query);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_apoll', 'index.php?option=com_apoll&view=apoll');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->title;
			$info->access = $row->access;
			$info->userid = 0;
			$info->link = JRoute::_('index.php?option=com_apoll&view=apoll&id='.$row->slug.$Itemid);
		}

		return $info;
	}
}