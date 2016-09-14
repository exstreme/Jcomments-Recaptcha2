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

class JCommentsImportCComment extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'ccomment';
		$this->extension = 'com_comment';
		$this->name = 'CComment';
		$this->author = 'Compojoom.com';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/copyleft/gpl.html';
		$this->siteUrl = 'https://compojoom.com';
		$this->tableName = '#__comment';
	}

	public function execute($language, $start = 0, $limit = 100)
	{
		$db = JFactory::getDBO();
		$source = $this->getCode();

		$query = $db->getQuery(true);

		$query->select('c.*');
		$query->from($db->quoteName($this->tableName) . ' AS c');
		$query->select('u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.userid = u.id');
		$query->order($db->escape('c.date'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->contentid;
			$table->object_group = $row->component;
			$table->parent = $row->parentid;
			$table->userid = $row->userid;
			$table->name = isset($row->user_name) ? $row->user_name : $row->name;
			$table->username = isset($row->user_username) ? $row->user_username : $row->name;
			$table->email = isset($row->user_email) ? $row->user_email : $row->email;
			$table->homepage = $row->website;
			$table->title = $row->title;
			$table->comment = $row->comment;
			$table->ip = $row->ip;
			$table->published = $row->published;
			$table->date = $row->date;
			$table->isgood = $row->voting_yes;
			$table->ispoor = $row->voting_no;
			$table->lang = $language;
			$table->source_id = $row->id;
			$table->source = $source;
			$table->store();
		}
	}
}