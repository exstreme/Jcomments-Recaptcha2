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

jimport('joomla.plugin.plugin');
include_once(JPATH_ROOT . '/components/com_jcomments/jcomments.legacy.php');

/**
 * Search plugin
 */
class plgSearchJComments extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array $config  An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_search_jcomments', JPATH_SITE);
	}

	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas()
	{
		static $areas = array('comments' => 'PLG_SEARCH_JCOMMENTS_COMMENTS');

		return defined('JCOMMENTS_JVERSION') ? $areas : array();
	}

	/**
	 * Comments Search method
	 *
	 * @param string $text Target search string
	 * @param string $phrase mathcing option, exact|any|all
	 * @param string $ordering ordering option, newest|oldest|popular|alpha|category
	 * @param mixed $areas An array if the search it to be restricted to areas, null if search all
	 * @return array
	 */
	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$text = strtolower(trim($text));
		$result = array();

		if ($text == '' || !defined('JCOMMENTS_JVERSION')) {
			return $result;
		}

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return $result;
			}
		}
		if (file_exists(JPATH_ROOT . '/components/com_jcomments/jcomments.php')) {
			require_once(JPATH_ROOT . '/components/com_jcomments/jcomments.php');

			$db = JFactory::getDBO();
			$limit = $this->params->def('search_limit', 50);

			switch ($phrase) {
				case 'exact':
					$text = $db->Quote('%' . $db->escape($text, true) . '%', false);
					$wheres2[] = "LOWER(c.name) LIKE " . $text;
					$wheres2[] = "LOWER(c.comment) LIKE " . $text;
					$wheres2[] = "LOWER(c.title) LIKE " . $text;
					$where = '(' . implode(') OR (', $wheres2) . ')';
					break;
				case 'all':
				case 'any':
				default:
					$words = explode(' ', $text);
					$wheres = array();
					foreach ($words as $word) {
						$word = $db->Quote('%' . $db->escape($word, true) . '%', false);
						$wheres2 = array();
						$wheres2[] = "LOWER(c.name) LIKE " . $word;
						$wheres2[] = "LOWER(c.comment) LIKE " . $word;
						$wheres2[] = "LOWER(c.title) LIKE " . $word;
						$wheres[] = implode(' OR ', $wheres2);
					}
					$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
					break;
			}

			switch ($ordering) {
				case 'oldest':
					$order = 'c.date ASC';
					break;
				case 'newest':
				default:
					$order = 'c.date DESC';
					break;
			}


			$acl = JCommentsFactory::getACL();
			$access = $acl->getUserAccess();

			if (is_array($access)) {
				$accessCondition = "AND jo.access IN (" . implode(',', $access) . ")";
			} else {
				$accessCondition = "AND jo.access <= " . (int)$access;
			}

			$query = "SELECT "
				. "  c.comment AS text"
				. ", c.date AS created"
				. ", '2' AS browsernav"
				. ", '" . JText::_('PLG_SEARCH_JCOMMENTS_COMMENTS') . "' AS section"
				. ", ''  AS href"
				. ", c.id"
				. ", jo.title AS object_title, jo.link AS object_link"
				. " FROM #__jcomments AS c"
				. " INNER JOIN #__jcomments_objects AS jo ON jo.object_id = c.object_id AND jo.object_group = c.object_group and jo.lang=c.lang"
				. " WHERE c.published=1"
				. " AND c.deleted=0"
				. " AND jo.link <> ''"
				. (JCommentsMultilingual::isEnabled() ? " AND c.lang = '" . JCommentsMultilingual::getLanguage() . "'" : "")
				. " AND ($where) "
				. $accessCondition
				. " ORDER BY c.object_id, $order";

			$db->setQuery($query, 0, $limit);
			$rows = $db->loadObjectList();

			$cnt = count($rows);

			if ($cnt > 0) {
				$config = JCommentsFactory::getConfig();
				$enableCensor = $acl->check('enable_autocensor');
				$word_maxlength = $config->getInt('word_maxlength');

				for ($i = 0; $i < $cnt; $i++) {
					$text = JCommentsText::cleanText($rows[$i]->text);

					if ($enableCensor) {
						$text = JCommentsText::censor($text);
					}

					if ($word_maxlength > 0) {
						$text = JCommentsText::fixLongWords($text, $word_maxlength);
					}

					if ($text != '') {
						$rows[$i]->title = $rows[$i]->object_title;
						$rows[$i]->text = $text;
						$rows[$i]->href = $rows[$i]->object_link . '#comment-' . $rows[$i]->id;
						$result[] = $rows[$i];
					}
				}
			}
			unset($rows);
		}

		return $result;
	}

	function onSearchAreas()
	{
		return $this->onContentSearchAreas();
	}

	function onSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		return $this->onContentSearch($text, $phrase, $ordering, $areas);
	}
}