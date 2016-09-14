<?php
/**
 * JComments - Joomla Comment System
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class JCommentsModelObject
{
	public static function getObjectInfo($object_id, $object_group, $language)
	{
		$db = JFactory::getDbo();

		$query = "SELECT * "
			. " FROM `#__jcomments_objects`"
			. " WHERE `object_id` = " . $db->Quote($object_id)
			. " AND `object_group` = " . $db->Quote($object_group)
			. " AND `lang` = " . $db->Quote($language)
			;

		$db->setQuery($query);
		$info = $db->loadObject();

		return empty($info) ? false : $info;
	}

	public static function setObjectInfo($objectId, $info)
	{
		$db = JFactory::getDbo();

		if (!empty($objectId)) {
			$query = "UPDATE #__jcomments_objects"
				. " SET "
				. "  `access` = " . (int) $info->access
				. ", `userid` = " . (int) $info->userid
				. ", `expired` = 0"
				. ", `modified` = " . $db->Quote(JFactory::getDate()->toSql())
				. (empty($info->title) ? "" : ", `title` = " . $db->Quote($info->title))
				. (empty($info->link) ? "" : ", `link` = " . $db->Quote($info->link))
				. (empty($info->category_id) ? "" : ", `category_id` = " . (int) $info->category_id)
				. " WHERE `id` = " . (int) $objectId . ";"
				;
		} else {
			$query = "INSERT INTO #__jcomments_objects"
				. " SET "
				. "  `object_id` = " . (int) $info->object_id
				. ", `object_group` = " . $db->Quote($info->object_group)
				. ", `category_id` = " . (int) $info->category_id
				. ", `lang` = " . $db->Quote($info->lang)
				. ", `title` = " . $db->Quote($info->title)
				. ", `link` = " . $db->Quote($info->link)
				. ", `access` = " . (int) $info->access
				. ", `userid` = " . (int) $info->userid
				. ", `expired` = 0"
				. ", `modified` = " . $db->Quote(JFactory::getDate()->toSql())
				;
		}

		$db->setQuery($query);
		$db->execute();
	}

	public static function IsEmpty($object)
	{
		return empty($object->title) && empty($object->link);
	}
}