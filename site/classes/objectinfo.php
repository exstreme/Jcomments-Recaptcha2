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
 * JComments object
 */
class JCommentsObjectInfo
{
	/** @var int */
	var $id = null;
	/** @var int */
	var $object_id = null;
	/** @var string */
	var $object_group = null;
	/** @var int */
	var $category_id = null;
	/** @var string */
	var $lang = null;
	/** @var string */
	var $title = null;
	/** @var string */
	var $link = null;
	/** @var int */
	var $access = null;
	/** @var int */
	var $userid = null;
	/** @var int */
	var $expired = null;
	/** @var datetime */
	var $modified = null;

	function __construct($src = null)
	{
		if ($src !== null && is_object($src)) {
			$vars = get_object_vars($this);
			foreach ($vars as $k => $v) {
				if (isset($src->$k)) {
					$this->$k = $src->$k;
				}
			}
		}
	}
}