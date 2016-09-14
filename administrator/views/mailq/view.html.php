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

class JCommentsViewMailq extends JCommentsViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	function display($tpl = null)
	{
		require_once JPATH_COMPONENT . '/helpers/jcomments.php';

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		JHtml::_('jcomments.stylesheet');

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JHtml::_('bootstrap.tooltip');
			JHtml::_('formbehavior.chosen', 'select');

			JCommentsHelper::addSubmenu('mailq');

			$this->bootstrap = true;
			$this->sidebar = JHtmlSidebar::render();
		} else {
			JCommentsHelper::addSubmenu('mailq');
		}

		$this->addToolbar();

		parent::display($tpl);

	}

	protected function addToolbar()
	{
		$canDo = JCommentsHelper::getActions();

		JToolBarHelper::title(JText::_('A_MAILQ'), 'jcomments-mailq');

		if (($canDo->get('core.delete'))) {
			JToolBarHelper::deletelist('', 'mailq.delete');
			JToolBarHelper::divider();
			JToolBarHelper::custom('mailq.purge', 'purge', 'icon-32-unpublish.png', 'A_MAILQ_PURGE_ITEMS', false);
		}
	}

	protected function getSortFields()
	{
		return array(
			'name' => JText::_('A_MAILQ_HEADING_NAME'),
			'email' => JText::_('A_MAILQ_HEADING_EMAIL'),
			'subject' => JText::_('A_MAILQ_HEADING_SUBJECT'),
			'priority' => JText::_('A_MAILQ_HEADING_PRIORITY'),
			'attempts' => JText::_('A_MAILQ_HEADING_ATTEMPTS'),
			'created' => JText::_('A_MAILQ_HEADING_CREATED'),
			'id' => JText::_('JGRID_HEADING_ID')
		);
	}
}