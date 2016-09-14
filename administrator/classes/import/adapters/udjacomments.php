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

class JCommentsImportUdjaComments extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'udjacomments';
		$this->extension = 'com_udjacomments';
		$this->name = 'Udja Comments';
		$this->author = 'Andy Sharman';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/licenses/gpl-2.0.html';
		$this->siteUrl = 'http://www.udjamaflip.com';
		$this->tableName = '#__udjacomments';
	}

	public function execute($language, $start = 0, $limit = 100)
	{
		static $cache = array();

		$db = JFactory::getDBO();
		$source = $this->getCode();

		$query = $db->getQuery(true);

		$query->select('c.*');
		$query->from($db->quoteName($this->tableName) . ' AS c');
		$query->select('u.id as user_id, u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON u.name = c.full_name AND u.email = c.email');
		$query->order($db->escape('c.time_added'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			if (!isset($cache[$row->comment_url])) {
				$cache[$row->comment_url] = null;

				if (preg_match('#[\/\?\=]#', $row->comment_url)) {
					// TODO Implement SEF URL reversing and etc.
				} else {
					$parts = preg_split('/:/', $row->comment_url);
					if (count($parts) == 2) {
						$object = new StdClass;
						$object->option = $parts[0];
						$object->id = $parts[1];

						$cache[$row->comment_url] = $object;
					}
				}
			}

			if ($cache[$row->comment_url] !== null) {
				$table = JTable::getInstance('Comment', 'JCommentsTable');
				$table->object_id = $cache[$row->comment_url]->id;
				$table->object_group = $cache[$row->comment_url]->option;
				$table->parent = $row->parent_id;
				$table->userid = isset($row->user_id) ? $row->user_id : 0;
				$table->name = $row->full_name;
				$table->username = isset($row->user_username) ? $row->user_username : $row->full_name;
				$table->email = $row->email;
				$table->comment = $row->content;
				$table->ip = $row->ip;
				$table->homepage = $row->url;
				$table->published = $row->is_published;
				$table->date = $row->time_added;
				$table->source = $source;
				$table->source_id = $row->id;
				$table->lang = $language;
				$table->store();
			}
		}
	}
}