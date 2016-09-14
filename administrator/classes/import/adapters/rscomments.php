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

class JCommentsImportRSComments extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'rscomments';
		$this->extension = 'com_rscomments';
		$this->name = 'RSComments';
		$this->author = 'RSJoomla';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/licenses/gpl-2.0.html';
		$this->siteUrl = 'http://www.rsjoomla.com/joomla-components/joomla-comments.html';
		$this->tableName = '#__rscomments_comments';
	}

	public function execute($language, $start = 0, $limit = 100)
	{
		$db = JFactory::getDBO();
		$source = $this->getCode();

		$query = $db->getQuery(true);

		$query->select('c.*');
		$query->from($db->quoteName($this->tableName) . ' AS c');
		$query->select('u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.uid = u.id');
		$query->order($db->escape('c.date'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->id;
			$table->object_group = $row->option;
			$table->parent = isset($row->parent_id) ? $row->parent_id : (isset($row->IdParent) ? $row->IdParent : 0);
			$table->userid = $row->uid;
			$table->name = isset($row->user_name) ? $row->user_name : $row->name;
			$table->username = $row->username;
			$table->email = isset($row->user_email) ? $row->user_email : $row->email;
			$table->homepage = $row->website;
			$table->ip = $row->ip;
			$table->title = $row->subject;
			$table->comment = $row->comment;
			$table->published = $row->published;
			$table->date = $row->date;
			$table->lang = $language;
			$table->source = $source;
			$table->store();
		}
	}
}