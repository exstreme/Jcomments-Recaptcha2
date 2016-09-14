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
 * JComments CustomBBCodes table
 */
class JCommentsTableCustomBBCode extends JTable
{
	public function __construct(&$_db)
	{
		parent::__construct('#__jcomments_custom_bbcodes', 'id', $_db);
	}

	public function check()
	{
		if (empty($this->ordering)) {
			$this->ordering = self::getNextOrder();
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		parent::store($updateNulls);

		return true;
	}
}