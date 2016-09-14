<?php
/**
 * JComments plugin for MijoPolls support
 *
 * @version 3.0
 * @package JComments
 * @author Mijosoft LLC
 * @copyright (C) 2009-2013 Mijosoft LLC, mijosoft.com
 * @copyright (C) 2009-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_mijopolls extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$router = JPATH_ROOT.'/components/com_mijopolls/router.php';
		if (is_file($router)) {
			require_once($router);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id, title, alias');
			$query->from('#__mijopolls_polls');
			$query->where('id = ' . (int) $id);

			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$Itemid = self::getItemid('com_mijopolls', 'index.php?option=com_mijopolls&view=polls');
				$Itemid = $Itemid > 0 ? '&Itemid=' . $Itemid : '';

				$row->slug = $row->alias ? ($row->id.':'.$row->alias) : $row->id;

				$info->title = $row->title;
				$info->link = JRoute::_('index.php?option=com_mijopolls&view=poll&id=' . $row->slug . $Itemid);
			}
		}

		return $info;
	}
}