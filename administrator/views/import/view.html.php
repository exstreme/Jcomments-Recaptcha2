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

class JCommentsViewImport extends JCommentsViewLegacy
{
	protected $items;
	protected $state;

	function display($tpl = null)
	{
		require_once JPATH_COMPONENT . '/helpers/jcomments.php';

		$this->items = $this->get('Items');
		$this->state = $this->get('State');

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		if (version_compare(JVERSION, '4.0', 'lt')) {
			JHTML::_('behavior.modal');
		}
		
		JHtml::_('jcomments.stylesheet');

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JHtml::_('bootstrap.tooltip');
			JHtml::_('formbehavior.chosen', 'select');

			JCommentsHelper::addSubmenu('import');

			$this->bootstrap = true;
			$this->sidebar = JHtmlSidebar::render();
		} else {
			JCommentsHelper::addSubmenu('import');
		}

		$this->addToolbar();

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		parent::display($tpl);

	}

	public function modal($tpl = null)
	{
		$this->state = $this->get('State');

		$this->importUrl = 'index.php?option=com_jcomments&task=import.ajax&tmpl=component';
		$this->objectsUrl = str_replace('/administrator', '', JRoute::_('index.php?option=com_jcomments&task=refreshObjectsAjax&amp;tmpl=component'));
		$this->hash = md5(JFactory::getApplication('administrator')->getCfg('secret'));

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		JHtml::_('jcomments.stylesheet');
		JHtml::_('jcomments.jquery');

		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true) . '/administrator/components/com_jcomments/assets/js/jcomments.progressbar.js');
		$document->addScript(JURI::root(true) . '/administrator/components/com_jcomments/assets/js/jcomments.import.js');
		$document->addScript(JURI::root(true) . '/administrator/components/com_jcomments/assets/js/jcomments.objects.js');

		parent::display($tpl);

	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('A_IMPORT'), 'jcomments-import');
	}
}