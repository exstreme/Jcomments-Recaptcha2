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

jimport('joomla.plugin.plugin');

/**
 * User plugin for updating user info in comments
 */
class plgUserJComments extends JPlugin
{
	function onUserAfterSave($user, $isNew, $success, $msg)
	{
		if ($success && !$isNew) {
			$id = (int)$user['id'];

			if ($id > 0 && trim($user['username']) != '' && trim($user['email']) != '') {
				$db = JFactory::getDBO();

				// update name, username and email in comments
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__jcomments'));
				$query->set($db->quoteName('name') . ' = ' . $db->Quote($user['name']));
				$query->set($db->quoteName('username') . ' = ' . $db->Quote($user['username']));
				$query->set($db->quoteName('email') . ' = ' . $db->Quote($user['email']));
				$query->where($db->quoteName('userid') . ' = ' . $id);
				$db->setQuery($query);
				$db->execute();

				// update email in subscriptions
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__jcomments_subscriptions'));
				$query->set($db->quoteName('email') . ' = ' . $db->Quote($user['email']));
				$query->where($db->quoteName('userid') . ' = ' . $id);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	function onUserAfterDelete($user, $success, $msg)
	{
		if ($success) {
			$id = (int)$user['id'];

			if ($id > 0) {
				$db = JFactory::getDBO();

				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__jcomments'));
				$query->set($db->quoteName('userid') . ' = 0');
				$query->where($db->quoteName('userid') . ' = ' . $id);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jcomments_reports'));
				$query->where($db->quoteName('userid') . ' = ' . $id);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jcomments_subscriptions'));
				$query->where($db->quoteName('userid') . ' = ' . $id);
				$db->setQuery($query);
				$db->execute();

				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jcomments_votes'));
				$query->where($db->quoteName('userid') . ' = ' . $id);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
}