<?php
/**
 * JComments plugin for JomClassifieds objects support
 *
 * @version 1.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2014 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jomclassifieds extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id, title, alias, userid, catid FROM #__jomcl_adverts WHERE id = " . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

			$Itemid = self::getItemid('com_jomclassifies');
			$Itemid = $Itemid > 0 ? '&amp;Itemid='.$Itemid : '';

			$info->category_id = $row->catid;
			$info->title = $row->title;
			$info->userid = $row->userid;
			$info->link = JRoute::_('index.php?option=com_jomclassifieds&view=advert&id='.$slug.$Itemid);
		}

		return $info;
	}
}