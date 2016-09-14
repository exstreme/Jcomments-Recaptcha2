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

class JCommentsControllerMailq extends JCommentsControllerLegacy
{
	public function delete()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = $this->input->get('cid', array(), 'array');

		if (!empty($cid)) {
			$model = $this->getModel('mailq');
			$model->delete($cid);
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jcomments&view=mailq', false));
	}

	public function purge()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mailq');
		$model->purge();

		$this->setMessage(JText::_('A_MAILQ_EMAILS_PURGED'));
		$this->setRedirect(JRoute::_('index.php?option=com_jcomments&view=mailq', false));
	}
}