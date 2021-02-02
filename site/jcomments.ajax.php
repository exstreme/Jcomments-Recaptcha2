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

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

ob_start();

if (!defined('JOOMLATUNE_AJAX')) {
	require_once (JCOMMENTS_LIBRARIES.'/joomlatune/ajax.php');
}

if ((version_compare(phpversion(), '5.1.0') >= 0)) {
	date_default_timezone_set('UTC');
}

JTable::addIncludePath(JCOMMENTS_TABLES);

/**
 * Frontend event handler
 */
class JCommentsAJAX
{
	public static function prepareValues( &$values )
	{
		foreach ($values as $k => $v) {
			if ($k == 'comment') {
				// strip all HTML except [code]
				$m = array();
				preg_match_all('#(\[code\=?([a-z0-9]*?)\].*\[\/code\])#isUu', trim($v), $m);

				$tmp = array();
				$key = '';

				foreach ($m[1] as $code) {
					$key = '{' . md5($code.$key). '}';
					$tmp[$key] = $code;
					$v = preg_replace('#' . preg_quote($code, '#') . '#isUu', $key, $v);
				}

				$v = trim(strip_tags($v));

				// handle magic quotes compatibility
				if (get_magic_quotes_gpc() == 1) {
					$v = stripslashes($v);
				}
				$v = JCommentsText::nl2br($v);

				foreach ($tmp as $key => $code) {
					if (get_magic_quotes_gpc() == 1) {
						$code = str_replace('\"', '"', $code);
						$code = str_replace('\'', "'", $code);
					}
					$v = preg_replace('#' . preg_quote($key, '#') . '#isUu', $code, $v);
				}
				unset($tmp, $m);
				$values[$k] = $v;
			} else {
				$values[$k] = trim(strip_tags($v));

				// handle magic quotes compatibility
				if (get_magic_quotes_gpc() == 1) {
					$values[$k] = stripslashes($values[$k]);
				}

			}
		}

		return $values;
	}

	public static function escapeMessage($message)
	{
		$message = str_replace("\n", '\n', $message);
		$message = str_replace('\n', '<br />', $message);
		$message = JCommentsText::jsEscape($message);
		return $message;
	}

	public static function showErrorMessage($message, $name = '', $target = '')
	{
		$message = self::escapeMessage($message);
		$response = JCommentsFactory::getAjaxResponse();
		$response->addScript("jcomments.error('$message','$target','$name');");
	}

	public static function showInfoMessage($message, $target = '')
	{
		$message = self::escapeMessage($message);
		$response = JCommentsFactory::getAjaxResponse();
		$response->addScript("jcomments.message('$message', '$target');");
	}

	public static function showForm( $object_id, $object_group, $target )
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$response = JCommentsFactory::getAjaxResponse();
		$object_group = JCommentsSecurity::clearObjectGroup($object_group);

