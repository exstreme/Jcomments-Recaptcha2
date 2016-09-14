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
 * JComments mail queue table
 */
class JCommentsTableMailq extends JTable
{
	/** @var int Primary key */
	var $id = null;
	/** @var string */
	var $name = null;
	/** @var string */
	var $email = null;
	/** @var string */
	var $subject = null;
	/** @var string */
	var $body = null;
	/** @var datetime */
	var $created = null;
	/** @var int */
	var $attempts = null;
	/** @var int */
	var $priority = null;
	/** @var string */
	var $session_id = null;

	public function __construct(&$_db)
	{
		parent::__construct('#__jcomments_mailq', 'id', $_db);
	}
}