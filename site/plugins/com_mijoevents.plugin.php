<?php
/**
 * JComments plugin for MijoEvents support
 *
 * @version 3.0
 * @package JComments
 * @author Mijosoft LLC
 * @copyright (C) 2009-2013 Mijosoft LLC, mijosoft.com
 * @copyright (C) 2009-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_mijoevents extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$helper = JPATH_ADMINISTRATOR . '/components/com_mijoevents/library/mijoevents.php';
		if (is_file($helper)) {
			require_once($helper);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id, title, created_by');
			$query->from('#__mijoevents_events');
			$query->where('id = ' . (int) $id);

			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$Itemid = MijoEvents::get('utility')->getItemid(array('category'), null, false);
				$Itemid = $Itemid > 0 ? '&Itemid=' . $Itemid : '';

				$info->title = $row->title;
				$info->userid = $row->created_by;
				$info->link = JRoute::_('index.php?option=com_mijoevents&view=event&event_id=' . $id . $Itemid);
			}
		}

		return $info;
	}
}