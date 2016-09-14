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
 * JComments blacklist table
 */
class JCommentsTableBlacklist extends JTable
{
	public function __construct(&$_db)
	{
		parent::__construct('#__jcomments_blacklist', 'id', $_db);
	}

	public function check()
	{
		if ($this->ip == $_SERVER['REMOTE_ADDR']) {
			$this->setError(JText::_('A_BLACKLIST_ERROR_YOU_CAN_NOT_BAN_YOUR_IP'));

			return false;
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		if (empty($this->id)) {
			$this->created_by = (int) JFactory::getUser()->id;
			$this->created = JFactory::getDate()->toSql();
		}

		parent::store($updateNulls);

		return true;
	}
}