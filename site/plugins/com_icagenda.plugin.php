<?php
/**
 * JComments plugin for iCagenda (http://www.joomlic.com/en/extensions/icagenda)
 *
 * @version 1.0
 * @package JComments
 * @author Denys Nosov (denys@joomla-ua.org)
 * @copyright (C) 2013 JoomliC, www.joomlic.com
 * @copyright (C) 2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_icagenda extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id, title, access, created_by, alias');
		$query->from('#__icagenda_events');
		$query->where('id = ' . (int) $id);
		$db->setQuery( $query );
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_icagenda', 'index.php?option=com_icagenda&view=list');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$row->slug = $row->alias ? ($row->id.':'.$row->alias) : $row->id;

			$info->title = $row->title;
			$info->access = $row->access;
			$info->userid = $row->created_by;
			$info->link = JRoute::_('index.php?option=com_icagenda&view=list&layout=event&id=' . $row->slug . $Itemid);
		}

		return $info;
	}
}