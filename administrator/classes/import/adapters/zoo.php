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

class JCommentsImportZoo extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'zoo';
		$this->extension = 'com_zoo';
		$this->name = 'ZOO Comments';
		$this->author = 'YOOtheme';
		$this->license = 'GNU/GPL v2';
		$this->licenseUrl = 'http://www.gnu.org/licenses/gpl-2.0.html';
		$this->siteUrl = 'http://www.yootheme.com/zoo/';
		$this->tableName = '#__zoo_comment';
	}

	public function execute($language, $start = 0, $limit = 100)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$source = $this->getCode();

		$query->select('c.*');
		$query->from($db->quoteName($this->tableName) . ' AS c');
		$query->select('u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.user_id = u.id');
		$query->order($db->escape('c.created'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->item_id;
			$table->object_group = 'com_zoo';
			$table->parent = $row->parent_id;
			$table->userid = $row->user_id;
			$table->name = $row->user_name;
			$table->username = $row->author;
			$table->comment = $row->content;
			$table->ip = $row->ip;
			$table->email = $row->user_email;
			$table->homepage = $row->url;
			$table->published = $row->state == 1;
			$table->date = $row->created;
			$table->lang = $language;
			$table->source_id = $row->id;
			$table->source = $source;
			$table->store();
		}
	}
}