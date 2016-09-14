<?php
/**
 * JComments plugin for MarketPlace objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_marketplace extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$Itemid = self::getItemid('com_marketplace');
		$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

		$marketplaceCore = JPATH_SITE.'/components/com_marketplace/controller.php';
		if (is_file($marketplaceCore)) {
			$db = JFactory::getDBO();
			$query = 'SELECT e.id, e.headline as title, e.user_id as userid, e.category_id'
				. " , CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(':', e.id, e.alias) ELSE e.id END as slug"
				. " , CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(':', c.id, c.alias) ELSE c.id END as categorySlug"
				. " FROM #__marketplace_entries AS e"
				. " JOIN #__marketplace_categories AS c ON c.id = e.category_id"
				. " WHERE e.id = " . $id
				;
			$db->setQuery($query);
			$row = $db->loadObject();

			$link = JRoute::_("index.php?option=com_marketplace&amp;view=entry&amp;catid=".htmlspecialchars($row->categorySlug)."&amp;entry=".$row->slug.$Itemid);

			if (!empty($row)) {
				$info->category_id = $row->category_id;
				$info->title = $row->ad_headline;
				$info->userid = $row->userid;
				$info->link = $link;
			}
		}

		return $info;
	}
}