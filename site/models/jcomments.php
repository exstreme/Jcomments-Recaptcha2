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

/**
 * JComments model
 */
class JCommentsModel
{
	/**
	 * Returns a comments count for given object
	 *
	 * @param array $options
	 * @param boolean $noCache
	 * @return int
	 */
	public static function getCommentsCount($options = array(), $noCache = false)
	{
		static $cache = array();

		$key = md5(serialize($options));

		if (!isset($cache[$key]) || $noCache == true) {
			$db = JFactory::getDbo();
			$db->setQuery(self::_getCommentsCountQuery($options));
			$cache[$key] = (int) $db->loadResult();
		}

		return $cache[$key];
	}

	/**
	 * Returns list of comments
	 *
	 * @param array $options
	 * @return array
	 */
	public static function getCommentsList($options = array())
	{
		if (!isset($options['orderBy'])) {
			$options['orderBy'] = self::_getDefaultOrder();
		}

		$db = JFactory::getDbo();

		$pagination = isset($options['pagination']) ? $options['pagination'] : '';

		if (isset($options['limit']) && $pagination == 'tree') {

			$options['level'] = 0;
			
			$db->setQuery(self::_getCommentsQuery($options));
			$rows = $db->loadObjectList();

			if (count($rows)) {
				$threads = array();
				foreach ($rows as $row){
					$threads[] = $row->id;
				}

				unset($options['level']);
				unset($options['limit']);

				$options['filter'] = ($options['filter'] ? $options['filter'] . ' AND ' : '') . 'c.thread_id IN (' . join(', ', $threads). ')';

				$db->setQuery(self::_getCommentsQuery($options));
				$rows = array_merge($rows, $db->loadObjectList());
			}
		} else {
			$db->setQuery(self::_getCommentsQuery($options));
			$rows = $db->loadObjectList();
		}

		return $rows;
	}

	public static function getLastComment($object_id, $object_group = 'com_content', $parent = 0)
	{
		$comment = null;

		$db = JFactory::getDbo();
		$config = JCommentsFactory::getConfig();

		$options['object_id'] = (int) $object_id;
		$options['object_group'] = trim($object_group);
		$options['parent'] = (int) $parent;
		$options['published'] = 1;
		$options['orderBy'] = 'c.date DESC';
		$options['limit'] = 1;
		$options['limitStart'] = 0;
		$options['votes'] = $config->getInt('enable_voting');

		$db->setQuery(self::_getCommentsQuery($options));
		$rows = $db->loadObjectList();
		if (count($rows)) {
			$comment = $rows[0];
		}

		return $comment;
	}

	/**
	 * Delete all comments for given ids
	 *
	 * @param  $ids Array of comments ids
	 * @return void
	 */
	public static function deleteCommentsByIds($ids)
	{
		if (is_array($ids)) {
			if (count($ids)) {
				$db = JFactory::getDbo();
				$db->setQuery("SELECT DISTINCT object_group, object_id FROM #__jcomments WHERE parent IN (" . implode(',', $ids) . ")");
				$objects = $db->loadObjectList();

				if (count($objects)) {
					require_once (JCOMMENTS_LIBRARIES . '/joomlatune/tree.php');

					$descendants = array();

					foreach ($objects as $o) {
						$query = "SELECT id, parent"
								. "\nFROM #__jcomments"
								. "\nWHERE `object_group` = " . $db->Quote($o->object_group)
								. "\nAND `object_id` = " . $db->Quote($o->object_id);
						$db->setQuery($query);
						$comments = $db->loadObjectList();

						$tree = new JoomlaTuneTree($comments);

						foreach ($ids as $id) {
							$descendants = array_merge($descendants, $tree->descendants((int) $id));
						}
						unset($tree);
						$descendants = array_unique($descendants);
					}
					$ids = array_merge($ids, $descendants);
				}
				unset($descendants);

				$ids = implode(',', $ids);

				$db->setQuery("DELETE FROM #__jcomments WHERE id IN (" . $ids . ")");
				$db->execute();

				$db->setQuery("DELETE FROM #__jcomments_votes WHERE commentid IN (" . $ids . ")");
				$db->execute();

				$db->setQuery("DELETE FROM #__jcomments_reports WHERE commentid IN (" . $ids . ")");
				$db->execute();
			}
		}
	}

