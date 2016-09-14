<?php
/**
 * JComments plugin for Music Collection support
 *
 * @version 2.1
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_muscol_artist extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT artist_name, id FROM #__muscol_artists WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$link = 'index.php?option=com_muscol&amp;view=artist&amp;id=' . $id;
		$_Itemid = self::getItemid('com_muscol');
		$link .= ($_Itemid > 0) ? ('&Itemid=' . $_Itemid) : '';
		$link = JRoute::_( $link );
		return $link;
	}
}