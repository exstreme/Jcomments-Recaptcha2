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

class JCommentsImportEasyBlog extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'easyblog';
		$this->extension = 'com_easyblog';
		$this->name = 'EasyBlog Comments';
		$this->author = 'Stack Ideas';
		$this->license = 'GPL License';
		$this->licenseUrl = 'http://www.gnu.org/licenses/gpl-2.0.html';
		$this->siteUrl = 'http://stackideas.com/easyblog.html';
		$this->tableName = '#__easyblog_comment';
	}

	public function execute($language, $start = 0, $limit = 100)
	{
		$db = JFactory::getDBO();
		$source = $this->getCode();

		$query = $db->getQuery(true);

		$query->select('c.*');
		$query->from($db->quoteName($this->tableName) . ' AS c');
		$query->select('u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.created_by = u.id');
		$query->order($db->escape('c.created'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->post_id;
			$table->object_group = 'com_easyblog';
			$table->parent = $row->parent_id;
			$table->userid = $row->created_by;
			$table->name = isset($row->user_name) ? $row->user_name : $row->name;
			$table->username = isset($row->user_username) ? $row->user_username : $row->name;
			$table->email = isset($row->user_email) ? $row->user_email : $row->email;;
			$table->homepage = $row->url;
			$table->comment = $row->comment;
			$table->title = $row->title;
			$table->ip = $row->ip;
			$table->published = $row->published;
			$table->date = $row->created;
			$table->isgood = $row->vote > 0 ? $row->vote : 0;
			$table->ispoor = $row->vote < 0 ? $row->vote : 0;
			$table->lang = $language;
			$table->source_id = $row->id;
			$table->source = $source;
			$table->store();
		}
	}
}