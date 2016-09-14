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
 * JComments Notification Helper
 */
class JCommentsNotificationHelper
{
	/**
	 * Pushes the email notification to the mail queue
	 *
	 * @param array $data An associative array of notification data
	 * @param string $type Type of notification
	 */
	public static function push($data, $type = 'comment-new')
	{
		if (isset($data['comment'])) {
			$subscribers = self::getSubscribers($data['comment']->object_id, $data['comment']->object_group,
				$data['comment']->lang, $type);

			if (count($subscribers)) {
				JTable::addIncludePath(JCOMMENTS_TABLES);

				$user = JFactory::getUser();
				$data = self::prepareData($data, $type);

				foreach ($subscribers as $subscriber) {
					if (($data['comment']->email <> $subscriber->email) && ($user->email <> $subscriber->email)) {
						if ($data['comment']->userid == 0 || $data['comment']->userid <> $subscriber->userid) {
							$table = JTable::getInstance('Mailq', 'JCommentsTable');
							$table->name = $subscriber->name;
							$table->email = $subscriber->email;
							$table->subject = self::getMessageSubject($data);
							$table->body = self::getMessageBody($data, $subscriber);
							$table->priority = self::getMessagePriority($type);
							$table->created = JFactory::getDate()->toSql();
							$table->store();
						}
					}
				}

				self::send();
			}
		}
	}

