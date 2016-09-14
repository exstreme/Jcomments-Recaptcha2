<?php
/**
 * JComments plugin for JoomGallery images comments support
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_joomgallery extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDBO();
		// Image comments
		$db->setQuery('SELECT imgtitle, id FROM #__joomgallery WHERE id = ' . $id);
		return $db->loadResult();
	}

	function getObjectLink($id) 
	{
		// Get an Itemid of JoomGallery
		// First, check whether there was set one in the configuration
		$db = JFactory::getDBO();
		$db->setQuery('SELECT jg_itemid FROM #__joomgallery_config LIMIT 1');
		if (!$_Itemid = $db->loadResult()) {
			$_Itemid = self::getItemid('com_joomgallery');
		}
		
		// Detail view
		return JRoute::_('index.php?option=com_joomgallery&amp;view=detail&amp;id=' . $id . '&amp;Itemid=' . $_Itemid);
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDBO();

		// Image owner
		$db->setQuery('SELECT owner FROM #__joomgallery WHERE id = ' . $id);
		$userid = $db->loadResult();
		return intval($userid);
	}
}