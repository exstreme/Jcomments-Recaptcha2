<?php
/**
 * JComments plugin for AdsManager ads objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_adsmanager extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{

		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, ad_headline, userid, category FROM #__adsmanager_ads WHERE id = ' . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_adsmanager', 'index.php?option=com_adsmanager&view=front');
			$Itemid = $Itemid > 0 ? '&Itemid=' . $Itemid : '';

			$info->category_id = $row->category;
			$info->title = $row->ad_headline;
			$info->userid = $row->userid;
			$info->link = JRoute::_("index.php?option=com_adsmanager&view=details&id=" . $row->id . "&catid=" . $row->category . $Itemid);
		}

		return $info;
	}
}