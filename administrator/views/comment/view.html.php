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

class JCommentsViewComment extends JCommentsViewLegacy
{
	protected $item;
	protected $reports;
	protected $form;
	protected $state;
	protected $ajax;

	function display($tpl = null)
	{
		$this->item = $this->get('Item');
		$this->reports = $this->get('Reports');
		$this->form = $this->get('Form');
		$this->state = $this->get('State');
		$this->ajax = JCommentsFactory::getLink('ajax-backend');

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidation');

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JHtml::_('formbehavior.chosen', 'select');
			$this->bootstrap = true;
		} else {
			JHtml::_('jcomments.bootstrap');
		}

		JHtml::_('jcomments.stylesheet');

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jcomments.php';

		$userId = JFactory::getUser()->get('id');
		$canDo = JCommentsHelper::getActions();
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		JFactory::getApplication()->input->set('hidemainmenu', 1);

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JToolBarHelper::title(JText::_('A_COMMENTS'));
		} else {
			JToolBarHelper::title(JText::_('A_COMMENT_EDIT'), 'jcomments-comments');
		}

		if (!$checkedOut && $canDo->get('core.edit')) {
			JToolBarHelper::apply('comment.apply');
			JToolBarHelper::save('comment.save');
		}

		JToolBarHelper::cancel('comment.cancel', 'JTOOLBAR_CLOSE');
	}
}