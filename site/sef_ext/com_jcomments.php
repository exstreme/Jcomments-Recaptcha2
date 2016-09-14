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

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & Sh404sefFactory::getConfig();
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// remove common URL from GET vars list, so that they don't show up as query string in the URL
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');
if (!empty($Itemid)) {
	shRemoveFromGETVarsList('Itemid');
}
if (!empty($limit)) {
	shRemoveFromGETVarsList('limit');
}

if (isset($limitstart)) {
	shRemoveFromGETVarsList('limitstart');
}

// start by inserting the menu element title (just an idea, this is not required at all)
$task = isset($task) ? $task : null;
$Itemid = isset($Itemid) ? $Itemid : null;

$shJCommentsName = shGetComponentPrefix($option);
$shJCommentsName = empty($shJCommentsName) ?  getMenuTitle($option, null, $Itemid, null, $shLangName) : $shJCommentsName;
$shJCommentsName = (empty($shJCommentsName) || $shJCommentsName == '/') ? 'Comments' : $shJCommentsName;

switch ($task) {
	case 'captcha':
		$title[] = $shJCommentsName;
		$title[] = 'captcha';
		break;

	case 'rss':
		$title[] = $shJCommentsName;
		$title[] = 'rss';
		break;

	case 'rss_full':
		$title[] = $shJCommentsName;
		$title[] = 'feed';
		$title[] = 'full';
		break;

	case 'rss_user':
		$title[] = $shJCommentsName;
		$title[] = 'feed';
		$title[] = 'user';
		if (isset($userid)) {
			$title[] = $userid;
			shRemoveFromGETVarsList('userid');
		}
		break;

	case 'unsubscribe':
		$title[] = $shJCommentsName;
		$title[] = 'unsubscribe';
		break;

	case 'cmd':
		$title[] = $shJCommentsName;
		if (isset($cmd)) {
			$title[] = $$cmd;
			shRemoveFromGETVarsList('cmd');
		}

		if (isset($id)) {
			$title[] = $id;
			shRemoveFromGETVarsList('id');
		}
		break;

	case 'notifications-cron':
	case 'refreshObjectsAjax':
		$dosef = false;
		break;

	default:
		$title[] = $shJCommentsName;
		$title[] = $task;
}

shRemoveFromGETVarsList('task');
shRemoveFromGETVarsList('view');
shRemoveFromGETVarsList('format');
shRemoveFromGETVarsList('tmpl');

if (isset($object_group)) {
	$title[] = $object_group;
	shRemoveFromGETVarsList('object_group');
}

if (isset($object_id)) {
	$title[] = $object_id;
	shRemoveFromGETVarsList('object_id');
}

// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef) {
	$string = shFinalizePlugin($string, $title, $shAppendString, $shItemidString,
			(isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
			(isset($shLangName) ? @$shLangName : null));
}
// ------------------  standard plugin finalize function - don't change ---------------------------