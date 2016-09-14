<?php
/**
 * JComments plugin for AllEvents (http://avonture.be/allevents) objects support
 *
 * @version 2.3
 * @package JComments
 * @author Christophe Avonture (http:/:http://avonture.be/allevents/christophe-avonture)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_allevents extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$routerHelper = JPATH_SITE.'/components/com_allevents/helpers/route.php';
		if (is_file($routerHelper)) {
			require_once($routerHelper);
		
			$db = JFactory::getDBO();
			$db->setQuery('SELECT titre, access, proposed_by FROM #__allevents_events WHERE `id`=' . $db->Quote($id));
			$row = $db->loadObject();
			
			if (!empty($row)) {
				$info->title = $row->titre;
				$info->access = $row->access;
				$info->userid = $row->proposed_by;
				$info->link = AllEventsHelperRoute::getEventRoute($id);
			}
		}
		return $info;
	}
}