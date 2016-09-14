<?php
/**
 * JComments plugin for BeeHeard
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_beeheardlite extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, title, user_id FROM #__beeheard_suggestions WHERE id = ' . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_beeheardlite');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->title;
			$info->userid = $row->user_id;
			$info->link = JRoute::_('index.php?option=com_beeheardlite&controller=suggestions&suggestion_id='.$id.$Itemid);
		}

		return $info;
	}
}