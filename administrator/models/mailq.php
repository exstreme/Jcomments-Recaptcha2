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

class JCommentsModelMailq extends JCommentsModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id',
				'name',
				'email',
				'subject',
				'priority',
				'attempts',
				'created',
			);
		}

		parent::__construct($config);
	}

	public function getTable($type = 'Mailq', $prefix = 'JCommentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);
		$query->select("*");
		$query->from($this->_db->quoteName('#__jcomments_mailq'));

		// Filter by search in name or email
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
			$query->where('(' . $this->_db->quoteName('name') . ' LIKE ' . $search .
				' OR ' . $this->_db->quoteName('email') . ' LIKE ' . $search . ')'
			);
		}
		$ordering = $this->state->get('list.ordering', $this->_db->quoteName('created'));
		$direction = $this->state->get('list.direction', 'ASC');
		$query->order($this->_db->escape($ordering . ' ' . $direction));

		return $query;
	}

	public function purge()
	{
		$query = $this->_db->getQuery(true);
		$query->delete();
		$query->from($this->_db->quoteName('#__jcomments_mailq'));
		$this->_db->setQuery($query);
		$this->_db->execute();

		return true;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		parent::populateState('created', 'desc');
	}
}