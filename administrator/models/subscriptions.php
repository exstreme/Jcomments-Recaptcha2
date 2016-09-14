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

class JCommentsModelSubscriptions extends JCommentsModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'js.id',
				'title', 'js.title',
				'name', 'js.name',
				'email', 'js.email',
				'published', 'js.published',
				'object_group', 'js.object_group',
				'lang', 'js.lang',
			);
		}

		parent::__construct($config);
	}

	public function getTable($type = 'Subscription', $prefix = 'JCommentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);
		$query->select("js.*");
		$query->from($this->_db->quoteName('#__jcomments_subscriptions') . ' AS js');

		// Join over the objects
		$query->select('jo.title AS object_title, jo.link AS object_link');
		$query->join('LEFT', $this->_db->quoteName('#__jcomments_objects') . ' AS jo ON jo.object_id = js.object_id AND jo.object_group = js.object_group AND jo.lang = js.lang');

		// Join over the users
		$query->select('u.name AS editor');
		$query->join('LEFT', $this->_db->quoteName('#__users') . ' AS u ON u.id = js.checked_out');

		// Filter by published state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('js.published = ' . (int)$state);
		}

		// Filter by component (object group)
		$object_group = $this->getState('filter.object_group');
		if ($object_group != '') {
			$query->where('js.object_group = ' . $this->_db->Quote($this->_db->escape($object_group)));
		}

		// Filter by language
		$language = $this->getState('filter.language');
		if ($language != '') {
			$query->where('js.lang = ' . $this->_db->Quote($this->_db->escape($language)));
		}

		// Filter by search in name or email
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('js.id = ' . (int)substr($search, 3));
			} else {
				$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
				$query->where('(js.name LIKE ' . $search . ' OR js.email LIKE ' . $search . ')');
			}
		}

		$ordering = $this->state->get('list.ordering', 'js.name');
		$direction = $this->state->get('list.direction', 'asc');
		$query->order($this->_db->escape($ordering . ' ' . $direction));

		return $query;
	}

	public function getFilterLanguages()
	{
		$query = $this->_db->getQuery(true);
		$query->select('DISTINCT(lang) AS name');
		$query->from('#__jcomments_subscriptions');
		$query->order('lang ASC');
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		return is_array($rows) ? $rows : array();
	}

	public function getFilterObjectGroups()
	{
		$query = $this->_db->getQuery(true);
		$query->select('DISTINCT(object_group) AS name');
		$query->from('#__jcomments_subscriptions');
		$query->order('object_group ASC');
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		return is_array($rows) ? $rows : array();
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$object_group = $app->getUserStateFromRequest($this->context . '.filter.object_group', 'filter_object_group', '');
		$this->setState('filter.object_group', $object_group);

		$language = $app->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		parent::populateState('js.name', 'asc');
	}
}