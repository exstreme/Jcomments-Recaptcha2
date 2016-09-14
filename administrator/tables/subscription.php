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

/**
 * JComments subscriptions table
 *
 */
class JCommentsTableSubscription extends JTable
{
	/** @var int Primary key */
	var $id = null;
	/** @var int */
	var $object_id = null;
	/** @var string */
	var $object_group = null;
	/** @var string */
	var $lang = null;
	/** @var int */
	var $userid = null;
	/** @var string */
	var $name = null;
	/** @var string */
	var $email = null;
	/** @var string */
	var $hash = null;
	/** @var boolean */
	var $published = null;

	public function __construct(&$_db)
	{
		parent::__construct('#__jcomments_subscriptions', 'id', $_db);
	}

	function store($updateNulls = false)
	{
		if ($this->userid != 0 && empty($this->email)) {
			$user = JFactory::getUser($this->userid);
			$this->email = $user->email;
		}

		if ($this->userid == 0 && !empty($this->email)) {
			$db = JFactory::getDBO();

			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('email') . ' = ' . $db->Quote($db->escape($this->email, true)));
			$db->setQuery($query);

			$users = $db->loadObjectList();

			if (count($users)) {
				$this->userid = $users[0]->id;
				$this->name = $users[0]->name;
			}
		}

		if (empty($this->lang)) {
			$this->lang = JCommentsMultilingual::getLanguage();
		}

		$this->hash = $this->getHash();
		return parent::store($updateNulls);
	}

	function getHash()
	{
		return md5($this->object_id . $this->object_group . $this->userid . $this->email . $this->lang);
	}
}