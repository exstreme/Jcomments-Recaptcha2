<?php
/**
 * JComments plugin for JaVoice events support
 *
 * @version 1.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;
 
class jc_com_javoice extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT title, id FROM #__jav_items WHERE id = '.$id);
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$_Itemid = self::getItemid('com_javoice');

		//get type_id
		$db = JFactory::getDBO();
		$db->setQuery('SELECT voice_types_id FROM #__jav_items WHERE id = '.$id);
		$type_id = $db->loadResult();
		
		$link = JRoute::_('index.php?option=com_javoice&amp;view=items&amp;layout=item&amp;cid='.$id.'&amp;type='.$type_id.'&amp;Itemid='.$_Itemid);
		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT user_id FROM #__jav_items WHERE id = '.$id);
		$userid = (int) $db->loadResult();
		
		return $userid;
	}
}