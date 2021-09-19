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

class JCommentsModelComment extends JCommentsModelForm
{
	public function getTable($type = 'Comment', $prefix = 'JCommentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getReports($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int)$this->getState($this->getName() . '.id');

		$query = $this->_db->getQuery(true);
		$query->select('*');
		$query->from($this->_db->quoteName('#__jcomments_reports'));
		$query->where('commentid = ' . (int)$pk);
		$query->order($this->_db->escape('date'));

		$this->_db->setQuery($query);
		$items = $this->_db->loadObjectList();

		return is_array($items) ? $items : array();
	}

	public function deleteReport($id)
	{
		$query = $this->_db->getQuery(true);
		$query->delete();
		$query->from($this->_db->quoteName('#__jcomments_reports'));
		$query->where('id = ' . (int) $id);

		$this->_db->setQuery($query);
		$this->_db->execute();

		return true;
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_jcomments.comment', 'comment', array('control' => 'jform',
																		  'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		if (!$this->canEditState((object)$data)) {
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_jcomments.edit.comment.data', array());

		if (empty($data)) {
			$data = $this->getItem();
			$data->comment = strip_tags(str_replace('<br />', "\n", $data->comment));
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

			$prevPublished = $table->published;

			if (!$table->bind($data)) {
				$this->setError($table->getError());

				return false;
			}

			if ($table->userid == 0) {
				$table->name = preg_replace('/[\'"\>\<\(\)\[\]]?+/i', '', $table->name);
				$table->username = $table->name;
			} else {
				$user = JFactory::getUser($table->userid);
				$table->name = $user->name;
				$table->username = $user->username;
				$table->email = $user->email;
			}

			if (!function_exists('get_magic_quotes_gpc') || get_magic_quotes_gpc() == 1) {
				$table->title = stripslashes($table->title);
				$table->comment = stripslashes($table->comment);
			}

			$table->comment = JCommentsText::nl2br($table->comment);
			$table->comment = JCommentsFactory::getBBCode()->filter($table->comment);

			if (!$table->check()) {
				$this->setError($table->getError());

				return false;
			}

			if (!$table->store()) {
				$this->setError($table->getError());

				return false;
			}

			if ($table->published && $prevPublished != $table->published) {
				JCommentsNotificationHelper::push(array('comment' => $table), 'comment-new');
			}

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
}