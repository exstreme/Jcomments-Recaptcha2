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

class JCommentsImportImproveMyCity extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'improvemycity';
		$this->extension = 'com_improvemycity';
		$this->name = 'ImproveMyCity';
		$this->author = 'URENIO Researh Unit';
		$this->license = 'GNU/AGPL';
		$this->licenseUrl = 'http://www.gnu.org/licenses/agpl.html';
		$this->siteUrl = 'https://github.com/icos-urenio/Improve-my-city';
		$this->tableName = '#__improvemycity_comments';
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
		$query->order($db->escape('c.created'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->improvemycityid;
			$table->object_group = 'com_improvemycity';
			$table->userid = $row->userid;
			$table->name = isset($row->user_name) ? $row->user_name : null;
			$table->username = isset($row->user_username) ? $row->user_username : null;
			$table->email = isset($row->user_email) ? $row->user_email : null;
			$table->comment = $row->description;
			$table->published = $row->state == 1;
			$table->date = $row->created;
			$table->lang = $language;
			$table->source_id = $row->id;
			$table->source = $source;
			$table->store();
		}
	}
}