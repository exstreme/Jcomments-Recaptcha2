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

class JCommentsViewAbout extends JCommentsViewLegacy
{
	function display($tpl = null)
	{
		require_once (JPATH_COMPONENT . '/helpers/jcomments.php');
		require_once (JPATH_COMPONENT . '/version.php');

		$this->version = new JCommentsVersion();

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		JHtml::_('jcomments.stylesheet');
		if (version_compare(JVERSION, '4.0', 'lt')) {
			JHtml::_('behavior.framework');
		}

		if (version_compare(JVERSION, '3.0', 'ge')) {
			JCommentsHelper::addSubmenu('about');
			$this->bootstrap = true;
			$this->sidebar = JHtmlSidebar::render();
		} else {
			JCommentsHelper::addSubmenu('about');
		}


		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('A_SUBMENU_ABOUT'));
	}
}