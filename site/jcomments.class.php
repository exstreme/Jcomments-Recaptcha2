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

ob_start();
require_once(JCOMMENTS_SITE . '/jcomments.legacy.php');
require_once(JCOMMENTS_HELPERS . '/object.php');
ob_end_clean();

// classes
JLoader::register('JCommentsCfg', JCOMMENTS_CLASSES . '/config.php');
JLoader::register('JCommentsACL', JCOMMENTS_CLASSES . '/acl.php');
JLoader::register('JCommentsSmilies', JCOMMENTS_CLASSES . '/smilies.php');
JLoader::register('JCommentsPlugin', JCOMMENTS_CLASSES . '/plugin.php');
JLoader::register('JCommentsText', JCOMMENTS_CLASSES . '/text.php');
JLoader::register('JCommentsBBCode', JCOMMENTS_CLASSES . '/bbcode.php');
JLoader::register('JCommentsCustomBBCode', JCOMMENTS_CLASSES . '/custombbcode.php');
JLoader::register('JCommentsSecurity', JCOMMENTS_CLASSES . '/security.php');
JLoader::register('JCommentsMultilingual', JCOMMENTS_CLASSES . '/multilingual.php');
JLoader::register('JCommentsFactory', JCOMMENTS_CLASSES . '/factory.php');
JLoader::register('JCommentsObjectInfo', JCOMMENTS_CLASSES . '/objectinfo.php');

// helpers
JLoader::register('JCommentsObjectHelper', JCOMMENTS_HELPERS . '/object.php');
JLoader::register('JCommentsEventHelper', JCOMMENTS_HELPERS . '/event.php');
JLoader::register('JCommentsNotificationHelper', JCOMMENTS_HELPERS . '/notification.php');