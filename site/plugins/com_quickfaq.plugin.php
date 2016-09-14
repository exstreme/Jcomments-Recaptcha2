<?php
/**
 * JComments plugin for QuickFAQ (http://joomlacode.org/gf/project/quickfaq) articles support
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_quickfaq extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT title, id FROM #__quickfaq_items WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
	        $link = '';

	        $quickFaqRouterPath = JPATH_SITE.DS.'components'.DS.'com_quickfaq'.DS.'helpers'.DS.'route.php';
	        
	        if (is_file($quickFaqRouterPath)) {
			require_once ($quickFaqRouterPath);

			$db = JFactory::getDbo();
			
			$query = 'SELECT CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug,'
				. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug'
				. ' FROM #__quickfaq_items AS i'
				. ' LEFT JOIN #__quickfaq_cats_item_relations AS rel ON rel.itemid = i.id'
				. ' LEFT JOIN #__quickfaq_categories AS c ON c.id = rel.catid'
				. ' WHERE i.id = '.$id
				;
			$db->setQuery($query);
			$row = $db->loadObject();
			
			$link = JRoute::_(QuickfaqHelperRoute::getItemRoute($row->slug, $row->categoryslug));
		}

		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT created_by, id FROM #__quickfaq_items WHERE id = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}
}