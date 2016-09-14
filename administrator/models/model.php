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

jimport('joomla.application.component.model');

if (version_compare(JVERSION, '3.0', 'ge')) {
	class JCommentsModelLegacy extends JModelLegacy
	{
		public static function addIncludePath($path = '', $prefix = '')
		{
			return parent::addIncludePath($path, $prefix);
		}
	}
} else {
	class JCommentsModelLegacy extends JModel
	{
		public static function addIncludePath($path = '', $prefix = '')
		{
			return parent::addIncludePath($path, $prefix);
		}
	}
}