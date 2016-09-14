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

class JCommentsModelList extends JCommentsModelLegacy
{
	protected $cache = array();
	protected $context = null;
	protected $query = array();
	protected $filter_fields = array();

	public function __construct($config = array())
	{
		parent::__construct($config);

		JTable::addIncludePath(JPATH_COMPONENT . '/tables');

		if (isset($config['filter_fields'])) {
			$this->filter_fields = $config['filter_fields'];
		}

		if (empty($this->context)) {
			$this->context = strtolower($this->option . '.' . $this->getName());
		}
	}

	public function getItems()
	{
		$store = $this->getStoreId();

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$query = $this->_getListQuery();

		try {
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		} catch (RuntimeException $e) {
			$this->setError($e->getMessage());

			return false;
		}

		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	public function getPagination()
	{
		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$limit = (int)$this->getState('list.limit');
		jimport('joomla.html.pagination');
		$page = new JPagination($this->getTotal(), $this->getStart(), $limit);

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}

	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);

		return $query;
	}

	protected function getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return md5($this->context . ':' . $id);
	}

	public function getTotal()
	{
		$store = $this->getStoreId('getTotal');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$query = $this->_getListQuery();

		try {
			$total = (int)$this->_getListCount($query);
		} catch (RuntimeException $e) {
			$this->setError($e->getMessage());

			return false;
		}

		$this->cache[$store] = $total;

		return $this->cache[$store];
	}

	public function getStart()
	{
		$store = $this->getStoreId('getStart');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal();
		if ($start > $total - $limit) {
			$start = max(0, (int)(ceil($total / $limit) - 1) * $limit);
		}

		$this->cache[$store] = $start;

		return $this->cache[$store];
	}


	protected function _getListQuery()
	{
		static $lastStoreId;

		$currentStoreId = $this->getStoreId();

		if ($lastStoreId != $currentStoreId || empty($this->query)) {
			$lastStoreId = $currentStoreId;
			$this->query = $this->getListQuery();
		}

		return $this->query;
	}

	protected function _getListCount($query)
	{
		$this->_db->setQuery($query);
		$this->_db->execute();

		return $this->_db->getNumRows();
	}

	protected function canDelete($record)
	{
		return JFactory::getUser()->authorise('core.delete', $this->option);
	}

	protected function canEditState($record)
	{
		return JFactory::getUser()->authorise('core.edit.state', $this->option);
	}

	public function delete(&$pks)
	{
		$pks = (array)$pks;
		$table = $this->getTable();

		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if ($this->canDelete($table)) {
					if (!$table->delete($pk)) {
						$this->setError($table->getError());

						return false;
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

		return true;
	}

	public function publish(&$pks, $value = 1)
	{
		$pks = (array)$pks;
		$user = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $i => $pk) {
			$table->reset();

			if ($table->load($pk)) {
				if (!$this->canEditState($table)) {
					unset($pks[$i]);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

					return false;
				}
				if (!$table->publish(array($pk), $value, $user->get('id'))) {
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	public function checkin($pks = array())
	{
		$pks = (array)$pks;
		$table = $this->getTable();
		$checkin = property_exists($table, 'checked_out');
		$count = 0;

		if ($checkin && !empty($pks)) {
			foreach ($pks as $pk) {
				if ($table->load($pk)) {
					if ($table->checked_out > 0) {
						if (!$table->checkin($pk)) {
							$this->setError($table->getError());
							return false;
						}
						$count++;
					}
				} else {
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return $count;
	}

	protected function getReorderConditions($table)
	{
		return array();
	}

	public function saveOrder($pks = null, $order = null)
	{
		if (!empty($pks)) {
			$table = $this->getTable();
			$conditions = array();
			$ordering = property_exists($table, 'ordering');

			if ($ordering) {
				foreach ($pks as $i => $pk) {
					$table->load((int)$pk);

					if ($table->ordering != $order[$i]) {
						$table->ordering = $order[$i];

						if (!$table->store()) {
							$this->setError($table->getError());

							return false;
						}

						$reorderCondition = $this->getReorderConditions($table);
						$found = false;

						foreach ($conditions as $condition) {
							if ($condition[1] == $reorderCondition) {
								$found = true;
								break;
							}
						}

						if (!$found) {
							$key = $table->getKeyName();
							$conditions[] = array($table->$key, $reorderCondition);
						}
					}
				}

				foreach ($conditions as $condition) {
					$table->load($condition[0]);
					$table->reorder($condition[1]);
				}
			}
		}

		return true;
	}

	public function reorder($pks, $delta = 0)
	{
		$table = $this->getTable();
		$pks = (array)$pks;
		$result = true;

		$allowed = true;

		foreach ($pks as $i => $pk) {
			$table->reset();

			if ($table->load($pk) && $this->checkout($pk)) {
				// Access checks.
				if (!$this->canEditState($table)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					$this->checkin($pk);
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
					$allowed = false;
					continue;
				}

				$where = $this->getReorderConditions($table);

				if (!$table->move($delta, $where)) {
					$this->setError($table->getError());
					unset($pks[$i]);
					$result = false;
				}

				$this->checkin($pk);
			} else {
				$this->setError($table->getError());
				unset($pks[$i]);
				$result = false;
			}
		}

		if ($allowed === false && empty($pks)) {
			$result = null;
		}

		if ($result == true) {
			$this->cleanCache();
		}

		return $result;
	}


	protected function populateState($ordering = null, $direction = null)
	{
		if ($this->context) {
			$app = JFactory::getApplication('administrator');

			$value = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $limit);

			$value = $app->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0);
			$start = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $start);

			$value = $app->getUserStateFromRequest($this->context . '.filter.order', 'filter_order', $ordering);
			if (!in_array($value, $this->filter_fields)) {
				$value = $ordering;
				$app->setUserState($this->context . '.filter.order', $value);
			}
			$this->setState('list.ordering', $value);

			$value = $app->getUserStateFromRequest($this->context . '.filter.order', 'filter_order_Dir', $direction);
			if (!in_array(strtoupper($value), array('ASC', 'DESC', ''))) {
				$value = $direction;
				$app->setUserState($this->context . '.filter.order_Dir', $value);
			}
			$this->setState('list.direction', $value);
		} else {
			$this->setState('list.start', 0);
		}
	}
}