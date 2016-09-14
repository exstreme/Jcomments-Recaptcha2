<?php
/**
 * JComments plugin for Community Polls
 *
 * @version 1.0
 * @package JComments
 * @author CoreJoomla (support@corejoomla.com)
 * @copyright (C) 2011-2012 by CoreJoomla (http://www.corejoomla.com)
 * @copyright (C) 2012-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_communitypolls extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select('a.id, a.title, a.alias, a.created_by');
		$query->from('#__jcp_polls AS a');
		$query->where('a.id = ' . (int) $id);

		$db->setQuery($query);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$row->slug = $row->alias ? ($row->id.':'.$row->alias) : $row->id;

			$items = JApplication::getMenu('site')->getItems('link', 'index.php?option=com_communitypolls&controller=polls');
			$itemid = isset($items[0]) ? '&amp;Itemid='.$items[0]->id : '';
		
			$info->title = $row->title;
			$info->userid = $row->created_by;
			$info->link = JRoute::_( 'index.php?option=com_communitypolls&amp;controller=polls&amp;task=viewpoll&amp;id='. $row->slug.$itemid);
		}

		return $info;
	}
}