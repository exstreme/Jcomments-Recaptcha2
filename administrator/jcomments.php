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

if (!JFactory::getUser()->authorise('core.manage', 'com_jcomments')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

if (!defined('JPATH_COMPONENT')) {
	define('JPATH_COMPONENT', dirname(__FILE__));
}

$language = JFactory::getLanguage();
$language->load('com_jcomments', JPATH_ROOT . '/administrator', 'en-GB', true);
$language->load('com_jcomments', JPATH_ROOT . '/administrator', null, true);

require_once(JPATH_ROOT . '/components/com_jcomments/jcomments.legacy.php');
require_once(JPATH_ROOT . '/components/com_jcomments/jcomments.class.php');

JLoader::register('JCommentsControllerLegacy', JPATH_COMPONENT . '/controllers/controller.php');
JLoader::register('JCommentsControllerForm', JPATH_COMPONENT . '/controllers/controllerform.php');
JLoader::register('JCommentsControllerList', JPATH_COMPONENT . '/controllers/controllerlist.php');
JLoader::register('JCommentsModelLegacy', JPATH_COMPONENT . '/models/model.php');
JLoader::register('JCommentsModelForm', JPATH_COMPONENT . '/models/modelform.php');
JLoader::register('JCommentsModelList', JPATH_COMPONENT . '/models/modellist.php');
JLoader::register('JCommentsViewLegacy', JPATH_COMPONENT . '/views/view.php');

$controller = JControllerLegacy::getInstance('JComments');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
