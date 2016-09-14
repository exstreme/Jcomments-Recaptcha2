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

class JCommentsViewCustombbcodes extends JCommentsViewLegacy
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

		$filter_state = $this->state->get('filter.state');

		// Filter by published state
		$filter_state_options = array();
		$filter_state_options[] = JHTML::_('select.option', '1', JText::_('A_FILTER_STATE_PUBLISHED'));
		$filter_state_options[] = JHTML::_('select.option', '0', JText::_('A_FILTER_STATE_UNPUBLISHED'));

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		JHtml::_('jcomments.stylesheet');

		// Use frontend template's stylesheet for icons
		$document = JFactory::getDocument();
		$document->addStylesheet(JURI::root(true) . '/components/com_jcomments/tpl/default/style.css?v=3002', 'text/css', null);

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JHtml::_('bootstrap.tooltip');
			JHtml::_('formbehavior.chosen', 'select');

			JCommentsHelper::addSubmenu('custombbcodes');

			JHtmlSidebar::setAction('index.php?option=com_jcomments&view=custombbcodes');
			JHtmlSidebar::addFilter(
				JText::_('A_FILTER_STATE'),
				'filter_state',
				JHtml::_('select.options', $filter_state_options, 'value', 'text', $filter_state, true)
			);

			$this->bootstrap = true;
			$this->sidebar = JHtmlSidebar::render();
		} else {
			JCommentsHelper::addSubmenu('custombbcodes');

			array_unshift($filter_state_options, JHTML::_('select.option', '', JText::_('A_FILTER_STATE')));
			$filter = JHTML::_('select.genericlist', $filter_state_options, 'filter_state', 'onchange="Joomla.submitform();"', 'value', 'text', $filter_state);

			$this->assignRef('filter', $filter);
		}

		$this->addToolbar();

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		parent::display($tpl);

	}

	protected function addToolbar()
	{
		$canDo = JCommentsHelper::getActions();

		JToolBarHelper::title(JText::_('A_SUBMENU_CUSTOM_BBCODE'), 'jcomments-custombbcodes');

		if (($canDo->get('core.create'))) {
			JToolBarHelper::addNew('custombbcode.add');
		}

		if (($canDo->get('core.edit'))) {
			JToolBarHelper::editList('custombbcode.edit');
		}

		if ($canDo->get('core.create')) {
			JToolbarHelper::custom('custombbcodes.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true);
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::publishList('custombbcodes.publish');
			JToolBarHelper::unpublishList('custombbcodes.unpublish');
			JToolbarHelper::checkin('custombbcodes.checkin');
		}

		if (($canDo->get('core.delete'))) {
			JToolBarHelper::deletelist('', 'custombbcodes.delete');
		}
	}

	protected function getSortFields()
	{
		return array(
			'ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'jcb.published' => JText::_('JSTATUS'),
			'jcb.name' => JText::_('A_CUSTOM_BBCODE_NAME'),
			'jcb.button_enabled' => JText::_('A_CUSTOM_BBCODE_BUTTON'),
			'jcb.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}