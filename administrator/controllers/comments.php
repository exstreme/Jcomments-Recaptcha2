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
use Joomla\Utilities\ArrayHelper;
class JCommentsControllerComments extends JCommentsControllerList
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish', 'publish');
	}

	function display($cachable = false, $urlparams = array())
	{
		$this->input->set('view', 'comments');

		parent::display($cachable, $urlparams);
	}

	public function getModel($name = 'Comments', $prefix = 'JCommentsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function publish()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = $this->input->get('cid', array(), 'array');
		$data = array('publish' => 1, 'unpublish' => 0);
		$task = $this->getTask();
		if (version_compare(JVERSION, '4.0', 'lt')){
			$value = JArrayHelper::getValue($data, $task, 0, 'int');
		}else {
			$value = ArrayHelper::getValue($data, $task, 0, 'int');
		}


		if (!empty($cid)) {
			$model = $this->getModel();
			$model->publish($cid, $value);
		}

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	public function checkin()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = JFactory::getApplication()->input->post->get('cid', array(), 'array');

		$model = $this->getModel();
		$return = $model->checkin($ids);
		if ($return === false) {
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');

			return false;
		} else {
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			return true;
		}
	}
}