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

class JCommentsControllerSettings extends JCommentsControllerLegacy
{
	function display($cachable = false, $urlparams = array())
	{
		JFactory::getApplication()->input->set('view', 'default');

		parent::display($cachable, $urlparams);
	}

	public function getModel($name = 'Settings', $prefix = 'JCommentsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$base64 = $app->input->get('base64', '');

		if (!empty($base64)) {
			$base64 = base64_decode(urldecode($base64));
			parse_str($base64, $data);

			foreach ($data as $k => $v) {
				$app->input->post->set($k, $v);
			}
		}

		$model = $this->getModel();
		$data = $app->input->post->get('jform', array(), 'array');

		$language = $app->input->post->get('language', '', 'string');
		$model->setState($model->getName() . '.language', $language);

		if ($model->save($data) === false) {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_jcomments&view=settings', false));

			return false;
		} else {
			$this->getModel('smiley')->saveLegacy();
		}

		$captchaEngine = JCommentsFactory::getConfig()->get('captcha_engine', 'kcaptcha');
		if ($captchaEngine == 'kcaptcha') {
			if (!extension_loaded('gd') || !function_exists('imagecreatefrompng')) {
				JFactory::getApplication()->enqueueMessage(JText::_('A_WARNINGS_PHP_GD'), 'warning');
			}
		}

		$cache = JFactory::getCache('com_jcomments');
		$cache->clean();

		$this->setMessage(JText::_('A_SETTINGS_SAVED'));
		$this->setRedirect(JRoute::_('index.php?option=com_jcomments&view=settings', false));

		return true;
	}

	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_jcomments', false));
	}

	public function reset()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$language = $app->input->post->get('language', '', 'string');

		$model = $this->getModel();
		$model->setState($model->getName() . '.language', $language);

		if ($model->reset() === false) {
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_jcomments&view=settings', false));

			return false;
		}

		$cache = JFactory::getCache('com_jcomments');
		$cache->clean();

		$this->setMessage(JText::_('A_SETTINGS_RESTORED'));
		$this->setRedirect(JRoute::_('index.php?option=com_jcomments&view=settings', false));

		return true;
	}
}