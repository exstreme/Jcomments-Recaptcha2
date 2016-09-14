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

class JCommentsSubscriptionManager
{
	/**
	 * An array of errors
	 *
	 * @var    array of error messages
	 */
	var $_errors = null;

	function JCommentsSubscriptionManager()
	{
		$this->_errors = array();
	}

	/**
	 * Returns a reference to a subscription manager object,
	 * only creating it if it doesn't already exist.
	 *
	 * @return JCommentsSubscriptionManager    A JCommentsSubscriptionManager object
	 */
	public static function getInstance()
	{
		static $instance = null;

		if (!is_object($instance)) {
			$instance = new JCommentsSubscriptionManager();
		}

		return $instance;
	}

	/**
	 * Subscribes user for new comments notifications for an object
	 *
	 * @param int $object_id    The object identifier
	 * @param string $object_group    The object group (component name)
	 * @param int $userid    The registered user identifier
	 * @param string $email    The user email (for guests only)
	 * @param string $name The user name (for guests only)
	 * @param string $lang The user language
	 * @return boolean True on success, false otherwise.
	 */
	function subscribe($object_id, $object_group, $userid, $email = '', $name = '', $lang = '')
	{
		$object_id = (int)$object_id;
		$object_group = trim($object_group);
		$userid = (int)$userid;
		$result = false;

		if ($lang == '') {
			$lang = JCommentsMultilingual::getLanguage();
		}

		$db = JFactory::getDbo();

		if ($userid != 0) {
			$user = JFactory::getUser($userid);
			$name = $user->name;
			$email = $user->email;
			unset($user);
		}

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jcomments_subscriptions'));
		$query->where($db->quoteName('object_id') . ' = ' . (int)$object_id);
		$query->where($db->quoteName('object_group') . ' = ' . $db->Quote($object_group));
		$query->where($db->quoteName('email') . ' = ' . $db->Quote($email));

		if (JCommentsMultilingual::isEnabled()) {
			$query->where($db->quoteName('lang') . ' = ' . $db->Quote(JCommentsMultilingual::getLanguage()));
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		JTable::addIncludePath(JCOMMENTS_TABLES);

		if (count($rows) == 0) {
			$subscription = JTable::getInstance('Subscription', 'JCommentsTable');
			$subscription->object_id = $object_id;
			$subscription->object_group = $object_group;
			$subscription->name = $name;
			$subscription->email = $email;
			$subscription->userid = $userid;
			$subscription->lang = $lang;
			$subscription->published = 1;
			$subscription->store();
			$result = true;
		} else {
			// if current user is registered, but already exists subscription
			// on same email by guest - update subscription data
			if ($userid > 0 && $rows[0]->userid == 0) {
				$subscription = JTable::getInstance('Subscription', 'JCommentsTable');
				$subscription->id = $rows[0]->id;
				$subscription->name = $name;
				$subscription->email = $email;
				$subscription->userid = $userid;
				$subscription->lang = $lang;
				$subscription->published = 1;
				$subscription->store();
				$result = true;
			} else {
				$this->_errors[] = JText::_('ERROR_ALREADY_SUBSCRIBED');
			}
		}

		if ($result) {
			$cache = JFactory::getCache('com_jcomments_subscriptions_' . strtolower($object_group));
			$cache->clean();
		}

		return $result;
	}

	/**
	 * Unsubscribe guest from new comments notifications by subscription hash
	 *
	 * @param string $hash    The secret hash value of subscription
	 * @return boolean True on success, false otherwise.
	 */
	function unsubscribeByHash($hash)
	{
		if (!empty($hash)) {
			$subscription = $this->getSubscriptionByHash($hash);
			
			if ($subscription !== null) {
				$db = JFactory::getDbo();

				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jcomments_subscriptions'));
				$query->where($db->quoteName('hash') . ' = ' . $db->Quote($hash));
				$db->setQuery($query);
				$db->execute();

				$cache = JFactory::getCache('com_jcomments_subscriptions_' . strtolower($subscription->object_group));
				$cache->clean();

				return true;
			}
		}

		return false;
	}

	/**
	 * Unsubscribe registered user from new comments notifications for an object
	 *
	 * @param int $object_id    The object identifier
	 * @param string $object_group    The object group (component name)
	 * @param int $userid    The registered user identifier
	 * @return boolean True on success, false otherwise.
	 */
	function unsubscribe($object_id, $object_group, $userid)
	{
		if ($userid != 0) {
			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__jcomments_subscriptions'));
			$query->where($db->quoteName('object_id') . ' = ' . (int)$object_id);
			$query->where($db->quoteName('object_group') . ' = ' . $db->Quote($object_group));
			$query->where($db->quoteName('userid') . ' = ' . (int)$userid);

			if (JCommentsMultilingual::isEnabled()) {
				$query->where($db->quoteName('lang') . ' = ' . $db->Quote(JCommentsMultilingual::getLanguage()));
			}

			$db->setQuery($query);
			$db->execute();

			$cache = JFactory::getCache('com_jcomments_subscriptions_' . strtolower($object_group));
			$cache->clean();

			return true;
		}

		return false;
	}

	function getSubscriptionByHash($hash)
	{
	        $subscription = null;

		if (!empty($hash)) {
			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__jcomments_subscriptions'));
			$query->where($db->quoteName('hash') . ' = ' . $db->Quote($hash));
			$db->setQuery($query);

			$subscription = $db->loadObject();
		}

		return $subscription;
	}

	/**
	 * Checks if given user is subscribed to new comments notifications for an object
	 *
	 * @param int $object_id    The object identifier
	 * @param string $object_group    The object group (component name)
	 * @param int $userid    The registered user identifier
	 * @param string $email    The user email (for guests only)
	 * @param string $language    The object language
	 * @return int
	 */
	function isSubscribed($object_id, $object_group, $userid, $email = '', $language = '')
	{
		static $data = null;

		$key = $object_id . $object_group . $userid . $email . $language;

		if (!isset($data[$key])) {
			$cache = JFactory::getCache('com_jcomments_subscriptions_' . strtolower($object_group), 'callback');
			$data[$key] = $cache->get(array($this, '_isSubscribed'), array($object_id, $object_group, $userid, $email,
																		   $language));
		}

		return $data[$key];
	}

	/**
	 * Return an array of errors messages
	 *
	 * @return Array The array of error messages
	 */
	function getErrors()
	{
		return $this->_errors;
	}

	function _isSubscribed($object_id, $object_group, $userid, $email = '', $language = '')
	{
		if (empty($language)) {
			$language = JCommentsMultilingual::getLanguage();
		}

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__jcomments_subscriptions'));
		$query->where($db->quoteName('object_id') . ' = ' . (int)$object_id);
		$query->where($db->quoteName('object_group') . ' = ' . $db->Quote($object_group));
		$query->where($db->quoteName('userid') . ' = ' . (int)$userid);

		if ($userid == 0) {
			$query->where($db->quoteName('email') . ' = ' . $db->Quote($email));
		}

		if (JCommentsMultilingual::isEnabled()) {
			$query->where($db->quoteName('lang') . ' = ' . $db->Quote($language));
		}

		$db->setQuery($query);

		$count = $db->loadResult();

		return ($count > 0 ? 1 : 0);
	}
}