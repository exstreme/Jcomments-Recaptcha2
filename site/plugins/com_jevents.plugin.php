<?php
/**
 * JComments plugin for JEvents objects support
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jevents extends JCommentsPlugin 
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$db = JFactory::getDBO();

		$query = 'SELECT det.summary, rpt.rp_id, ev.created_by, ev.access'
			. ' FROM #__jevents_repetition AS rpt '
			. ' LEFT JOIN #__jevents_vevdetail AS det ON det.evdet_id = rpt.eventdetail_id '
			. ' LEFT JOIN #__jevents_vevent AS ev ON ev.ev_id = rpt.eventid '
			. ' WHERE ev.ev_id = ' . $id;

		$db->setQuery($query);
		$row = $db->loadObject();
			
		if (!empty($row)) {
			$info->title = $row->summary;
			$info->access = $row->access;
			$info->userid = $row->created_by;
			$info->link = JRoute::_( 'index.php?option=com_jevents&task=icalrepeat.detail&evid=' . $row->rp_id );
		}

		return $info;
	}
}