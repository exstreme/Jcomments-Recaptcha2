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

abstract class JCommentsModelForm extends JCommentsModelLegacy
{
	protected $_forms = array();

	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0) {
			$return = $table->load($pk);

			if ($return === false && $table->getError()) {
				$this->setError($table->getError());

				return false;
			}
		}

		$properties = $table->getProperties(1);
		$item = JArrayHelper::toObject($properties, 'JObject');

		return $item;
	}

	abstract public function getForm($data = array(), $loadData = true);

	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		$hash = md5($source . serialize($options));

		if (isset($this->_forms[$hash]) && !$clear) {
			return $this->_forms[$hash];
		}

		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try {
			$form = JForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data']) {
				$data = $this->loadFormData();
			} else {
				$data = array();
			}

			$form->bind($data);

		} catch (Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		$this->_forms[$hash] = $form;

		return $form;
	}

	protected function loadFormData()
	{
		return array();
	}

	protected function canDelete($record)
	{
		return JFactory::getUser()->authorise('core.delete', $this->option);
	}

	protected function canEditState($record)
	{
		return JFactory::getUser()->authorise('core.edit.state', $this->option);
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
		} catch (Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		if (isset($table->$pkName)) {
			$this->setState($this->getName() . '.id', $table->$pkName);
		}

		return true;
	}

	public function validate($form, $data, $group = null)
	{
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		if ($return instanceof Exception) {
			$this->setError($return->getMessage());

			return false;
		}

		if ($return === false) {
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}

	public function checkin($pk = null)
	{
		if ($pk) {
			$table = $this->getTable();
			$checkin = property_exists($table, 'checked_out');
			if ($checkin) {
				if (!$table->load($pk)) {
					$this->setError($table->getError());

					return false;
				}

				$user = JFactory::getUser();

				if ($table->checked_out > 0 && $table->checked_out != $user->get('id') && !$user->authorise('core.admin', 'com_checkin')) {
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));

					return false;
				}

				if (!$table->checkin($pk)) {
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	public function checkout($pk = null)
	{
		if ($pk) {
			$table = $this->getTable();
			$checkin = property_exists($table, 'checked_out');
			if ($checkin) {
				if (!$table->load($pk)) {
					$this->setError($table->getError());

					return false;
				}

				$user = JFactory::getUser();

				if ($table->checked_out > 0 && $table->checked_out != $user->get('id')) {
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));

					return false;
				}

				if (!$table->checkout($user->get('id'), $pk)) {
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	protected function populateState()
	{
		$table = $this->getTable();
		$key = $table->getKeyName();

		$pk = JFactory::getApplication()->input->getInt($key);
		$this->setState($this->getName() . '.id', $pk);
	}
}