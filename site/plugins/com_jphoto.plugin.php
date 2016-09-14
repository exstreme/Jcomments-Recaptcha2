<?php
/**
 * JComments plugin for JPhoto
 *
 * @version 2.0
 * @package JComments
 * @author Hari Karam Singh (harikaram@regallygraceful.com)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jphoto extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT title, id FROM #__jphoto_imgs WHERE id = ' . $id);
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT alias, gallery FROM #__jphoto_imgs WHERE id = ' . $id );
		$r = $db->loadAssoc();
		$alias = $r['alias'];
		$gal_id = $r['gallery'];

		$link = 'index.php?option=com_jphoto&Itemid='.$gal_id.'&id='. $id . ':' . $alias . '&view=image';

		$link = JRoute::_($link);
		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT created_by FROM #__jphoto_imgs WHERE id = " . $id);
		$userid = $db->loadResult();
		
		return intval($userid);
	}
}