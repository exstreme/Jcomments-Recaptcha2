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

class JCommentsControllerForm extends JCommentsControllerLegacy
{
	protected $context;
	protected $option;
	protected $view_item;
	protected $view_list;
	protected $text_prefix;

	public function __construct($config = array())
	{
		parent::__construct($config);

		if (empty($this->option)) {
			$this->option = 'com_' . strtolower($this->getName());
		}

		if (empty($this->text_prefix)) {
			$this->text_prefix = strtoupper($this->option);
		}

		if (empty($this->context)) {
			$r = null;
			if (!preg_match('/(.*)Controller(.*)/i', get_class($this), $r)) {
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_CONTROLLER_GET_NAME'), 500);
			}
			$this->context = strtolower($r[2]);
		}

		if (empty($this->view_item)) {
			$this->view_item = $this->context;
		}

		if (empty($this->view_list)) {
			// Simple pluralisation based on public domain snippet by Paul Osman
			// For more complex types, just manually set the variable in your class.
			$plural = array(
				array('/(x|ch|ss|sh)$/i', "$1es"),
				array('/([^aeiouy]|qu)y$/i', "$1ies"),
				array('/([^aeiouy]|qu)ies$/i', "$1y"),
				array('/(bu)s$/i', "$1ses"),
				array('/s$/i', "s"),
				array('/$/', "s"));

			foreach ($plural as $pattern) {
				if (preg_match($pattern[0], $this->view_item)) {
					$this->view_list = preg_replace($pattern[0], $pattern[1], $this->view_item);
					break;
				}
			}
		}

		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
	}

	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		if (empty($name)) {
			$name = $this->context;
		}

		return parent::getModel($name, $prefix, $config);
	}

	protected function allowAdd($data = array())
	{
		return JFactory::getUser()->authorise('core.create', $this->option);
	}

	protected function allowEdit($data = array(), $key = 'id')
	{
		return JFactory::getUser()->authorise('core.edit', $this->option);
	}

	protected function allowSave($data, $key = 'id')
	{
		$recordId = isset($data[$key]) ? $data[$key] : '0';

		if ($recordId) {
			return $this->allowEdit($data, $key);
		} else {
			return $this->allowAdd($data);
		}
	}

	public function add()
	{
		$app = JFactory::getApplication();
		$context = "$this->option.edit.$this->context";

		if (!$this->allowAdd()) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_($this->getRedirectToList(), false));

			return false;
		}

		$app->setUserState($context . '.data', null);

		$this->setRedirect(JRoute::_($this->getRedirectToItem(), false));
	}

	public function edit($key = null, $urlVar = null)
	{
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$cid = $app->input->post->get('cid', array(), 'array');
		$context = "$this->option.edit.$this->context";

		if (empty($key)) {
			$key = $table->getKeyName();
		}

		if (empty($urlVar)) {
			$urlVar = $key;
		}

		$recordId = (int)(count($cid) ? $cid[0] : $app->input->getInt($urlVar));

		if (!$this->allowEdit(array($key => $recordId), $key)) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_($this->getRedirectToList(), false));

			return false;
		}

		if (!$model->checkout($recordId)) {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_($this->getRedirectToItem($recordId), false));

			return false;
		} else {
			$this->holdEditId($context, $recordId);
			$app->setUserState($context . '.data', null);
			$this->setRedirect(JRoute::_($this->getRedirectToItem($recordId), false));

			return true;
		}
	}

	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$language = JFactory::getLanguage();
		$task = $this->getTask();
		$data = $app->input->post->get('jform', array(), 'array');
		$model = $this->getModel();
		$table = $model->getTable();
		$key = $table->getKeyName();
		$context = "$this->option.edit.$this->context";

		if (!$this->allowSave($data, $key)) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_($this->getRedirectToList(), false));

			return false;
		}

		$form = $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());

			return false;
		}

		$recordId = $app->input->getInt($key);

		if (!$this->checkEditId($context, $recordId)) {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_($this->getRedirectToList(), false));

			return false;
		}

		$data[$key] = $recordId;

		if ($task == 'save2copy') {
			if ($model->checkin($data[$key]) === false) {
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect(JRoute::_($this->getRedirectToItem($recordId), false));

				return false;
			}

			$data[$key] = 0;
			$task = 'apply';
		}

		$return = $model->validate($form, $data);

		if ($return === false) {
			$errors = $model->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$app->setUserState($context . '.data', $data);
			$this->setRedirect(JRoute::_($this->getRedirectToItem($recordId), false));

			return false;
		}

		$data = $return;

		if ($model->save($data) === false) {
			$app->setUserState($context . '.data', $data);
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_($this->getRedirectToItem($recordId), false));

			return false;
		}

		if ($model->checkin($data[$key]) === false) {
			$app->setUserState($context . '.data', $data);
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_($this->getRedirectToItem($recordId), false));

			return false;
		}

		$this->setMessage(JText::_(($language->hasKey($this->text_prefix . '_SAVE_SUCCESS') ? $this->text_prefix : 'JLIB_APPLICATION') . '_SAVE_SUCCESS'));

		switch ($task) {
			case 'apply':
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$this->setRedirect(JRoute::_($this->getRedirectToItem($recordId), false));
				break;

			case 'save2new':
				$model->setState($context . '.id', null);
				$this->setRedirect(JRoute::_($this->getRedirectToItem(), false));
				break;

			default:
				$model->setState($context . '.id', null);
				$this->setRedirect(JRoute::_($this->getRedirectToList(), false));
				break;
		}

		return true;
	}

	public function cancel()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$key = $table->getKeyName();
		$context = "$this->option.edit.$this->context";

		$recordId = $app->input->getInt($key);

		if ($recordId) {
			if (property_exists($table, 'checked_out')) {
				if ($model->checkin($recordId) === false) {
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');
					$this->setRedirect(JRoute::_($this->getRedirectToItem($recordId), false));

					return false;
				}
			}
		}

		$this->releaseEditId($context, $recordId);
		$app->setUserState($context . '.data', null);
		$this->setRedirect(JRoute::_($this->getRedirectToList(), false));

		return true;
	}

	protected function getRedirectToItem($recordId = null, $urlVar = 'id')
	{
		$app = JFactory::getApplication();
		$tmpl = $app->input->get('tmpl');
		$layout = $app->input->get('layout', 'edit');
		$url = 'index.php?option=' . $this->option . '&view=' . $this->view_item;

		if ($tmpl) {
			$url .= '&tmpl=' . $tmpl;
		}

		if ($layout) {
			$url .= '&layout=' . $layout;
		}

		if ($recordId) {
			$url .= '&' . $urlVar . '=' . $recordId;
		}

		return $url;
	}

	protected function getRedirectToList()
	{
		$tmpl = JFactory::getApplication()->input->get('tmpl');
		$url = 'index.php?option=' . $this->option . '&view=' . $this->view_list;

		if ($tmpl) {
			$url .= '&tmpl=' . $tmpl;
		}

		return $url;
	}
}