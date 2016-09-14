<?php
/**
 * JComments plugin for JVideoClip support
 *
 * @version 2.0
 * @package JComments
 * @author mazao
 * @copyright (C) 2011 by mazao
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jvideoclip extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT title, id FROM #__jvc_videos WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$_Itemid = self::getItemid('com_jvideoclip');
		$link = 'index.php?option=com_jvideoclip&view=showvideo&id=' . $id;
		$link .= ($_Itemid > 0) ? ('&Itemid=' . $_Itemid) : '';
		$link = JRoute::_( $link );
		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT user_id FROM #__jvc_videos WHERE id = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}
}