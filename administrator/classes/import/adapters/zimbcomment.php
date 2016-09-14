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

class JCommentsImportZimbComment extends JCommentsImportAdapter
{
	public function __construct()
	{
		$this->code = 'zimbcommentt';
		$this->extension = 'com_zimbcomment';
		$this->name = 'ZiMB Comment';
		$this->author = 'ZiMB LLC';
		$this->license = 'GNU/GPL';
		$this->licenseUrl = 'http://www.gnu.org/copyleft/gpl.html';
		$this->siteUrl = 'http://www.zimbllc.com/Software/zimbcomment';
		$this->tableName = '#__zimbcomment_comment';
	}

	public function execute($language, $start = 0, $limit = 100)
	{
		$db = JFactory::getDBO();
		$source = $this->getCode();

		$query = $db->getQuery(true);

		$query->select('c.*');
		$query->from($db->quoteName($this->tableName) . ' AS c');
		$query->select('u.username as user_username, u.name as user_name, u.email as user_email');
		$query->join('LEFT', $db->quoteName('#__users') . ' AS u ON c.iduser = u.id');
		$query->order($db->escape('c.saved'));

		$db->setQuery($query, $start, $limit);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			$table = JTable::getInstance('Comment', 'JCommentsTable');
			$table->object_id = $row->articleId;
			$table->object_group = 'com_content';
			$table->parent = 0;
			$table->userid = $row->iduser;
			$table->username = isset($row->handle) ? $row->handle : $row->username;
			$table->name = $row->name;
			$table->email = $row->email;
			$table->homepage = $row->url;
			$table->title = '';
			$table->comment = $row->content;
			$table->published = $row->published;
			$table->date = $row->saved;
			$table->source = $source;
			$table->lang = $language;
			$table->store();
		}
	}
}