		$form = JComments::getCommentsForm($object_id, $object_group);
		$response->addAssign($target, 'innerHTML', $form);
		return $response;
	}

	public static function showReportForm($id, $target)
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$config = JCommentsFactory::getConfig();
		if ($config->getInt('report_reason_required') == 0) {
			JFactory::getApplication()->input->post->set('commentid', (int) $id);
			$response = JCommentsFactory::getAjaxResponse();
			$response->addAssign($target, 'innerHTML', '<div id="comments-report-form"></div>');
			return self::reportComment();
		} else {
			$response = JCommentsFactory::getAjaxResponse();

			$comment = JTable::getInstance('Comment', 'JCommentsTable');
			if ($comment->load($id)) {
				$form = JComments::getCommentsReportForm($id, $comment->object_id, $comment->object_group);
				$response->addAssign($target, 'innerHTML', $form);
			}
			return $response;
		}
	}

	public static function addComment($values = array())
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$user = JFactory::getUser();
		$acl = JCommentsFactory::getACL();
		$config = JCommentsFactory::getConfig();
		$response = JCommentsFactory::getAjaxResponse();

		if ($acl->canComment()) {
			$values = self::prepareValues($_POST);

			$object_group = isset($values['object_group']) ? JCommentsSecurity::clearObjectGroup($values['object_group']) : '';
			$object_id = isset($values['object_id']) ? intval($values['object_id']) : '';

			if ($object_group == '' || $object_id == '') {
				// TODO: add appropriate error message
			 	return $response;
			}

			$commentsPerObject = $config->getInt('max_comments_per_object');
			if ($commentsPerObject > 0) {
				$commentsCount = JComments::getCommentsCount($object_id, $object_group);
				if ($commentsCount >= $commentsPerObject) {
					$message = $config->get('message_locked');
					if (empty($message)) {
						$message = $config->get('ERROR_CANT_COMMENT');
					}
					$message = self::escapeMessage($message);
					$response->addAlert($message);
					return $response;
				}
			}

			$userIP = $acl->getUserIP();

			if (!$user->id) {
				$noErrors = false;

				if (isset($values['userid']) && intval($values['userid']) > 0) {
					// TODO: we need more correct way to detect login timeout
					self::showErrorMessage(JText::_('ERROR_SESSION_EXPIRED'));
				} else if (($config->getInt('author_name', 2) == 2) && empty($values['name'])) {
					self::showErrorMessage(JText::_('ERROR_EMPTY_NAME'), 'name');
				} else if (JCommentsSecurity::checkIsRegisteredUsername($values['name']) == 1) {
					self::showErrorMessage(JText::_('ERROR_NAME_EXISTS'), 'name');
				} else if (JCommentsSecurity::checkIsForbiddenUsername($values['name']) == 1) {
					self::showErrorMessage(JText::_('ERROR_FORBIDDEN_NAME'), 'name');
				} else if (preg_match('/[\"\'\[\]\=\<\>\(\)\;]+/', $values['name'])) {
					self::showErrorMessage(JText::_('ERROR_INVALID_NAME'), 'name');
				} else if (($config->get('username_maxlength') != 0)
					&& (JCommentsText::strlen($values['name']) > $config->get('username_maxlength'))) {
					self::showErrorMessage(JText::_('ERROR_TOO_LONG_USERNAME'), 'name');
				} else if (($config->getInt('author_email') == 2) && empty($values['email'])) {
					self::showErrorMessage(JText::_('ERROR_EMPTY_EMAIL'), 'email');
				} else if (!empty($values['email']) && (!preg_match(_JC_REGEXP_EMAIL2, $values['email']))) {
					self::showErrorMessage(JText::_('ERROR_INCORRECT_EMAIL'), 'email');
				} else if (($config->getInt('author_email') != 0) && JCommentsSecurity::checkIsRegisteredEmail($values['email']) == 1) {
					self::showErrorMessage(JText::_('ERROR_EMAIL_EXISTS'), 'email');
				} else if (($config->getInt('author_homepage') == 2) && empty($values['homepage'])) {
					self::showErrorMessage(JText::_('ERROR_EMPTY_HOMEPAGE'), 'homepage');
				} else {
					$noErrors = true;
				}

				if (!$noErrors) {
					return $response;
				}
			}
			
			$values['name_checkbox_terms_of_use'] = isset($values['name_checkbox_terms_of_use']) ? (int) $values['name_checkbox_terms_of_use'] : 0;
			
			if (($acl->check('floodprotection') == 1) && (JCommentsSecurity::checkFlood($userIP))) {
				self::showErrorMessage(JText::_('ERROR_TOO_QUICK'));
			} else if (($config->getInt('show_checkbox_terms_of_use') == 1)					// Is checkbox enabled from backend (tab Layout)?
				&& ($values['name_checkbox_terms_of_use'] == 0)									// Is checkbox selected?
				&& ($acl->check('enable_permission_show_checkbox_terms_of_use') == 1)) {	// Is checkbox enabled from backend (tab Permissions)?				
				self::showErrorMessage(JText::_('ERROR_CHECKBOX_TERMS_OF_USE_NO_SELECTED'), 'name_checkbox_terms_of_use');
			} else if (empty($values['homepage']) && ($config->get('author_homepage') == 3)) {
				self::showErrorMessage(JText::_('ERROR_EMPTY_HOMEPAGE'), 'homepage');
			} else if (empty($values['title']) && ($config->get('comment_title') == 3)) {
				self::showErrorMessage(JText::_('ERROR_EMPTY_TITLE'), 'title');
			} else if (empty($values['comment'])) {
				self::showErrorMessage(JText::_('ERROR_EMPTY_COMMENT'), 'comment');
			} else if (($config->getInt('comment_maxlength') != 0)
				&& ($acl->check('enable_comment_length_check') == 1)
				&& (JCommentsText::strlen($values['comment']) > $config->get('comment_maxlength'))) {
				self::showErrorMessage(JText::_('ERROR_YOUR_COMMENT_IS_TOO_LONG'), 'comment');
			} else if (($config->getInt('comment_minlength', 0) != 0)
				&& ($acl->check('enable_comment_length_check') == 1)
				&& (JCommentsText::strlen($values['comment']) < $config->get('comment_minlength'))) {
				self::showErrorMessage(JText::_('ERROR_YOUR_COMMENT_IS_TOO_SHORT'), 'comment');
			} else {
				if ($acl->check('enable_captcha') == 1) {
					$captchaEngine = $config->get('captcha_engine', 'kcaptcha');

					switch ($captchaEngine)
					{
						case 'kcaptcha':
							require_once( JCOMMENTS_SITE.'/jcomments.captcha.php' );
							if (!JCommentsCaptcha::check($values['captcha_refid'])) {
								self::showErrorMessage(JText::_('ERROR_CAPTCHA'), 'captcha');
								JCommentsCaptcha::destroy();
								$response->addScript("jcomments.clear('captcha');");
								return $response;
							}
							break;

						case 'recaptcha':
							JPluginHelper::importPlugin('captcha', "recaptcha");
							if (version_compare(JVERSION, '3.0', 'ge')) {
                                				$dispatcher = JEventDispatcher::getInstance();
                            				} else {
                                				$dispatcher = JDispatcher::getInstance();
                           				}
							//Check if the installed version of Joomla is less than 3.9
							if (version_compare(JVERSION, '3.9', '<' ) == 1)
							{
								$res = $dispatcher->trigger('onCheckAnswer');
								if(!$res[0])
								{
									self::showErrorMessage(JText::_('ERROR_CAPTCHA'), 'captcha');
									$response->addScript("grecaptcha.reset()");
									return $response;
								}
							}
							else
							{
								try
								{
									$dispatcher->trigger('onCheckAnswer');
								}
								catch (Exception $e)
								{
									self::showErrorMessage($e->getMessage());
									$response->addScript("grecaptcha.reset()");
									return $response;
								}
							}
							break;

						//Since Joomla v3.9, reCaptcha invisible is implemented
						case 'recaptcha_invisible':
							JPluginHelper::importPlugin('captcha', "recaptcha_invisible");
							if (version_compare(JVERSION, '3.0', 'ge')) {
                                				$dispatcher = JEventDispatcher::getInstance();
                            					} else {
                                				$dispatcher = JDispatcher::getInstance();
                            					}
							try
							{
								$dispatcher->trigger('onCheckAnswer');
							}
							catch (Exception $e)
							{
								self::showErrorMessage($e->getMessage());
								$response->addScript("grecaptcha.reset()");
								return $response;
							}
							break;

						default:
							$result = JCommentsEventHelper::trigger('onJCommentsCaptchaVerify', array($values['captcha_refid'], &$response));
							// if all plugins returns false
							if (!in_array(true, $result, true)) {
								self::showErrorMessage(JText::_('ERROR_CAPTCHA'));
								return $response;
							}
					}
				}

				$db = JFactory::getDbo();

				// small fix (by default $my has empty 'name' and 'email' field)
				if ($user->id) {
					$currentUser = JFactory::getUser($user->id);
					$user->name = $currentUser->name;
					$user->username = $currentUser->username;
					$user->email = $currentUser->email;
					unset($currentUser);
				}

				if (empty($values['name'])) {
					$values['name'] = 'Guest'; // JText::_('Guest');
				}

				$comment = JTable::getInstance('Comment', 'JCommentsTable');

				$comment->id = 0;
				$comment->name = $user->id ? $user->name : preg_replace("/[\'\"\>\<\(\)\[\]]?+/i", '', $values['name']);
				$comment->username = $user->id ? $user->username : $comment->name;
				$comment->email = $user->id ? $user->email : (isset($values['email']) ? $values['email'] : '');

				if (($config->getInt('author_homepage') != 0)
				&& !empty($values['homepage'])) {
					$comment->homepage = JCommentsText::url($values['homepage']);
				}

				$comment->comment = $values['comment'];

				// filter forbidden bbcodes
				$bbcode = JCommentsFactory::getBBCode();
				$comment->comment = $bbcode->filter( $comment->comment );

				if ($comment->comment != '') {
					if ($config->getInt('enable_custom_bbcode')) {
						// filter forbidden custom bbcodes
						$commentLength = strlen($comment->comment);
						$customBBCode = JCommentsFactory::getCustomBBCode();
						$comment->comment = $customBBCode->filter( $comment->comment );

						if (strlen($comment->comment) == 0 && $commentLength > 0) {
							self::showErrorMessage(JText::_('ERROR_YOU_HAVE_NO_RIGHTS_TO_USE_THIS_TAG'), 'comment');
							return $response;
						}
					}
				}

				if ($comment->comment == '') {
					self::showErrorMessage(JText::_('ERROR_EMPTY_COMMENT'), 'comment');
					return $response;
				}

				$commentWithoutQuotes = $bbcode->removeQuotes($comment->comment);
				if ($commentWithoutQuotes == '') {
					self::showErrorMessage(JText::_('ERROR_NOTHING_EXCEPT_QUOTES'), 'comment');
					return $response;
				} else if (($config->getInt('comment_minlength', 0) != 0)
					&& ($acl->check('enable_comment_length_check') == 1)
					&& (JCommentsText::strlen($commentWithoutQuotes) < $config->get('comment_minlength'))) {
					self::showErrorMessage(JText::_('ERROR_YOUR_COMMENT_IS_TOO_SHORT'), 'comment');
					return $response;
				}

				$values['subscribe'] = isset($values['subscribe']) ? (int) $values['subscribe'] : 0;

				if ($values['subscribe'] == 1 && $comment->email == '') {
					self::showErrorMessage(JText::_('ERROR_SUBSCRIPTION_EMAIL'), 'email');
					return $response;
				}

				$comment->object_id = (int) $object_id;
				$comment->object_group = $object_group;
				$comment->title = isset($values['title']) ? $values['title'] : '';
				$comment->parent = isset($values['parent']) ? intval($values['parent']) : 0;
				$comment->lang = JCommentsMultilingual::getLanguage();
				$comment->ip = $userIP;
				$comment->userid = $user->id ? $user->id : 0;
				$comment->published = $acl->check('autopublish');
				$comment->date = JFactory::getDate()->toSql();

				$query = $db->getQuery(true);
				$query
					->select('COUNT(*)')
					->from($db->quoteName('#__jcomments'))
					->where($db->quoteName('comment') . ' = ' . $db->Quote($comment->comment))
					->where($db->quoteName('ip') . ' = ' . $db->Quote($comment->ip))
					->where($db->quoteName('name') . ' = ' . $db->Quote($comment->name))
					->where($db->quoteName('userid') . ' = ' . $comment->userid)
					->where($db->quoteName('object_id') . ' = ' . $comment->object_id)
					->where($db->quoteName('parent') . ' = ' . $comment->parent)
					->where($db->quoteName('object_group') . ' = ' . $db->Quote($comment->object_group));
				if(JCommentsMultilingual::isEnabled()) {
					$query->where($db->quoteName('lang') . ' = ' . $db->Quote(JCommentsMultilingual::getLanguage()));
				}

				$db->setQuery($query);
				$found = $db->loadResult();

				// if duplicates is not found
				if ($found == 0) {
					$result = JCommentsEventHelper::trigger('onJCommentsCommentBeforeAdd', array(&$comment));

					if (in_array(false, $result, true)) {
						return $response;
					}

					// save comments subscription
					if ($values['subscribe']) {
						require_once (JCOMMENTS_SITE.'/jcomments.subscription.php');
						$manager = JCommentsSubscriptionManager::getInstance();
						$manager->subscribe($comment->object_id, $comment->object_group, $comment->userid, $comment->email, $comment->name, $comment->lang);
					}

					$merged = false;
					$merge_time = $config->getInt('merge_time', 0);

					// merge comments from same author
					if ($user->id && $merge_time > 0) {
						// load previous comment for same object and group
						$prevComment = JCommentsModel::getLastComment($comment->object_id, $comment->object_group, $comment->parent);

						if ($prevComment != null) {
							// if previous comment from same author and it currently not edited
							// by any user - we'll update comment, else - insert new record to database
							if (($prevComment->userid == $comment->userid)
							&& ($prevComment->parent == $comment->parent)
							&& (!$acl->isLocked($prevComment))) {

								$newText = $prevComment->comment . '<br /><br />' . $comment->comment;
								$timeDiff = strtotime($comment->date) - strtotime($prevComment->date);

								if ($timeDiff < $merge_time) {

									$maxlength = $config->getInt('comment_maxlength');
									$needcheck = $acl->check('enable_comment_length_check');

									// validate new comment text length and if it longer than specified -
									// disable union current comment with previous
									if (($needcheck == 0) || (($needcheck == 1) && ($maxlength != 0)
										&& (JCommentsText::strlen($newText) <= $maxlength))) {
										$comment->id = $prevComment->id;
										$comment->comment = $newText;
										$merged = true;
									}
								}
							}
							unset($prevComment);
						}
					}

					// save new comment to database
					if (!$comment->store()) {
						$response->addScript("jcomments.clear('comment');");

						if ($acl->check('enable_captcha') == 1) {
							if ($config->get('captcha_engine', 'kcaptcha') == 'kcaptcha') {
								JCommentsCaptcha::destroy();
								$response->addScript("jcomments.clear('captcha');");
							} 
							else if ($config->get('captcha_engine', 'kcaptcha') == 'recaptcha') {
								$response->addScript("grecaptcha.reset()");
							}
						}
						return $response;
					}

					// store/update information about commented object
					JCommentsObjectHelper::storeObjectInfo($comment->object_id, $comment->object_group, $comment->lang);

					JCommentsEventHelper::trigger('onJCommentsCommentAfterAdd', array(&$comment));

					// send notification to administrators
					if ($config->getInt('enable_notification') == 1) {
						if ($config->check('notification_type', 1) == true) {
							JComments::sendNotification($comment, true);
						}
					}

					// if comment published we need update comments list
					if ($comment->published) {
						// send notification to comment subscribers
						JComments::sendToSubscribers($comment, true);

						if ($merged) {
							$commentText = $comment->comment;
							$html = JCommentsText::jsEscape(JComments::getCommentItem($comment));
							$response->addScript("jcomments.updateComment(".$comment->id.", '$html');");
							$comment->comment = $commentText;
						} else {
							$count = JComments::getCommentsCount($comment->object_id, $comment->object_group);

							if ($config->get('template_view') == 'tree') {
								if ($count > 1) {
									$html = JComments::getCommentListItem($comment);
									$html = JCommentsText::jsEscape($html);
									$mode = ($config->getInt('comments_tree_order') == 1
											|| ($config->getInt('comments_tree_order') == 2 && $comment->parent > 0)) ? 'b' : 'a';
									$response->addScript("jcomments.updateTree('$html','$comment->parent','$mode');");
								} else {
									$html = JComments::getCommentsTree($comment->object_id, $comment->object_group);
									$html = JCommentsText::jsEscape($html);
									$response->addScript("jcomments.updateTree('$html',null);");
								}
							} else {
								// if pagination disabled and comments count > 1...
								if ($config->getInt('comments_per_page') == 0 && $count > 1) {
									// update only added comment
									$html = JComments::getCommentListItem($comment);
									$html = JCommentsText::jsEscape($html);

									if ($config->get('comments_list_order') == 'DESC') {
										$response->addScript("jcomments.updateList('$html','p');");
									} else {
										$response->addScript("jcomments.updateList('$html','a');");
									}
								} else {
									// update comments list
									$html = JComments::getCommentsList($comment->object_id, $comment->object_group, JComments::getCommentPage($comment->object_id, $comment->object_group, $comment->id));
									$html = JCommentsText::jsEscape($html);
									$response->addScript("jcomments.updateList('$html','r');");
								}

								// scroll to first comment
								if ($config->get('comments_list_order') == 'DESC') {
									$response->addScript("jcomments.scrollToList();");
								}
							}
						}
						self::showInfoMessage(JText::_('THANK_YOU_FOR_YOUR_SUBMISSION'));
					} else {
						self::showInfoMessage(JText::_('THANK_YOU_YOUR_COMMENT_WILL_BE_PUBLISHED_ONCE_REVIEWED'));
					}

					// clear comments textarea & update comment length counter if needed
					$response->addScript("jcomments.clear('comment');");

					if ($acl->check('enable_captcha') == 1) {
						if ($config->get('captcha_engine', 'kcaptcha') == 'kcaptcha') {
							require_once( JCOMMENTS_SITE.'/jcomments.captcha.php' );
							JCommentsCaptcha::destroy();
							$response->addScript("jcomments.clear('captcha');");
						}
						else if ($config->get('captcha_engine', 'kcaptcha') == 'recaptcha') {
							$response->addScript("grecaptcha.reset();");
						}
					}
				} else {
					self::showErrorMessage(JText::_('ERROR_DUPLICATE_COMMENT'), 'comment');
				}
			}
		} else {
			$message = $config->get('ERROR_CANT_COMMENT');
			if ($acl->getUserBlocked()) {
				$bannedMessage = $config->get('message_banned');
				if (!empty($bannedMessage)) {
					$message = self::escapeMessage($bannedMessage);
				}
			}
			$response->addAlert($message);
		}
		return $response;
	}

	public static function deleteComment($id)
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$acl = JCommentsFactory::getACL();
		$config = JCommentsFactory::getConfig();
		$response = JCommentsFactory::getAjaxResponse();

		$comment = JTable::getInstance('Comment', 'JCommentsTable');

		if ($comment->load((int) $id)) {
			if ($acl->isLocked($comment)) {
				$response->addAlert(JText::_('ERROR_BEING_EDITTED'));
			} else if ($acl->canDelete($comment)) {

				$object_id = $comment->object_id;
				$object_group = $comment->object_group;

				$result = JCommentsEventHelper::trigger('onJCommentsCommentBeforeDelete', array(&$comment));

				if (!in_array(false, $result, true)) {
					if ($config->getInt('delete_mode') == 0) {
						$comment->delete();
						$count = JComments::getCommentsCount($object_id, $object_group, '', true);

						if ($config->get('template_view') == 'tree') {
							if ($count > 0) {
								$response->addScript("jcomments.updateComment('$id','');");
							} else {
								$response->addScript("jcomments.updateTree('',null);");
							}
						} else {
							if ($count > 0) {
								if ($config->getInt('comments_per_page') > 0) {
									require_once (JCOMMENTS_HELPERS.'/pagination.php');
									$pagination = new JCommentsPagination($object_id, $object_group);
									$pagination->setCommentsCount($count);
									$currentPage = $pagination->getCommentPage($object_id, $object_group, $id);
									$currentPage = min($currentPage, $pagination->getTotalPages());

									$html = JComments::getCommentsList($object_id, $object_group, $currentPage);
									$html = JCommentsText::jsEscape($html);
									$response->addScript("jcomments.updateList('$html','r');");
								} else {
									$response->addScript("jcomments.updateComment('$id','');");
								}
							} else {
								$response->addScript("jcomments.updateList('','r');");
							}
						}
					} else {
						$comment->markAsDeleted();
						$html = JCommentsText::jsEscape(JComments::getCommentItem($comment));
						$response->addScript("jcomments.updateComment(" . $comment->id . ", '$html');");
					}

					JCommentsEventHelper::trigger('onJCommentsCommentAfterDelete', array(&$comment));
				}
			} else {
				$response->addAlert(JText::_('ERROR_CANT_DELETE'));
			}
		}
		return $response;
	}

	public static function publishComment($id)
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$acl = JCommentsFactory::getACL();
		$response = JCommentsFactory::getAjaxResponse();

		$comment = JTable::getInstance('Comment', 'JCommentsTable');

		if ($comment->load((int) $id)) {
			if ($acl->isLocked($comment)) {
				$response->addAlert(JText::_('ERROR_BEING_EDITTED'));
			} else if ($acl->canPublish($comment)) {

				$object_id = $comment->object_id;
				$object_group = $comment->object_group;
				$page = JComments::getCommentPage($object_id, $object_group, $comment->id);
				$comment->published = !$comment->published;

				$result = JCommentsEventHelper::trigger('onJCommentsCommentBeforePublish', array(&$comment));

				if (!in_array(false, $result, true)) {
					if ($comment->store()) {
						JCommentsEventHelper::trigger('onJCommentsCommentAfterPublish', array(&$comment));
						if ($comment->published) {
							JComments::sendToSubscribers($comment, true);
						}
						self::updateCommentsList($response, $object_id, $object_group, $page);
					}
				}
			} else {
				$response->addAlert(JText::_('ERROR_CANT_PUBLISH'));
			}
		}
		return $response;
	}

	public static function cancelComment($id)
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$response = JCommentsFactory::getAjaxResponse();
		$comment = JTable::getInstance('Comment', 'JCommentsTable');

		if ($comment->load((int) $id)) {
			$acl = JCommentsFactory::getACL();

			if (!$acl->isLocked($comment)) {
				$comment->checkin();
			}
		}
		return $response;
	}

	public static function editComment($id, $loadForm = 0)
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$user = JFactory::getUser();
		$response = JCommentsFactory::getAjaxResponse();
		$comment = JTable::getInstance('Comment', 'JCommentsTable');
		if ($comment->load((int) $id)) {
			$acl = JCommentsFactory::getACL();

			if ($acl->isLocked($comment)) {
				$response->addAlert(JText::_('ERROR_BEING_EDITTED'));
			} else if ($acl->canEdit($comment)) {
					$comment->checkout($user->id);

					$name = ($comment->userid) ? '' : JCommentsText::jsEscape($comment->name);
					$email = ($comment->userid) ? '' : JCommentsText::jsEscape($comment->email);
					$homepage = JCommentsText::jsEscape($comment->homepage);
					$text = JCommentsText::jsEscape(JCommentsText::br2nl($comment->comment));
					$title = JCommentsText::jsEscape(str_replace("\n", '', JCommentsText::br2nl($comment->title)));

					if (intval($loadForm) == 1) {
						$form = JComments::getCommentsForm($comment->object_id, $comment->object_group, true);
						$response->addAssign('comments-form-link', 'innerHTML', $form);
					}
					$response->addScript("jcomments.showEdit(" . $comment->id . ", '$name', '$email', '$homepage', '$title', '$text');");
				} else {
					$response->addAlert(JText::_('ERROR_CANT_EDIT'));
				}
		}
		return $response;
	}

	public static function saveComment($values = array())
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$config = JCommentsFactory::getConfig();

		$response = JCommentsFactory::getAjaxResponse();
		$values = self::prepareValues($_POST);
		$comment = JTable::getInstance('Comment', 'JCommentsTable');
		$id = (int) $values['id'];

		if ($comment->load($id)) {
			$acl = JCommentsFactory::getACL();

			$values['name_checkbox_terms_of_use'] = isset($values['name_checkbox_terms_of_use']) ? (int) $values['name_checkbox_terms_of_use'] : 0;
						
			if ($acl->canEdit($comment)) {
				if ($values['comment'] == '') {
					self::showErrorMessage(JText::_('ERROR_EMPTY_COMMENT'), 'comment');
				} else if (($config->getInt('show_checkbox_terms_of_use') == 1)					// Is checkbox enabled from backend (tab Layout)?
				    && ($values['name_checkbox_terms_of_use'] == 0)									// Is checkbox selected?
				    && ($acl->check('enable_permission_show_checkbox_terms_of_use') == 1)) {	// Is checkbox enabled from backend (tab Permissions)?
				        self::showErrorMessage(JText::_('ERROR_CHECKBOX_TERMS_OF_USE_NO_SELECTED'), 'name_checkbox_terms_of_use');
				} else if (($config->getInt('comment_maxlength') != 0)
					&& ($acl->check('enable_comment_length_check') == 1)
					&& (JCommentsText::strlen($values['comment']) > $config->getInt('comment_maxlength'))) {
					self::showErrorMessage(JText::_('ERROR_YOUR_COMMENT_IS_TOO_LONG'), 'comment');
				} else if (($config->getInt('comment_minlength') != 0)
					&& ($acl->check('enable_comment_length_check') == 1)
					&& (JCommentsText::strlen($values['comment']) < $config->getInt('comment_minlength'))) {
					self::showErrorMessage(JText::_('ERROR_YOUR_COMMENT_IS_TOO_SHORT'), 'comment');
				} else {
					$bbcode = JCommentsFactory::getBBCode();

					$comment->comment = $values['comment'];
					$comment->comment = $bbcode->filter($comment->comment);
					$comment->published = $acl->check('autopublish');


					if (($config->getInt('comment_title') != 0) && isset($values['title'])) {
						$comment->title = stripslashes((string)$values['title']);
					}

					if (($config->getInt('author_homepage') == 1) && isset($values['homepage'])) {
						$comment->homepage = JCommentsText::url($values['homepage']);
					} else {
						$comment->homepage = '';
					}

					$result = JCommentsEventHelper::trigger('onJCommentsCommentBeforeChange', array(&$comment));

					if (in_array(false, $result, true)) {
						return $response;
					}

					$comment->store();
					$comment->checkin();

					JCommentsEventHelper::trigger('onJCommentsCommentAfterChange', array(&$comment));

					if ($config->getInt('enable_notification') == 1) {
						if ($config->check('notification_type', 1) == true) {
							JComments::sendNotification($comment, false);
						}
					}
					$html = JCommentsText::jsEscape(JComments::getCommentItem($comment));
					$response->addScript("jcomments.updateComment(" . $comment->id . ", '$html');");
				}
			} else {
				$response->addAlert(JText::_('ERROR_CANT_EDIT'));
			}
		}
		return $response;
	}

	public static function quoteComment($id, $loadForm = 0)
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$acl = JCommentsFactory::getACL();
		$config = JCommentsFactory::getConfig();
		$response = JCommentsFactory::getAjaxResponse();
		$comment = JTable::getInstance('Comment', 'JCommentsTable');

		if ($comment->load((int) $id)) {
			$comment_name = JComments::getCommentAuthorName($comment);
			$comment_text = JCommentsText::br2nl($comment->comment);

			if ($config->getInt('enable_nested_quotes') == 0) {
				$bbcode = JCommentsFactory::getBBCode();
				$comment_text = $bbcode->removeQuotes($comment_text);
			}

			if ($config->getInt('enable_custom_bbcode')) {
				$customBBCode = JCommentsFactory::getCustomBBCode();
				$comment_text = $customBBCode->filter($comment_text, true);
			}

			if ($acl->getUserId() == 0) {
				$bbcode = JCommentsFactory::getBBCode();
				$comment_text = $bbcode->removeHidden($comment_text);
			}

			if ($comment_text != '') {
				if ($acl->check('enable_autocensor')) {
					$comment_text = JCommentsText::censor($comment_text);
				}

				if (intval($loadForm) == 1) {
					$form = JComments::getCommentsForm($comment->object_id, $comment->object_group, true);
					$response->addAssign('comments-form-link', 'innerHTML', $form);
				}

				$comment_name = JCommentsText::jsEscape($comment_name);
				$comment_text = JCommentsText::jsEscape($comment_text);
				$text = '[quote name="' . $comment_name . '"]' . $comment_text . '[/quote]\n';
				$response->addScript("jcomments.insertText('" . $text . "');");
			} else {
				$response->addAlert(JText::_('ERROR_NOTHING_TO_QUOTE'));
			}
		}
		return $response;
	}

	public static function updateCommentsList(&$response, $object_id, $object_group, $page)
	{
		$config = JCommentsFactory::getConfig();
		if ($config->get('template_view') == 'tree') {
			$html = JComments::getCommentsTree($object_id, $object_group, $page);
			$html = JCommentsText::jsEscape($html);
			$response->addScript("jcomments.updateTree('$html',null);");
		} else {
			$html = JComments::getCommentsList($object_id, $object_group, $page);
			$html = JCommentsText::jsEscape($html);
			$response->addScript("jcomments.updateList('$html','r');");
		}
	}

	public static function showPage($object_id, $object_group, $page)
	{
		$response = JCommentsFactory::getAjaxResponse();

		$object_id = (int) $object_id;
		$object_group = strip_tags($object_group);
		$object_group = JCommentsSecurity::clearObjectGroup($object_group);
		$page = (int) $page;

		self::updateCommentsList($response, $object_id, $object_group, $page);
		return $response;
	}

	public static function showComment($id)
	{
		$response = JCommentsFactory::getAjaxResponse();
		$acl = JCommentsFactory::getACL();
		$config = JCommentsFactory::getConfig();
		$comment = JTable::getInstance('Comment', 'JCommentsTable');

		if ($comment->load((int) $id) && ($acl->canPublish($comment) || $comment->published)) {
			if ($config->get('template_view') == 'tree') {
				$page = 0;
			} else {
				$page = JComments::getCommentPage($comment->object_id, $comment->object_group, $comment->id);
			}
			self::updateCommentsList($response, $comment->object_id, $comment->object_group, $page);
			$response->addScript("jcomments.scrollToComment('$id');");
		} else {
			$response->addAlert(JText::_('ERROR_NOT_FOUND'));
		}
		return $response;
	}

	public static function jump2email($id, $hash)
	{
		$response = JCommentsFactory::getAjaxResponse();
		$comment = JTable::getInstance('Comment', 'JCommentsTable');
		$hash = preg_replace('#[\(\)\'\"]#is', '', strip_tags($hash));

		if ((strlen($hash) == 32) && ($comment->load( (int) $id))) {
			$matches = array();
			preg_match_all(_JC_REGEXP_EMAIL, $comment->comment, $matches);
			foreach ($matches[0] as $email) {
				if (md5((string) $email) == $hash) {
					$response->addScript("window.location='mailto:$email';");
				}
			}
		}
		return $response;
	}

	public static function subscribeUser($object_id, $object_group)
	{
		$user = JFactory::getUser();
		$response = JCommentsFactory::getAjaxResponse();

		if ($user->id) {
			require_once (JCOMMENTS_SITE.'/jcomments.subscription.php');

			$manager = JCommentsSubscriptionManager::getInstance();
			$result = $manager->subscribe($object_id, $object_group, $user->id);

			if ($result) {
				$response->addScript("jcomments.updateSubscription(true, '" . JText::_('BUTTON_UNSUBSCRIBE') . "');");
			} else {
				$errors = $manager->getErrors();
				if (count($errors)) {
					$response->addAlert(implode('\n', $errors));
				}
			}
		}
		return $response;
	}

	public static function unsubscribeUser($object_id, $object_group)
	{
		$user = JFactory::getUser();
		$response = JCommentsFactory::getAjaxResponse();
		$object_group = JCommentsSecurity::clearObjectGroup($object_group);

		if ($user->id) {
			require_once (JCOMMENTS_SITE.'/jcomments.subscription.php');

			$manager = JCommentsSubscriptionManager::getInstance();
			$result = $manager->unsubscribe($object_id, $object_group, $user->id);

			if ($result) {
				$response->addScript("jcomments.updateSubscription(false, '" . JText::_('BUTTON_SUBSCRIBE') . "');");
			} else {
				$errors = $manager->getErrors();
				$response->addAlert(implode('\n', $errors));
			}
		}
		return $response;
	}

	public static function voteComment($id, $value)
	{
		$acl = JCommentsFactory::getACL();
		$db = JFactory::getDbo();
		$response = JCommentsFactory::getAjaxResponse();

		$id = (int) $id;
		$value = (int) $value;
		$value = ($value > 0) ? 1 : -1;
		$ip = $acl->getUserIP();

		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from($db->quoteName('#__jcomments_votes'))
			->where($db->quoteName('commentid') . ' = ' . $id);

		if ($acl->getUserId()) {
			$query->where($db->quoteName('userid') . ' = ' . $acl->getUserId());
		} else {
			$query->where($db->quoteName('userid') . ' = 0 AND ' .
				      $db->quoteName('ip') . ' = ' . $db->Quote($ip));
		}
		$db->setQuery($query);
		$voted = $db->loadResult();

		if ($voted == 0) {
			$comment = JTable::getInstance('Comment', 'JCommentsTable');

			if ($comment->load($id)) {
				if ($acl->canVote($comment)) {
					$result = JCommentsEventHelper::trigger('onJCommentsCommentBeforeVote', array(&$comment, &$value));

					if (!in_array(false, $result, true)) {

						if ($value > 0) {
							$comment->isgood++;
						} else {
							$comment->ispoor++;
						}
						$comment->store();

						$now = JFactory::getDate()->toSql();
                                                $query = $db->getQuery(true);
                                                $query
                                                        ->insert($db->quoteName('#__jcomments_votes'))
                                                                ->columns(
                                                                        array(
                                                                                $db->quoteName('commentid'),
                                                                                $db->quoteName('userid'),
                                                                                $db->quoteName('ip'),
                                                                                $db->quoteName('date'),
                                                                                $db->quoteName('value')
                                                                        ))
                                                                        ->values(
                                                                                $db->quote($comment->id) . ', ' .
                                                                                $db->quote($acl->getUserId()) . ', ' .
                                                                                $db->quote($ip) . ', ' .
                                                                                $db->quote($now) . ', ' .
                                                                                $value
                                                                        );
						$db->setQuery($query);
						$db->execute();

						JCommentsEventHelper::trigger('onJCommentsCommentAfterVote', array(&$comment, $value));
					}

					$tmpl = JCommentsFactory::getTemplate();
					$tmpl->load('tpl_comment');
					$tmpl->addVar('tpl_comment', 'get_comment_vote', 1);
					$tmpl->addObject('tpl_comment', 'comment', $comment);

					$html = $tmpl->renderTemplate('tpl_comment');
					$html = JCommentsText::jsEscape($html);
					$response->addScript("jcomments.updateVote('" . $comment->id . "','$html');");
				} else {
					$response->addAlert(JText::_('ERROR_CANT_VOTE'));
				}
			} else {
				$response->addAlert(JText::_('ERROR_NOT_FOUND'));
			}
		} else {
			$response->addAlert(JText::_('ERROR_ALREADY_VOTED'));
		}
		return $response;
	}

	public static function reportComment()
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$acl = JCommentsFactory::getACL();
		$db = JFactory::getDbo();
		$config = JCommentsFactory::getConfig();
		$response = JCommentsFactory::getAjaxResponse();
		$values = self::prepareValues($_POST);

		$id = (int) $values['commentid'];
		$reason = trim(strip_tags($values['reason']));
		$name = trim(strip_tags($values['name']));
		$ip = $acl->getUserIP();

		if (empty($reason)) {
			if ($config->getInt('report_reason_required') == 1) {
				self::showErrorMessage(JText::_('ERROR_NO_REASON_FOR_REPORT'), '', 'comments-report-form');
				return $response;
			} else {
				$reason = JText::_('REPORT_REASON_UNKNOWN_REASON');
			}
		}

		$query = $db->getQuery(true);
		$query
			->select('COUNT(*)')
			->from($db->quoteName('#__jcomments_reports'))
			->where($db->quoteName('commentid') . ' = ' . $id);
		if ($acl->getUserId()) {
			$query->where($db->quoteName('userid') . ' = ' . $acl->getUserId());
		} else {
			$query->where($db->quoteName('userid') . ' = 0 AND ' .
					$db->quoteName('ip') . ' = ' . $db->Quote($ip));
		}
		$db->setQuery($query);
		$reported = $db->loadResult();

		if (!$reported) {
			$maxReportsPerComment = $config->getInt('reports_per_comment', 1);
			$maxReportsBeforeUnpublish = $config->getInt('reports_before_unpublish', 0);
			$query
				->clear()
				->select('COUNT(*)')
				->from($db->quoteName('#__jcomments_reports'))
				->where($db->quoteName('commentid') . ' = ' . $id);
			$db->setQuery($query);
			$reported = $db->loadResult();
			if ($reported < $maxReportsPerComment || $maxReportsPerComment == 0) {
				$comment = JTable::getInstance('Comment', 'JCommentsTable');
				if ($comment->load($id)) {
					if ($acl->canReport($comment)) {
						if ($acl->getUserId()) {
							$user = JFactory::getUser();
							$name = $user->name;
						} else {
							if (empty($name)) {
								$name = 'Guest'; // JText::_('Guest');
							}
						}

						$report = JTable::getInstance('Report', 'JCommentsTable');
						$report->commentid = $comment->id;
						$report->date = JFactory::getDate()->toSql();
						$report->userid = $acl->getUserId();
						$report->ip = $ip;
						$report->name = $name;
						$report->reason = $reason;

						$html = '';
						$result = JCommentsEventHelper::trigger('onJCommentsCommentBeforeReport', array(&$comment, &$report));

						if (!in_array(false, $result, true)) {
							if ($report->store()) {
								JCommentsEventHelper::trigger('onJCommentsCommentAfterReport', array(&$comment, $report));

								if ($config->getInt('enable_notification') == 1) {
									if ($config->check('notification_type', 2)) {
										JComments::sendReport($comment, $name, $reason);
									}
								}

								// unpublish comment if reports count is enough
								if ($maxReportsBeforeUnpublish > 0 && $reported >= $maxReportsBeforeUnpublish) {
									$comment->published = 0;
									$comment->store();
								}

								$html = JText::_('REPORT_SUCCESSFULLY_SENT');
								$html = str_replace("\n", '\n', $html);
								$html = str_replace('\n', '<br />', $html);
								$html = JCommentsText::jsEscape($html);
							}
						}
						$response->addScript("jcomments.closeReport('$html');");
					} else {
						self::showErrorMessage(JText::_('ERROR_YOU_HAVE_NO_RIGHTS_TO_REPORT'), '', 'comments-report-form');
					}
				} else {
					$response->addAlert(JText::_('ERROR_NOT_FOUND'));
				}
			} else {
				self::showErrorMessage(JText::_('ERROR_COMMENT_ALREADY_REPORTED'), '', 'comments-report-form');
			}
		} else {
			self::showErrorMessage(JText::_('ERROR_YOU_CAN_NOT_REPORT_THE_SAME_COMMENT_MORE_THAN_ONCE'), '', 'comments-report-form');
		}
		return $response;
	}

	public static function BanIP($id)
	{
		if (JCommentsSecurity::badRequest() == 1) {
			JCommentsSecurity::notAuth();
		}

		$acl = JCommentsFactory::getACL();
		$response = JCommentsFactory::getAjaxResponse();
		if ($acl->canBan()) {
			$config = JCommentsFactory::getConfig();
			if ($config->getInt('enable_blacklist') == 1) {
				$id = (int) $id;

				$comment = JTable::getInstance('Comment', 'JCommentsTable');
				if ($comment->load($id)) {
					// we will not ban own IP ;)
					if ($comment->ip != $acl->getUserIP()) {
						$options = array();
						$options['ip'] = $comment->ip;
						// check if this IP already banned
						if (JCommentsSecurity::checkBlacklist($options)) {

							$result = JCommentsEventHelper::trigger('onJCommentsUserBeforeBan', array(&$comment, &$options));

							if (!in_array(false, $result, true)) {
								$blacklist = JTable::getInstance('Blacklist', 'JCommentsTable');
								$blacklist->ip = $comment->ip;
								$blacklist->created = JFactory::getDate()->toSql();
								$blacklist->created_by = $acl->getUserId();

								if ($blacklist->store()) {
									JCommentsEventHelper::trigger('onJCommentsUserAfterBan', array(&$comment, $options));
									self::showInfoMessage(JText::_('SUCCESSFULLY_BANNED'), 'comment-item-' . $id);
								}
							}
						} else {
							self::showErrorMessage(JText::_('ERROR_IP_ALREADY_BANNED'), '', 'comment-item-' . $id);
						}
					} else {
						self::showErrorMessage(JText::_('ERROR_YOU_CAN_NOT_BAN_YOUR_IP'), '', 'comment-item-' . $id);
					}
				}
			}
		}
		return $response;
	}

	public static function refreshObjectsAjax()
	{
		$app = JFactory::getApplication();

		$hash = $app->input->post->get('hash', '');
		$step = $app->input->post->getInt('step');
		$lang = $app->input->post->get('lang', '');

		$count = 50;

		if ($hash === md5($app->getCfg('secret'))) {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			if ($step == 0) {
				if ($app->getCfg('caching') != 0) {
					// clean cache for all object groups
					$query
						->select('DISTINCT ' . $db->quoteName('object_group'))
						->from($db->quoteName('#__jcomments_objects'));
					$db->setQuery($query);
					$rows = $db->loadColumn();
					foreach ($rows as $row) {
						$cache = JFactory::getCache('com_jcomments_objects_'.strtolower($row));
						$cache->clean();
					}
				}

				$db->setQuery('TRUNCATE TABLE ' . $db->quoteName('#__jcomments_objects'));
				$db->execute();
			}

			$where = array();
			$where[] = 'IFNULL(' . $db->quoteName('c.lang') . ', "") <> ""';

			// count objects without information
			$query
				->clear()
				->select('COUNT(DISTINCT ' . $db->quoteName(array('c.object_id','c.object_group','c.lang')) . ')')
				->from($db->quoteName('#__jcomments','c'));
			if(count($where)) {
				$query->where(implode(' AND ', $where));
			}

			$db->setQuery($query);
			$total = (int) $db->loadResult();

			$count = 0;

			if ($total > 0) {
				$where[] = 'NOT EXISTS (SELECT ' . $db->quoteName('o.id') . ' FROM ' . $db->quoteName('#__jcomments_objects','o') . ' WHERE ' .
						$db->quoteName('o.object_id') . ' = ' . $db->quoteName('c.object_id') . ' AND ' .
						$db->quoteName('o.object_group') . ' = ' . $db->quoteName('c.object_group') . ' AND ' .
						$db->quoteName('o.lang') . ' = ' . $db->quoteName('c.lang') . ')';

				// get list of first objects without information
				$query = "SELECT DISTINCT " . $db->quoteName(array('c.object_id', 'c.object_group', 'c.lang'))	
					. " FROM " . $db->quoteName('#__jcomments','c')
					. (count($where) ? ("\nWHERE " . implode(' AND ', $where)) : "")
					. " ORDER BY " . $db->quoteName(array('c.object_group', 'c.lang'))
					;

				$db->setQuery($query, 0, $count);
				$rows = $db->loadObjectList();

				$i = 0;
				$multilanguage = JCommentsMultilingual::isEnabled();

				$nextLanguage = $lang;
				if (count($rows)) {
					foreach ($rows as $row) {
						if ($nextLanguage != $row->lang && $multilanguage) {
							$nextLanguage = $row->lang;
							break;
						}

						// retrieve and store object information
						JCommentsObjectHelper::storeObjectInfo($row->object_id, $row->object_group, $row->lang, false, true);
						$i++;
					}
				}

				if ($i > 0) {
					$db->setQuery("SELECT COUNT(*) FROM " . $db->quoteName('#__jcomments_objects'));
					$count = (int) $db->loadResult();
				}
		
				$percent = ceil(($count / $total) * 100);
				$percent = min($percent, 100);
			} else {
				$percent = 100;
			}

			$step++;

			$lang_codes = JLanguageHelper::getLanguages('lang_code');
			$language_sef = isset($lang_codes[$nextLanguage]) ? $lang_codes[$nextLanguage]->sef : $nextLanguage;

			$data = array('count' => $count,
					  'total' => $total,
					  'percent' => $percent,
					  'step' => $step,
					  'hash' => $hash,
					  'object_group' => null,
					  'lang' => $nextLanguage,
					  'lang_sef' => $language_sef,
			);

			echo json_encode($data);
		}

		$app->close();
	}
}

$result = ob_get_contents();
ob_end_clean();
