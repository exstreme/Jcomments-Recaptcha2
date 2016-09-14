<?php
/**
 * JComments plugin for AcePolls (http://www.joomace.net/joomla-extensions/acepolls-joomla-polls-component)
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright 2009-2011 JoomAce LLC, www.joomace.net
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_acepolls extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT p.id, p.title, p.access '
			. ', CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(":", p.id, p.alias) ELSE p.id END as slug'
			. ' FROM #__acepolls_polls AS p'
			. ' WHERE p.id = '.$id
			;
		$db->setQuery($query);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_acepolls', 'index.php?option=com_acepolls&view=polls');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->title;
			$info->access = $row->access;
			$info->userid = 0;
			$info->link = JRoute::_('index.php?option=com_acepolls&amp;view=poll&amp;id='.$row->slug.$Itemid);
		}

		return $info;
	}
}