	/**
	 * Sends notifications from the mail queue to recipients
	 *
	 * @param int $limit The number of messages to be sent
	 */
	public static function send($limit = 10)
	{
		$app = JFactory::getApplication('site');

		$senderEmail = $app->getCfg('mailfrom');
		$senderName = $app->getCfg('fromname');

		if (!empty($senderEmail) && !empty($senderName)) {
			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__jcomments_mailq'));
			$query->order($db->quoteName('priority') . ' desc');
			$db->setQuery($query, 0, $limit);

			$items = $db->loadObjectList('id');

			if (!empty($items)) {
				JTable::addIncludePath(JCOMMENTS_TABLES);

				self::lock(array_keys($items));

				foreach ($items as $item) {
					$table = JTable::getInstance('Mailq', 'JCommentsTable');
					if ($table->load($item->id)) {
						if (empty($table->session_id) || $table->session_id == self::getSessionId()) {
							$result = self::sendMail($senderEmail, $senderName, $table->email, $table->subject, $table->body);
							if ($result) {
								$table->delete();
							} else {
								$table->attempts = $table->attempts + 1;
								$table->session_id = null;
								$table->store();
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Purges all notifications from the mail queue
	 */
	public static function purge()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__jcomments_mailq'));
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Set lock to mail queue items by current session
	 *
	 * @param array $keys Array of IDs
	 */
	private static function lock($keys)
	{
		if (is_array($keys)) {
			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__jcomments_mailq'));
			$query->set($db->quoteName('session_id') . ' = ' . $db->Quote(self::getSessionId()));
			$query->where($db->quoteName('session_id') . ' IS NULL');
			$query->where($db->quoteName('id') . ' IN (' . implode(',', $keys) . ')');
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Prepares data for notification
	 *
	 * @param array $data An associative array of notification data
	 * @param string $type Type of notification
	 *
	 * @return mixed
	 */
	private static function prepareData($data, $type)
	{
		require_once(JPATH_ROOT . '/components/com_jcomments/jcomments.php');

		$object = JCommentsObjectHelper::getObjectInfo($data['comment']->object_id, $data['comment']->object_group, $data['comment']->lang);

		$data['notification-type'] = $type;
		$data['object_title'] = $object->title;
		$data['object_link'] = JCommentsFactory::getAbsLink($object->link);

		$data['comment']->author = JComments::getCommentAuthorName($data['comment']);
		$data['comment']->title = JCommentsText::censor($data['comment']->title);
		$data['comment']->comment = JCommentsText::censor($data['comment']->comment);
		$data['comment']->comment = JCommentsFactory::getBBCode()->replace($data['comment']->comment);

		if (JCommentsFactory::getConfig()->getInt('enable_custom_bbcode')) {
			$data['comment']->comment = JCommentsFactory::getCustomBBCode()->replace($data['comment']->comment, true);
		}

		$data['comment']->comment = trim(preg_replace('/(\s){2,}/i', '\\1', $data['comment']->comment));

		return $data;
	}

	/**
	 * Returns priority of the message
	 *
	 * @param string $type Type of notification
	 *
	 * @return int
	 */
	private static function getMessagePriority($type)
	{
		switch ($type) {
			case 'moderate-new':
			case 'moderate-update':
				$priority = 10;
				break;

			case 'report':
				$priority = 5;
				break;

			case 'comment-new':
			case 'comment-reply':
			case 'comment-update':
			default:
				$priority = 0;
				break;
		}

		return $priority;
	}

	/**
	 * Returns message subject
	 *
	 * @param array $data An associative array of notification data
	 *
	 * @return string
	 */
	private static function getMessageSubject($data)
	{
		switch ($data['notification-type']) {
			case 'report':
				$subject = JText::sprintf('REPORT_NOTIFICATION_SUBJECT', $data['comment']->author);
				break;

			case 'comment-new':
			case 'moderate-new':
				$subject = JText::sprintf('NOTIFICATION_SUBJECT_NEW', $data['object_title']);
				break;

			case 'comment-reply':
			case 'comment-update':
			case 'moderate-update':
			default:
				$subject = JText::sprintf('NOTIFICATION_SUBJECT_UPDATED', $data['object_title']);
				break;
		}

		return $subject;
	}

	/**
	 * Returns message body
	 *
	 * @param array $data An associative array of notification data
	 * @param object $subscriber An object with information about subscriber
	 *
	 * @return string
	 */
	private static function getMessageBody($data, $subscriber)
	{
		switch ($data['notification-type']) {
			case 'moderate-new':
			case 'moderate-update':
				$templateName = 'tpl_email_administrator';
				break;

			case 'report':
				$templateName = 'tpl_email_report';
				break;

			case 'comment-new':
			case 'comment-reply':
			case 'comment-update':
			default:
				$templateName = 'tpl_email';
				break;
		}

		$tmpl = JCommentsFactory::getTemplate($data['comment']->object_id, $data['comment']->object_group);

		if ($tmpl->load($templateName)) {
			$config = JCommentsFactory::getConfig();

			foreach ($data as $key => $value) {
				if (is_scalar($value)) {
					$tmpl->addVar($templateName, $key, $value);
				} else {
					$tmpl->addObject($templateName, $key, $value);
				}
			}

			$tmpl->addVar($templateName, 'notification-unsubscribe-link', self::getUnsubscribeLink($subscriber->hash));
			$tmpl->addVar($templateName, 'comment-object_title', $data['object_title']);
			$tmpl->addVar($templateName, 'comment-object_link', $data['object_link']);

			if ($data['notification-type'] == 'report'
				|| $data['notification-type'] == 'moderate-new'
				|| $data['notification-type'] == 'moderate-update'
			) {
				$tmpl->addVar($templateName, 'quick-moderation', $config->getInt('enable_quick_moderation'));
				$tmpl->addVar($templateName, 'enable-blacklist', $config->getInt('enable_blacklist'));
			}

			// backward compatibility only
			$tmpl->addVar($templateName, 'hash', $subscriber->hash);
			$tmpl->addVar($templateName, 'comment-isnew', ($data['notification-type'] == 'new') ? 1 : 0);

			return $tmpl->renderTemplate($templateName);
		}

		return false;
	}

	/**
	 * Returns link for canceling the user's subscription for notifications about new comments
	 *
	 * @param string $hash Unique subscriber's hash value
	 *
	 * @return string
	 */
	public static function getUnsubscribeLink($hash)
	{
		$link = 'index.php?option=com_jcomments&amp;task=unsubscribe&amp;hash=' . $hash . '&amp;format=raw';

		if (JFactory::getApplication()->isAdmin()) {
			$link = trim(str_replace('/administrator', '', JURI::root()), '/') . '/' . $link;
		} else {
			$liveSite = trim(str_replace(JURI::root(true), '', str_replace('/administrator', '', JURI::root())), '/');
			$link = $liveSite . JRoute::_($link);
		}

		return $link;
	}

	/**
	 * Returns list of subscribers for given object and subscription type
	 *
	 * @param int $object_id
	 * @param string $object_group
	 * @param string $lang The language
	 * @param string $type The subscription type
	 *
	 * @return array
	 */
	private static function getSubscribers($object_id, $object_group, $lang, $type)
	{
		$subscribers = array();

		switch ($type) {
			case 'moderate-new':
			case 'moderate-update':
			case 'report':
				$config = JCommentsFactory::getConfig();
				if ($config->get('notification_email') != '') {
					$emails = explode(',', $config->get('notification_email'));

					$db = JFactory::getDbo();
					$db->setQuery('SELECT * FROM #__users WHERE email IN ("' . implode('", "', $emails) . '")');
					$users = $db->loadObjectList('email');

					foreach ($emails as $email) {
						$email = trim($email);

						$subscriber = new stdClass();
						$subscriber->id = isset($users[$email]) ? $users[$email]->id : 0;
						$subscriber->name = isset($users[$email]) ? $users[$email]->name : '';
						$subscriber->email = $email;
						$subscriber->hash = md5($email);

						$subscribers[] = $subscriber;
					}
				}
				break;

			case 'comment-new':
			case 'comment-reply':
			case 'comment-update':
			default:
				$db = JFactory::getDbo();

				$query = "SELECT DISTINCTROW js.`name`, js.`email`, js.`hash`, js.`userid` "
					. " FROM #__jcomments_subscriptions AS js"
					. " JOIN #__jcomments_objects AS jo ON js.object_id = jo.object_id AND js.object_group = jo.object_group"
					. " WHERE js.`object_group` = " . $db->Quote($object_group)
					. " AND js.`object_id` = " . intval($object_id)
					. " AND js.`published` = 1 "
					. (JCommentsMultilingual::isEnabled() ? " AND js.`lang` = " . $db->Quote($lang) : '')
					. (JCommentsMultilingual::isEnabled() ? " AND jo.`lang` = " . $db->Quote($lang) : '');
				$db->setQuery($query);
				$subscribers = $db->loadObjectList();
				break;
		}

		return is_array($subscribers) ? $subscribers : array();
	}

	/**
	 * Function to send an email
	 *
	 * @param string $from From email address
	 * @param string $fromName From name
	 * @param mixed $recipient Recipient email address(es)
	 * @param string $subject Email subject
	 * @param string $body Message body
	 *
	 * @return  boolean  True on success
	 */
	private static function sendMail($from, $fromName, $recipient, $subject, $body)
	{
		$mailer = JFactory::getMailer();
		$mailer->setSender(array($from, $fromName));
		$mailer->addRecipient($recipient);
		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->IsHTML(true);

		return $mailer->Send();
	}

	/**
	 * Returns current session id
	 *
	 * @return string
	 */
	private static function getSessionId()
	{
		static $sessionId = null;

		if ($sessionId === null) {
			$sessionId = JFactory::getSession()->getId();
		}

		return $sessionId;
	}
}