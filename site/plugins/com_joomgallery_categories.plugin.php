<?php
/**
 * JComments plugin for JoomGallery categories comments support
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_joomgallery_categories extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		// Category comments
		$db = JFactory::getDBO();
		$db->setQuery('SELECT name, cid FROM #__joomgallery_catg WHERE cid = ' . $id);
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

		// Category view
		return JRoute::_('index.php?option=com_joomgallery&amp;view=category&amp;catid=' . $id . '&amp;Itemid=' . $_Itemid);
	}

	function getObjectOwner($id)
	{
		// Category owner
		$db = JFactory::getDBO();
		$db->setQuery('SELECT owner FROM #__joomgallery_catg WHERE cid = ' . $id);
		$userid = $db->loadResult();
		return intval($userid);
	}
}