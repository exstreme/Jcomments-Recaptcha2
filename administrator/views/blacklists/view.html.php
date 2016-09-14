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

class JCommentsViewBlacklists extends JCommentsViewLegacy
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

			JCommentsHelper::addSubmenu('blacklists');

			JHtmlSidebar::setAction('index.php?option=com_jcomments&view=blacklists');

			$this->bootstrap = true;
			$this->sidebar = JHtmlSidebar::render();
		} else {
			JCommentsHelper::addSubmenu('blacklists');
		}

		$this->addToolbar();

		parent::display($tpl);

	}

	protected function addToolbar()
	{
		$canDo = JCommentsHelper::getActions();

		JToolBarHelper::title(JText::_('A_SUBMENU_BLACKLIST'), 'jcomments-blacklist');

		if (($canDo->get('core.create'))) {
			JToolBarHelper::addNew('blacklist.add');
		}

		if (($canDo->get('core.edit'))) {
			JToolBarHelper::editList('blacklist.edit');
		}

		if (($canDo->get('core.delete'))) {
			JToolBarHelper::deletelist('', 'blacklists.delete');
		}
	}

	protected function getSortFields()
	{
		return array(
			'jb.ip' => JText::_('A_BLACKLIST_IP'),
			'jb.reason' => JText::_('A_BLACKLIST_REASON'),
			'jb.notes' => JText::_('A_BLACKLIST_NOTES'),
			'u.name' => JText::_('JGRID_HEADING_CREATED_BY'),
			'jb.created' => JText::_('JGLOBAL_CREATED_DATE'),
			'jb.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}