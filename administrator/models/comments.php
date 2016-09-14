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

class JCommentsModelComments extends JCommentsModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'jc.id',
				'title', 'jc.title',
				'name', 'jc.name',
				'username', 'jc.username',
				'published', 'jc.published',
				'object_title', 'jo.title',
				'object_group', 'jc.object_group',
				'date', 'jc.date',
				'lang', 'jc.lang',
				'checked_out', 'jc.checked_out',
				'checked_out_time', 'jc.checked_out_time',
			);
		}

		parent::__construct($config);
	}

	public function getTable($type = 'Comment', $prefix = 'JCommentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function getListQuery()
	{
		// TODO: filter deleted comments
		$query = $this->_db->getQuery(true);

		$reportsSubQuery = ', (SELECT COUNT(*) FROM ' . $this->_db->quoteName('#__jcomments_reports') . ' AS r  WHERE r.commentid = jc.id) AS reports';

		$query->select('jc.*' . $reportsSubQuery);
		$query->from($this->_db->quoteName('#__jcomments') . ' AS jc');

		// Join over the objects
		$query->select('jo.title as object_title, jo.link as object_link');
		$query->join('LEFT', $this->_db->quoteName('#__jcomments_objects') . ' AS jo ON jo.object_id = jc.object_id AND jo.object_group = jc.object_group AND jo.lang = jc.lang');

		// Join over the users
		$query->select('u.name AS editor');
		$query->join('LEFT', $this->_db->quoteName('#__users') . ' AS u ON u.id = jc.checked_out');

		// Filter by published state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			if ($state == 2) {
				$query->where('EXISTS (SELECT * FROM ' . $this->_db->quoteName('#__jcomments_reports') . ' AS jr WHERE jr.commentid = jc.id)');
			} else {
				$query->where('jc.published = ' . (int)$state);
			}
		}

		// Filter by component (object group)
		$object_group = $this->getState('filter.object_group');
		if ($object_group != '') {
			$query->where('jc.object_group = ' . $this->_db->Quote($this->_db->escape($object_group)));
		}

		// Filter by language
		$language = $this->getState('filter.language');
		if ($language != '') {
			$query->where('jc.lang = ' . $this->_db->Quote($this->_db->escape($language)));
		}

		// Filter by search in name or email
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('jc.id = ' . (int)substr($search, 3));
			} else if (stripos($search, 'user:') === 0) {
				$escaped = $this->_db->Quote('%' . $this->_db->escape(JString::substr($search, 5), true) . '%');
				$query->where('(jc.email LIKE ' . $escaped . ' OR jc.name LIKE ' . $escaped . ' OR jc.username LIKE ' . $escaped . ')');
			} else if (stripos($search, 'object:') === 0) {
				$escaped = $this->_db->Quote('%' . $this->_db->escape(JString::substr($search, 7), true) . '%');
				$query->where('jo.title LIKE ' . $escaped);
			} else {
				$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
				$query->where('(jc.comment LIKE ' . $search . ' OR jc.title LIKE ' . $search . ')');
			}
		}

		$ordering = $this->state->get('list.ordering', 'jc.date');
		$direction = $this->state->get('list.direction', 'DESC');
		$query->order($this->_db->escape($ordering . ' ' . $direction));

		return $query;
	}

	public function getFilterLanguages()
	{
		$query = $this->_db->getQuery(true);
		$query->select('DISTINCT(lang) AS name');
		$query->from('#__jcomments');
		$query->order('lang ASC');
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		return is_array($rows) ? $rows : array();
	}

	public function getFilterObjectGroups()
	{
		$query = $this->_db->getQuery(true);
		$query->select('DISTINCT(object_group) AS name');
		$query->from('#__jcomments');
		$query->order('object_group ASC');
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		return is_array($rows) ? $rows : array();
	}

	public function delete(&$pks)
	{
		$pks = (array)$pks;
		$table = $this->getTable();

		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if ($this->canDelete($table)) {
					$config = JCommentsFactory::getConfig();

					if ($config->getInt('delete_mode') == 0) {
						if (!$table->delete($pk)) {
							$this->setError($table->getError());

							return false;
						}
					} else {
						$table->markAsDeleted();
					}
				} else {
					unset($pks[$i]);
					$error = $this->getError();
					if ($error) {
						JLog::add($error, JLog::WARNING, 'jerror');

						return false;
					} else {
						JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

						return false;
					}
				}
			}
		}

		$this->cleanCache('com_jcomments');

		return true;
	}

	public function publish(&$pks, $value = 1)
	{
		$pks = (array)$pks;
		$user = JFactory::getUser();
		$language = JFactory::getLanguage();
		$table = $this->getTable();

		$lastLanguage = '';
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if (!$this->canEditState($table)) {
					unset($pks[$i]);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

					return false;
				}

				if ($table->published != $value) {
					$result = JCommentsEventHelper::trigger('onJCommentsCommentBeforePublish', array(&$table));
					if (!in_array(false, $result, true)) {
						if (!$table->publish(array($pk), $value, $user->get('id'))) {
							$this->setError($table->getError());

							return false;
						}

						JCommentsEventHelper::trigger('onJCommentsCommentAfterPublish', array(&$table));

						if ($table->published) {
							if ($lastLanguage != $table->lang) {
								$lastLanguage = $table->lang;
								$language->load('com_jcomments', JPATH_SITE, $table->lang);
							}

							JCommentsNotificationHelper::push(array('comment' => $table), 'comment-new');
						}
					}
				}
			}
		}

		return true;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');
		$config = JCommentsFactory::getConfig();

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$object_group = $app->getUserStateFromRequest($this->context . '.filter.object_group', 'filter_object_group', '');
		$this->setState('filter.object_group', $object_group);

		$language = $app->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$this->setState('config.comment_title', $config->getInt('comment_title'));

		parent::populateState('jc.date', 'desc');
	}
}