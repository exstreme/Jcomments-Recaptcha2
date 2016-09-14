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

class JCommentsViewSubscriptions extends JCommentsViewLegacy
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

		$filter_object_group = $this->state->get('filter.object_group');
		$filter_language = $this->state->get('filter.language');
		$filter_state = $this->state->get('filter.state');

		// Filter by published state
		$filter_state_options = array();
		$filter_state_options[] = JHTML::_('select.option', '1', JText::_('A_FILTER_STATE_PUBLISHED'));
		$filter_state_options[] = JHTML::_('select.option', '0', JText::_('A_FILTER_STATE_UNPUBLISHED'));

		// Filter by component (object_group)
		$filter_object_group_options = array();
		$object_groups = $this->get('FilterObjectGroups');
		foreach ($object_groups as $object_group) {
			$filter_object_group_options[] = JHTML::_('select.option', $object_group->name, $object_group->name);
		}

		// Filter by language
		$filter_language_options = array();
		$languages = $this->get('FilterLanguages');
		foreach ($languages as $language) {
			$filter_language_options[] = JHTML::_('select.option', $language->name, $language->name);
		}

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JHtml::_('bootstrap.tooltip');
			JHtml::_('behavior.multiselect');
			JHtml::_('formbehavior.chosen', 'select');

			JCommentsHelper::addSubmenu('subscriptions');

			JHtmlSidebar::setAction('index.php?option=com_jcomments&view=subscriptions');
			JHtmlSidebar::addFilter(
				JText::_('A_FILTER_STATE'),
				'filter_state',
				JHtml::_('select.options', $filter_state_options, 'value', 'text', $filter_state, true)
			);

			if (count($filter_object_group_options)) {
				JHtmlSidebar::addFilter(
					JText::_('A_FILTER_COMPONENT'),
					'filter_object_group',
					JHtml::_('select.options', $filter_object_group_options, 'value', 'text', $filter_object_group, true)
				);
			}

			if (count($filter_language_options)) {
				JHtmlSidebar::addFilter(
					JText::_('A_FILTER_LANGUAGE'),
					'filter_language',
					JHtml::_('select.options', $filter_language_options, 'value', 'text', $filter_language, true)
				);
			}

			$this->bootstrap = true;
			$this->sidebar = JHtmlSidebar::render();
		} else {
			JCommentsHelper::addSubmenu('subscriptions');

			$filter = '';

			array_unshift($filter_state_options, JHTML::_('select.option', '', JText::_('A_FILTER_STATE')));
			$filter .= JHTML::_('select.genericlist', $filter_state_options, 'filter_state', 'onchange="Joomla.submitform();"', 'value', 'text', $filter_state);

			if (count($filter_object_group_options)) {
				array_unshift($filter_object_group_options, JHTML::_('select.option', '', JText::_('A_FILTER_COMPONENT')));
				$filter .= ' ' . JHTML::_('select.genericlist', $filter_object_group_options, 'filter_object_group', 'onchange="Joomla.submitform();"', 'value', 'text', $filter_object_group);
			}

			if (count($filter_language_options)) {
				array_unshift($filter_language_options, JHTML::_('select.option', '', JText::_('A_FILTER_LANGUAGE')));
				$filter .= ' ' . JHTML::_('select.genericlist', $filter_language_options, 'filter_language', 'onchange="Joomla.submitform();"', 'value', 'text', $filter_language);
			}

			$this->assignRef('filter', $filter);
		}

		$this->addToolbar();

		parent::display($tpl);

	}

	protected function addToolbar()
	{
		$canDo = JCommentsHelper::getActions();

		JToolBarHelper::title(JText::_('A_SUBMENU_SUBSCRIPTIONS'), 'jcomments-subscriptions');

		if (($canDo->get('core.create'))) {
			JToolBarHelper::addNew('subscription.add');
		}

		if (($canDo->get('core.edit'))) {
			JToolBarHelper::editList('subscription.edit');
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::publishList('subscriptions.publish');
			JToolBarHelper::unpublishList('subscriptions.unpublish');
			JToolbarHelper::checkin('subscriptions.checkin');
		}

		if (($canDo->get('core.delete'))) {
			JToolBarHelper::deletelist('', 'subscriptions.delete');
		}
	}

	protected function getSortFields()
	{
		return array(
			'js.published' => JText::_('JSTATUS'),
			'js.name' => JText::_('A_SUBSCRIPTION_NAME'),
			'js.email' => JText::_('A_SUBSCRIPTION_EMAIL'),
			'js.object_group' => JText::_('A_COMPONENT'),
			'jo.title' => JText::_('A_COMMENT_OBJECT_TITLE'),
			'jc.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}