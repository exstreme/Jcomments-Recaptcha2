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
 * JComments smilies table
 *
 */
class JCommentsTableSmiley extends JTable
{
	public function __construct(&$_db)
	{
		parent::__construct('#__jcomments_smilies', 'id', $_db);
	}

	function store($updateNulls = false)
	{
		if (empty($this->name)) {
			$this->name = $this->code;
		}

		return parent::store($updateNulls);
	}
}