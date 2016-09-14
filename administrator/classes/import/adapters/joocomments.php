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

class JCommentsImportJooComments extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'joocomments';
		$this->extension = 'com_joocomments';
		$this->name = 'JooComments';
		$this->author = 'BullRaider';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/licenses/gpl-2.0.html';
		$this->siteUrl = 'http://www.bullraider.com/';
		$this->tableName = '#__joocomments';
	}

	public function execute($language, $start = 0, $limit = 100)
	{
		$db = JFactory::getDBO();
		$source = $this->getCode();

		$query = $db->getQuery(true);

		$query->select('c.*');
		$query->from($db->quoteName($this->tableName) . ' AS c');
		$query->order($db->escape('c.publish_date'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->article_id;
			$table->object_group = 'com_content';
			$table->parent = 0;
			$table->userid = 0;
			$table->name = $row->name;
			$table->username = $row->name;
			$table->comment = $row->comment;
			$table->email = $row->email;
			$table->homepage = $row->website;
			$table->published = $row->published;
			$table->date = $row->publish_date;
			$table->lang = $language;
			$table->source = $source;
			$table->store();
		}
	}
}