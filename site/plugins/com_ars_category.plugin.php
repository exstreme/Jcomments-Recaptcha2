<?php
/**
 * JComments plugin for Akeeba Release System (https://www.akeebabackup.com/)
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_ars_category extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$routerHelper = JPATH_ROOT. '/components/com_ars/router.php';
		if (is_file($routerHelper)) {
			require_once($routerHelper);

			$db = JFactory::getDBO();
			$query = 'SELECT id, title, alias, access, created_by'
				. ' FROM #__ars_categories'
				. ' WHERE id = ' . $id
				;
			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$options = array('option' => 'com_ars', 'view'=>'browses', 'language' => $language);
				$menu = ArsRouterHelper::findMenu($options);
				$Itemid = $menu->id;
				$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

				$info->title = $row->title;
				$info->access = $row->access;
				$info->userid = $row->created_by;
				$info->link = JRoute::_('index.php?option=com_ars&view=category&id=' . $id . $Itemid);
			}
		}

		return $info;
	}

}