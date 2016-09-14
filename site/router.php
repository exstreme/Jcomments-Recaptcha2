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
 * Router (Translates an internal Joomla URL to a humanly readable URL)
 *
 * @param    array $query    A named array
 * @return    array
 */
function JCommentsBuildRoute(& $query)
{
	$segments = array();

	if (isset($query['task'])) {
		switch ($query['task']) {
			case 'rss':
				$segments[] = 'feed';
				break;
			case 'rss_full':
				$segments[] = 'feed';
				$segments[] = 'full';
				break;
			case 'rss_user':
				$segments[] = 'feed';
				$segments[] = 'user';
				if (isset($query['userid'])) {
					$segments[] = $query['userid'];
					unset($query['userid']);
				}
				break;
			case 'captcha':
				$segments[] = 'captcha';
				if (isset($query['ac'])) {
					$segments[] = $query['ac'];
					unset($query['ac']);
				}
				break;
			case 'unsubscribe':
				$segments[] = 'unsubscribe';

				if (isset($query['hash'])) {
					$segments[] = $query['hash'];
					unset($query['hash']);
				}
				break;
			case 'cmd':
				if (isset($query['cmd'])) {
					$segments[] = $query['cmd'];
					unset($query['cmd']);
				}
				if (isset($query['id'])) {
					$segments[] = $query['id'];
					unset($query['id']);
				}
				if (isset($query['hash'])) {
					$segments[] = $query['hash'];
					unset($query['hash']);
				}
				break;
		}
		unset($query['task']);
	}

	if (isset($query['object_group'])) {
		$segments[] = $query['object_group'];
		unset($query['object_group']);
	}

	if (isset($query['object_id'])) {
		$segments[] = $query['object_id'];
		unset($query['object_id']);
	}

	if (isset($query['limit'])) {
		$segments[] = $query['limit'];
		unset($query['limit']);
	}

	if (isset($query['tmpl'])) {
		unset($query['tmpl']);
	}

	if (isset($query['format'])) {
		unset($query['format']);
	}

	return $segments;
}

/**
 * @param    array $segments    A named array
 * @return  array
 */
function JCommentsParseRoute($segments)
{
	$vars = array();
	$cnt = count($segments);

	if (isset($segments[0])) {
		switch ($segments[0]) {
			case 'feed':
				if ($segments[1] == 'full') {
					$vars['task'] = 'rss_full';
					if ($cnt == 3) {
						$vars['object_group'] = $segments[2];
					} else {
						if ($cnt == 4) {
							$vars['object_group'] = $segments[2];
							$vars['limit'] = $segments[3];
						}
					}
					$vars['format'] = 'raw';
				} elseif ($segments[1] == 'user') {
					$vars['task'] = 'rss_user';
					if ($cnt == 3) {
						$vars['userid'] = $segments[2];
					}
					$vars['format'] = 'raw';
				} else {
					$vars['task'] = 'rss';
					$vars['object_group'] = $segments[1];
					$vars['object_id'] = $segments[2];

					if (isset($segments[3])) {
						$vars['limit'] = $segments[3];
					}
					$vars['format'] = 'raw';
				}
				break;
			case 'captcha':
				$vars['task'] = $segments[0];
				$vars['ac'] = $segments[1];
				$vars['format'] = 'raw';
				break;
			case 'unsubscribe':
				$vars['task'] = $segments[0];
				$vars['hash'] = $segments[1];
				$vars['format'] = 'raw';
				break;
			case 'ban':
			case 'delete':
			case 'publish':
			case 'unpublish':
				$vars['task'] = 'cmd';
				$vars['cmd'] = $segments[0];
				$vars['id'] = $segments[1];
				$vars['hash'] = $segments[2];
				$vars['format'] = 'raw';
				break;
			default:
				if (isset($segments[1]) && $segments[1] == 'unsubscribe') {
					$vars['task'] = $segments[1];
					$vars['hash'] = $segments[0];
					$vars['format'] = 'raw';
				}
				break;
		}
	}

	return $vars;
}