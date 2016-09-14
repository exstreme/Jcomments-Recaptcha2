<?php
/**
 * JComments plugin for JoomDOC objects support (http://www.artio.net/downloads/joomla/joomdoc)
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_joomdoc extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$routerHelper = JPATH_ROOT.'/administrator/components/com_joomdoc/libraries/joomdoc/application/route.php';
		if (is_file($routerHelper)) {
			require_once(JPATH_ROOT.'/administrator/components/com_joomdoc/defines.php');
			require_once($routerHelper);

			$db = JFactory::getDBO();

			$query = $db->getQuery(true);
			$query->select('a.id, a.title, a.alias, a.access, a.created_by, a.path');
			$query->from('#__joomdoc AS a');
			$query->where('a.id = ' . (int) $id);
			
			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

				$info->title = $row->title;
				$info->access = $row->access;
				$info->userid = $row->created_by;
				$info->link = JRoute::_(JoomDOCRoute::viewDocuments($row->path, false));;
			}
		}

		return $info;
	}
}