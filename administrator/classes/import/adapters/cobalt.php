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

class JCommentsImportCobalt extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'cobalt';
		$this->extension = 'com_cobalt';
		$this->name = 'Cobalt';
		$this->author = 'MintJoomla';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/copyleft/gpl.html';
		$this->siteUrl = 'http://www.mintjoomla.com/joomla-components/cobalt.html';
		$this->tableName = '#__js_res_comments';
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
		$query->order($db->escape('c.ctime'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->record_id;
			$table->object_group = 'com_cobalt';
			$table->parent = $row->parent_id;
			$table->userid = $row->user_id;
			$table->name = isset($row->user_name) ? $row->user_name : $row->name;
			$table->username = isset($row->user_username) ? $row->user_username : $row->name;
			$table->email = isset($row->user_email) ? $row->user_email : $row->email;
			$table->comment = $row->comment;
			$table->ip = $row->ip;
			$table->published = $row->published;
			$table->date = $row->ctime;
			$table->thread_id = $row->root_id;
			$table->level = $row->level - 1;
			$table->lang = $row->langs != '' ? $row->langs : $language;
			$table->source_id = $row->id;
			$table->source = $source;
			$table->store();
		}
	}
}