<?php
/**
 * JComments plugin for EventList
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_eventlist extends JCommentsPlugin 
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT title, id FROM #__eventlist_events WHERE id = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$db = JFactory::getDBO();

		$query = 'SELECT a.id, CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug'
			. ' FROM #__eventlist_events AS a'
			. ' WHERE id = ' . $id
			;
		$db->setQuery($query);
		$slug = $db->loadResult();

		require_once(JPATH_SITE.'/includes/application.php');

		$eventListRouter = JPATH_SITE.'/components/com_eventlist/helpers/route.php';
		if (is_file($eventListRouter)) {
			require_once($eventListRouter);
			$link = JRoute::_( EventListHelperRoute::getRoute($slug) );
		} else {
			$link = JRoute::_( 'index.php?option=com_eventlist&view=details&id=' . $slug );
		}

		return $link;
	}

	function getObjectOwner($id) {

		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT created_by, id FROM #__eventlist_events WHERE id = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}
}