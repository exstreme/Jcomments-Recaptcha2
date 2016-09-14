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

class JCommentsImportVirtuemart extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'virtuemart';
		$this->extension = 'com_virtuemart';
		$this->name = 'VirtueMart Reviews';
		$this->author = 'The VirtueMart Development Team';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/licenses/gpl-2.0.html';
		$this->siteUrl = 'http://www.virtuemart.net/';
		$this->tableName = '#__vm_product_reviews';
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
		$query->order($db->escape('c.time'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->product_id;
			$table->object_group = 'com_virtuemart';
			$table->parent = 0;
			$table->userid = $row->userid;
			$table->name = $row->name;
			$table->username = $row->username;
			$table->comment = $row->comment;
			$table->email = $row->email;
			$table->published = $row->published;
			$table->date = strftime("%Y-%m-%d %H:%M:00", $row->time);
			$table->lang = $language;
			$table->source = $source;
			$table->store();
		}
	}
}