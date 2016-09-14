<?php
/**
 * JComments plugin for TPDugg objects support
 *
 * @version 1.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_tpdugg extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT title, id FROM #__tpdugg WHERE id='$id'");
		return $db->loadResult();
	}
 
	function getObjectLink($id)
	{
		$_Itemid = self::getItemid('com_tpdugg');
		$link = JRoute::_('index.php?option=com_tpdugg&amp;task=detail&amp;id='.$id.'&amp;show=comments'.'&Itemid='. $_Itemid);
		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT userid FROM #__tpdugg WHERE id='$id'");
		return $db->loadResult();
	}
}