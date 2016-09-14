<?php
/**
 * JComments plugin for hwdMediaShare support
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_hwdmediashare extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$routerHelper = JPATH_SITE.'/components/com_hwdmediashare/helpers/route.php';
		if (is_file($routerHelper)) {
			require_once($routerHelper);

			$db = JFactory::getDBO();
			$db->setQuery('SELECT id, title, access, created_user_id FROM #__hwdms_media WHERE id = ' . $id);
			$row = $db->loadObject();
			
			if (!empty($row)) {
				$slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

				$info->title = $row->title ? 'Unknown hwdMediaShare Content' : $row->title;
				$info->userid = $row->created_user_id;
				$info->access = $row->access;
				$info->link = JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($slug));
			}
		}

		return $info;
	}
}