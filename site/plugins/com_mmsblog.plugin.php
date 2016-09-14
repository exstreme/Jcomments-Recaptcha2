<?php
/**
 * JComments plugin for mmsBlog support
 *
 * @version 2.0
 * @package JComments
 * @author majus (m_rausch@gmx.de)
 * @copyright (C) 2011 by majus
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_mmsblog extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT subject, id FROM #__mmsblog_item WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$_Itemid = self::getItemid('com_mmsblog');
		$link = JRoute::_('index.php?option=com_mmsblog&amp;view=item&amp;id='. $id .'&amp;Itemid='. $_Itemid);
		return $link;
	}
}