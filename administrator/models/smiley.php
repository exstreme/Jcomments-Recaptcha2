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

JTable::addIncludePath(JPATH_COMPONENT . '/tables');

class JCommentsModelSmiley extends JCommentsModelForm
{
	public function getTable($type = 'Smiley', $prefix = 'JCommentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_jcomments.smiley', 'smiley', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		if (!$this->canEditState((object)$data)) {
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'filter', 'unset');

			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_jcomments.edit.smiley.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data)
	{
		$table = $this->getTable();
		$pkName = $table->getKeyName();
		$pk = (!empty($data[$pkName])) ? $data[$pkName] : (int)$this->getState($this->getName() . '.id');

		try {
			if ($pk > 0) {
				$table->load($pk);
			}

			if (!$table->bind($data)) {
				$this->setError($table->getError());

				return false;
			}

			if (!$table->check()) {
				$this->setError($table->getError());

				return false;
			}

			if (!$table->store()) {
				$this->setError($table->getError());

				return false;
			}

			$this->saveLegacy();

			$this->cleanCache('com_jcomments');

		} catch (Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		if (isset($table->$pkName)) {
			$this->setState($this->getName() . '.id', $table->$pkName);
		}

		return true;
	}

	public function saveLegacy()
	{
		$query = $this->_db->getQuery(true);
		$query->select("code, image");
		$query->from($this->_db->quoteName('#__jcomments_smilies'));
		$query->where('published = 1');
		$query->order('ordering');

		$this->_db->setQuery($query);

		$items = $this->_db->loadObjectList();

		if (count($items)) {
			$values = array();
			foreach ($items as $item) {
				if ($item->code != '' && $item->image != '') {
					$values[] = $item->code . "\t" . $item->image;
				}
			}

			$values = count($values) ? implode("\n", $values) : '';

			$query = $this->_db->getQuery(true);
			$query->select("COUNT(*)");
			$query->from($this->_db->quoteName('#__jcomments_settings'));
			$query->where('component = ' . $this->_db->quote(''));
			$query->where('name = ' . $this->_db->quote('smilies'));
			$this->_db->setQuery($query);

			$count = $this->_db->loadResult();

			if ($count) {
				$query = $this->_db->getQuery(true);
				$query->update($this->_db->quoteName('#__jcomments_settings'));
				$query->set($this->_db->quoteName('value') . ' = ' . $this->_db->quote($values));
				$query->where('name = ' . $this->_db->quote('smilies'));
				$this->_db->setQuery($query);
				$this->_db->execute();
			} else {
				$query = $this->_db->getQuery(true);
				$query->insert($this->_db->quoteName('#__jcomments_settings'));
				$query->columns(array($this->_db->quoteName('name'), $this->_db->quoteName('value')));
				$query->values($this->_db->quote('smilies') . ', ' . $this->_db->quote($values));
				$this->_db->setQuery($query);
				$this->_db->execute();
			}
		}
	}
}