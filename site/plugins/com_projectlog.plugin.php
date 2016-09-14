<?php
/**
 * JComments plugin for ProjectLog (http://www.thethinkery.net)
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_projectlog extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( "SELECT title, id FROM #__projectlog_projects WHERE id = $id" );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$_Itemid = self::getItemid( 'com_projectlog' );
		$link = "index.php?option=com_projectlog&view=project&project_id=" . $id;
		$link .= ($_Itemid > 0) ? ('&Itemid=' . $_Itemid) : '';
		$link = JRoute::_($link);
		return $link;
	}
}