	public static function deleteComments($object_id, $object_group = 'com_content')
	{
		$object_group = trim($object_group);
		$oids = is_array($object_id) ? implode(',', $object_id) : $object_id;

		$db = JFactory::getDbo();

		$query = "SELECT id FROM #__jcomments "
				. "\n WHERE object_group = " . $db->Quote($object_group)
				. "\n AND object_id IN (" . $oids . ")";
		$db->setQuery($query);
		$cids = $db->loadColumn();

		JCommentsModel::deleteCommentsByIds($cids);

		$query = "DELETE FROM #__jcomments_objects "
			. " WHERE object_group = " . $db->Quote($object_group)
			. " AND object_id = " . $db->Quote($object_id);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	protected static function _getCommentsCountQuery(&$options)
	{
		$db = JFactory::getDbo();

		$object_id = @$options['object_id'];
		$object_group = @$options['object_group'];
		$published = @$options['published'];
		$userid = @$options['userid'];
		$parent = @$options['parent'];
		$level = @$options['level'];
		$filter = @$options['filter'];

		$where = array();

		if (!empty($object_id)) {
			$where[] = "c.object_id = " . (int) $object_id;
		}

		if (!empty($object_group)) {
			$where[] = "c.object_group = " . $db->Quote($object_group);
		}

		if ($parent !== null) {
			$where[] = "c.parent = " . (int) $parent;
		}

		if ($level !== null) {
			$where[] = "c.level = " . (int) $level;
		}

		if ($published !== null) {
			$where[] = "c.published = " . (int) $published;
		}

		if ($userid !== null) {
			$where[] = "c.userid = " . (int) $userid;
		}

		if ($filter != "") {
			$where[] = $filter;
		}

		if (JCommentsMultilingual::isEnabled()) {
			$where[] = "c.lang = '" . JCommentsMultilingual::getLanguage() . "'";
		}

		$query = "SELECT count(*)"
				. "\nFROM #__jcomments AS c"
				. (count($where) ? ("\nWHERE " . implode(' AND ', $where)) : "");

		return $query;
	}

	protected static function _getCommentsQuery(&$options)
	{
		$acl = JCommentsFactory::getACL();
		$db = JFactory::getDbo();

		$object_id = @$options['object_id'];
		$object_group = @$options['object_group'];
		$parent = @$options['parent'];
		$level = @$options['level'];
		$published = @$options['published'];
		$userid = @$options['userid'];
		$filter = @$options['filter'];

		$orderBy = @$options['orderBy'];
		$limitStart = isset($options['limitStart']) ? $options['limitStart'] : 0;
		$limit = @$options['limit'];

		$votes = isset($options['votes']) ? $options['votes'] : true;
		$objectinfo = isset($options['objectinfo']) ? $options['objectinfo'] : false;

		$where = array();

		if (!empty($object_id)) {
			$where[] = "c.object_id = " . $object_id;
		}

		if (!empty($object_group)) {
			if (is_array($object_group)) {
				$where[] = "(c.object_group = '" . implode("' OR c.object_group = '", $object_group) . "')";
			} else {
				$where[] = "c.object_group = " . $db->Quote($object_group);
			}
		}

		if ($parent !== null) {
			$where[] = "c.parent = " . $parent;
		}

		if ($level !== null) {
			$where[] = "c.level = " . (int) $level;
		}

		if ($published !== null) {
			$where[] = "c.published = " . $published;
		}

		if ($userid !== null) {
			$where[] = "c.userid = " . $userid;
		}

		if (JCommentsMultilingual::isEnabled()) {
			$language = isset($options['lang']) ? $options['lang'] : JCommentsMultilingual::getLanguage();
			$where[] = "c.lang = " . $db->Quote($language);
		}

		if ($objectinfo && isset($options['access'])) {
			if (is_array($options['access'])) {
				$access = implode(',', $options['access']);
				$where[] = "jo.access IN (" . $access . ")";
			} else {
				$where[] = "jo.access <= " . (int) $options['access'];
			}
		}

		if ($filter != "") {
			$where[] = $filter;
		}

		$query = "SELECT c.id, c.parent, c.object_id, c.object_group, c.userid, c.name, c.username, c.title, c.comment"
				. "\n, c.email, c.homepage, c.date, c.date as datetime, c.ip, c.published, c.deleted, c.checked_out, c.checked_out_time"
				. "\n, c.isgood, c.ispoor"
				. ($votes ? "\n, v.value as voted" : "\n, 1 as voted")
				. "\n, case when c.parent = 0 then unix_timestamp(c.date) else 0 end as threaddate"
				. ($objectinfo ? "\n, jo.title AS object_title, jo.link AS object_link, jo.access AS object_access" : ", '' AS object_title, '' AS object_link, 0 AS object_access, 0 AS object_owner")
				. "\nFROM #__jcomments AS c"
				. ($votes ? "\nLEFT JOIN #__jcomments_votes AS v ON c.id = v.commentid " . ($acl->getUserId() ? " AND  v.userid = " . $acl->getUserId() : " AND v.userid = 0 AND v.ip = '" . $acl->getUserIP() . "'") : "")
				. ($objectinfo ? "\n LEFT JOIN #__jcomments_objects AS jo ON jo.object_id = c.object_id AND jo.object_group = c.object_group AND jo.lang=c.lang" : "")
				. (count($where) ? ("\nWHERE " . implode(' AND ', $where)) : "")
				. "\nORDER BY " . $orderBy
				. (($limit > 0) ? "\nLIMIT $limitStart, $limit" : "");

		return $query;
	}

	/**
	 * Returns default order for comments list
	 *
	 * @return string
	 */
	protected static function _getDefaultOrder()
	{
		$config = JCommentsFactory::getConfig();

		if ($config->get('template_view') == 'tree') {
			switch($config->getInt('comments_tree_order')) {
				case 2:
					$result = 'threadDate DESC, c.date ASC';
					break;
				case 1:
					$result = 'c.parent, c.date DESC';
					break;
				default:
					$result = 'c.parent, c.date ASC';
					break;
			}
		} else {
			$result = 'c.date ' . $config->get('comments_list_order');
		}

		return $result;
	}
}