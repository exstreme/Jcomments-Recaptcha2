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

class JCommentsVersion
{
	/** @var string Product */
	var $PRODUCT = 'JComments';
	/** @var int Main Release Level */
	var $RELEASE = '3.0';
	/** @var int Sub Release Level */
	var $DEV_LEVEL = '7.8';
	/** @var string Development Status */
	var $DEV_STATUS = '';
	/** @var int Build Number */
	var $BUILD = '';
	/** @var string Date */
	var $RELDATE = '19/09/2021';
	/** @var string Time */
	var $RELTIME = '16:30';
	/** @var string Timezone */
	var $RELTZ = 'GMT+2';

	/**
	 * @return string Long format version
	 */
	function getLongVersion()
	{
		return trim($this->PRODUCT . ' ' . $this->RELEASE . '.' . $this->DEV_LEVEL . ($this->BUILD ? '.' . $this->BUILD : '') . ' ' . $this->DEV_STATUS);
	}

	/**
	 * @return string Short version format
	 */
	function getShortVersion()
	{
		return $this->RELEASE . '.' . $this->DEV_LEVEL;
	}

	/**
	 * @return string Version
	 */
	function getVersion()
	{
		return trim($this->RELEASE . '.' . $this->DEV_LEVEL . ($this->BUILD ? '.' . $this->BUILD : '') . ' ' . $this->DEV_STATUS);
	}

	/**
	 * @return string Release date
	 */
	function getReleaseDate()
	{
		return $this->RELDATE;
	}
}