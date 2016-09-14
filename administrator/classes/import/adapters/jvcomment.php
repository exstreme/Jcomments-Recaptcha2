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

class JCommentsImportJVComment extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'jvcomment';
		$this->extension = 'com_jvcomment';
		$this->name = 'JV Comment';
		$this->author = 'Open Source Code Solutions Co';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/licenses/gpl-3.0.html';
		$this->siteUrl = 'http://www.joomlavi.com';
		$this->tableName = '#__jvcomment_comment';
	}

	public function execute($language, $start = 0, $limit = 100)
	{
		$db = JFactory::getDBO();
		$source = $this->getCode();

		$query = $db->getQuery(true);

		$query->select('c.*');
		$query->from($db->quoteName($this->tableName) . ' AS c');
		$query->select('u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.user_id = u.id');
		$query->order($db->escape('c.datecreated'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->object_id;
			$table->object_group = $row->object_group;
			$table->parent = $row->parent_id;
			$table->userid = $row->user_id;
			$table->name = isset($row->user_name) ? $row->user_name : $row->guest_name;
			$table->username = isset($row->user_username) ? $row->user_username : $row->guest_name;
			$table->email = isset($row->user_email) ? $row->user_email : $row->guest_email;
			$table->title = $row->title;
			$table->comment = $row->comment;
			$table->published = $row->state == 1;
			$table->date = $row->datecreated;
			$table->lang = $language;
			$table->source_id = $row->id;
			$table->source = $source;
			$table->store();
		}
	}
}