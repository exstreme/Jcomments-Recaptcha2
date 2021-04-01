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
use Joomla\CMS\HTML\HTMLHelper;
class JCommentsViewSettings extends JCommentsViewLegacy
{
	protected $item;
	protected $form;
	protected $state;
	protected $languages;
	protected $groups;
	protected $permissionForms;

	function display($tpl = null)
	{
		require_once JPATH_COMPONENT . '/helpers/jcomments.php';

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->groups = $this->get('UserGroups');
		$this->permissionForms = $this->get('PermissionForms');
		$this->state = $this->get('State');

		$languages = $this->get('Languages');
		$language_options = array();

		if (count($languages)) {
			// $language_options[] = JHTML::_('select.option', '', JText::_('JALL_LANGUAGE'));
			foreach ($languages as $language) {
				$language_options[] = JHTML::_('select.option', $language->lang_code, $language->title);
			}
		}

		$language = $this->state->get('settings.language');

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		
		if (version_compare(JVERSION, '4.0', 'lt')) {
			JHtml::_('behavior.formvalidation');
			JHtml::_('behavior.tooltip');
		} else {
			HTMLHelper::_('behavior.formvalidator');
			HTMLHelper::_('bootstrap.tooltip');
		}	

		if (version_compare(JVERSION, '3.0', 'ge')) {
			if (version_compare(JVERSION, '4.0', '<')){
				JHtml::_('formbehavior.chosen', 'select:not(.jcommentscategories)');
			} else	{
				HTMLHelper::_('formbehavior.chosen', 'select:not(.jcommentscategories)');
			}
			JHtml::_('jcomments.stylesheet');

			JCommentsHelper::addSubmenu('settings');
			JHtmlSidebar::setAction('index.php?option=com_jcomments&view=settings');

			if (count($languages)) {
				JHtmlSidebar::addFilter(
					JText::_('JOPTION_SELECT_LANGUAGE'),
					'language',
					JHtml::_('select.options', $language_options, 'value', 'text', $language, true)
				);
			}


			$this->bootstrap = true;
			$this->sidebar = JHtmlSidebar::render();
		} else {
			JHtml::_('jcomments.bootstrap');
			JHtml::_('jcomments.stylesheet');

			JCommentsHelper::addSubmenu('settings');

			$filter = '';

			if (count($languages)) {
				if (count($language_options)) {
					array_unshift($language_options, JHTML::_('select.option', '', JText::_('JOPTION_SELECT_LANGUAGE')));
					$filter = JHTML::_('select.genericlist', $language_options, 'language', 'onchange="Joomla.submitform(\'settings-form\');"', 'value', 'text', $language);
				}
			}

			$this->assignRef('filter', $filter);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jcomments.php';

		$canDo = JCommentsHelper::getActions();

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JToolBarHelper::title(JText::_('A_SETTINGS'));
		} else {
			JToolBarHelper::title(JText::_('A_SETTINGS'), 'jcomments-settings');
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::apply('settings.save');
		}

		JToolBarHelper::cancel('settings.cancel');

		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_jcomments', '600', '800');
		}
	}
}