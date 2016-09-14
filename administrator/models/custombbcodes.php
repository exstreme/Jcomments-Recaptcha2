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

class JCommentsModelCustomBBCodes extends JCommentsModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'jcb.id',
				'name', 'jcb.name',
				'button_enabled', 'jcb.button_enabled',
				'published', 'jcb.published',
				'ordering', 'jcb.ordering',
			);
		}

		parent::__construct($config);
	}

	public function getTable($type = 'CustomBBCode', $prefix = 'JCommentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);
		$query->select("jcb.*");
		$query->from($this->_db->quoteName('#__jcomments_custom_bbcodes') . ' AS jcb');

		// Join over the users
		$query->select('u.name AS editor');
		$query->join('LEFT', $this->_db->quoteName('#__users') . ' AS u ON u.id = jcb.checked_out');

		// Filter by published state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('jcb.published = ' . (int)$state);
		}

		// Filter by search in name or email
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
			$query->where('(jcb.name LIKE ' . $search . ' OR jcb.button_title LIKE ' . $search . ')');
		}

		$ordering = $this->state->get('list.ordering', 'ordering');
		$direction = $this->state->get('list.direction', 'asc');
		$query->order($this->_db->escape($ordering . ' ' . $direction));

		return $query;
	}

	public function changeButtonState(&$pks, $state = 1)
	{
		$pks = (array)$pks;
		$table = $this->getTable();
		$key = $table->getKeyName();

		$query = $this->_db->getQuery(true);

		$query->update($table->getTableName());
		$query->set('button_enabled = ' . (int)$state);
		$query->where($key . ' = ' . implode(' OR ' . $key . ' = ', $pks));

		$this->_db->setQuery($query);
		$this->_db->execute();

		return true;
	}

	public function duplicate(&$pks)
	{
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$table = $this->getTable();

		foreach ($pks as $pk) {
			if ($table->load($pk, true)) {
				$table->id = 0;

				$m = null;
				if (preg_match('#\((\d+)\)$#', $table->name, $m)) {
					$table->name = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $table->name);
				} else {
					$table->name .= ' (2)';
				}

				$table->published = 0;

				if (!$table->check() || !$table->store()) {
					throw new Exception($table->getError());
				}
			} else {
				throw new Exception($table->getError());
			}
		}

		$this->cleanCache();

		return true;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		parent::populateState('ordering', 'asc');
	}
}