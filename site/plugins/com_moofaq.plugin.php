<?php
/**
 * JComments plugin for MooFAQ objects support
 *
 * @version 2.1
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_moofaq extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT title, id FROM #__content WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
	        $link = '';

		require_once(JPATH_ROOT.DS.'components'.DS.'com_moofaq'.DS.'helpers'.DS.'route.php');
		
		$query = 'SELECT a.id, a.sectionid, a.access,' .
				' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
				' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
				' FROM #__content AS a' .
				' LEFT JOIN #__categories AS cc ON cc.id = a.catid' .
				' WHERE a.id = ' . $id;

		$db = JFactory::getDbo();
		$db->setQuery( $query );
		$row = $db->loadObject();

		$user = JFactory::getUser();
		
		if ($row->access <= $user->get('aid', 0)) {
			$link = JRoute::_(MoofaqHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid));
		} else {
			$link = JRoute::_("index.php?option=com_user&task=register");
		}
		
		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT created_by FROM #__content WHERE id = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}
}