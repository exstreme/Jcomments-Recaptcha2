<?php
/**
 * JComments plugin for Zoo (zoo.yootheme.com) objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_zoo extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();
		$app = JFactory::getApplication();
		if (!JCommentsSystemPluginHelper::isAdmin($app)) {
			$zooConfig = JPATH_ADMINISTRATOR.'/components/com_zoo/config.php';
			if (is_file($zooConfig)) {
				require_once($zooConfig);
				$zooApp = App::getInstance('zoo');
				$item = $zooApp->table->item->get($id);
				if (!empty($item)) {
					$info->title = $item->name;
					$info->access = $item->access;
					$info->userid = $item->created_by;
					$info->link = JRoute::_($zooApp->route->item($item));
				}
			}
		}
		return $info;
	}
}