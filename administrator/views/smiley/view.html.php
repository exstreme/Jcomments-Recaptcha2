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

class JCommentsViewSmiley extends JCommentsViewLegacy
{
	protected $item;
	protected $form;
	protected $state;

	function display($tpl = null)
	{
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->state = $this->get('State');

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

		JFactory::getApplication()->input->set('hidemainmenu', 1);

		$userId = JFactory::getUser()->get('id');
		$canDo = JCommentsHelper::getActions();

		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JToolbarHelper::title($isNew ? JText::_('A_SMILIES_SMILEY_NEW') : JText::_('A_SMILIES_SMILEY_EDIT'), 'smilies.png');
		} else {
			JToolbarHelper::title($isNew ? JText::_('A_SMILIES_SMILEY_NEW') : JText::_('A_SMILIES_SMILEY_EDIT'), 'jcomments-smilies');
		}

		if (!$checkedOut && $canDo->get('core.edit')) {
			JToolBarHelper::apply('smiley.apply');
			JToolBarHelper::save('smiley.save');
		}

		if (!$isNew && $canDo->get('core.create')) {
			JToolbarHelper::save2new('smiley.save2new');
		}

		if ($isNew) {
			JToolBarHelper::cancel('smiley.cancel');
		} else {
			JToolBarHelper::cancel('smiley.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}