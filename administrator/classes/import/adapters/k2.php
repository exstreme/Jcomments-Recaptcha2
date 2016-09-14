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

class JCommentsImportK2 extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'k2';
		$this->extension = 'com_k2';
		$this->name = 'K2 Comments';
		$this->author = 'JoomlaWorks';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/licenses/gpl-2.0.html';
		$this->siteUrl = 'http://getk2.org/';
		$this->tableName = '#__k2_comments';
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
		$query->order($db->escape('c.commentDate'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->itemID;
			$table->object_group = 'com_k2';
			$table->parent = 0;
			$table->userid = isset($row->userID) ? intval($row->userID) : 0;
			$table->name = isset($row->userName) ? $row->userName : $row->name;
			$table->username = isset($row->userName) ? $row->userName : $row->name;
			$table->comment = $row->commentText;
			$table->email = $row->commentEmail;
			$table->homepage = $row->commentURL;
			$table->ip = '';
			$table->published = $row->published;
			$table->date = $row->commentDate;
			$table->lang = $language;
			$table->source = $source;
			$table->store();
		}
	}
}