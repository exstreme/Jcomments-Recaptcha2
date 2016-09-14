<?php
/**
 * JComments plugin for Listbingo support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2011-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_listbingo extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id, title, user_id FROM #__gbl_ads WHERE id = ".$id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_listbingo');
			$Itemid = $Itemid > 0 ? '&amp;Itemid='.$Itemid : '';

			$info->title = $row->title;
			$info->userid = $row->user_id;
			$info->link = JRoute::_('index.php?option=com_listbingo&amp;task=ads.view&amp;adid='.$id.$Itemid);
		}

		return $info;
	}
}