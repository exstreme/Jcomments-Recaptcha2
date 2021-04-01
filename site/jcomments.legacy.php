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
 * Legacy for previous versions
 */

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JCOMMENTS_ENCODING')) {
	DEFINE('JCOMMENTS_ENCODING', 'utf-8');
}

if (!defined('JCOMMENTS_PCRE_UTF8')) {
	DEFINE('JCOMMENTS_PCRE_UTF8', 'u');
}

if (!defined('JCOMMENTS_SITE')) { 
	define('JCOMMENTS_SITE', JPATH_ROOT . '/components/com_jcomments');
}
define('JCOMMENTS_ADMINISTRATOR', JPATH_ROOT . '/administrator/components/com_jcomments');
define('JCOMMENTS_LIBRARIES', JCOMMENTS_SITE . '/libraries');
define('JCOMMENTS_MODELS', JCOMMENTS_SITE . '/models');
if (!defined('JCOMMENTS_HELPERS')) { 
	define('JCOMMENTS_HELPERS', JCOMMENTS_SITE . '/helpers');
}
define('JCOMMENTS_CLASSES', JCOMMENTS_SITE . '/classes');
define('JCOMMENTS_TABLES', JCOMMENTS_ADMINISTRATOR . '/tables');

define('JCOMMENTS_BASE', JCOMMENTS_SITE);

if (version_compare(JVERSION, '1.7', 'ge')) {
	define('JCOMMENTS_JVERSION', '1.7');
}

$option = JFactory::getApplication()->input->get('option');
if ($option !== 'com_jcomments') {
	JFactory::getLanguage()->load('com_jcomments', JPATH_SITE);
}