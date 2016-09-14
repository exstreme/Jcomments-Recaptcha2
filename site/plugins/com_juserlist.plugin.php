<?php
/**
 * JComments plugin for JUserlist
 *
 * @version 1.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_juserlist extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT name FROM #__users WHERE id = ' . $id );
		return $db->loadResult();
	}
 
	function getObjectLink($id)
	{
		$_Itemid = self::getItemid( 'com_juserlist' );
 
		$link = JRoute::_( 'index.php?option=com_juserlist&amp;view=profile&amp;id=' .$id .'&amp;Itemid=' . $_Itemid);
		return $link;
	}
 
	function getObjectOwner($id)
	{
		$user = JFactory::getUser();
		return $user->id;
	}
}