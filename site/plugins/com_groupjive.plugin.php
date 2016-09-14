<?php
/**
 * JComments plugin for GroupJive (http://www.groupjive.org/) support
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_groupjive extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT subject FROM #__gj_bul WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$Itemid = self::getItemid('com_groupjive');
		$Itemid = $Itemid > 0 ? '&Itemid=' . $Itemid : '';

		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT group_id FROM #__gj_bul WHERE id = ' . $id );
		$gid = $db->loadResult();

		$link = JRoute::_('index.php?option=com_groupjive&amp;task=showfullmessage&amp;idm=' . $id . '&amp;groupid=' . $gid . $Itemid);
		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT author_id FROM #__gj_bul WHERE id = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}
}