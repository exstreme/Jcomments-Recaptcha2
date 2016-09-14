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

class JCommentsControllerAbout extends JCommentsControllerLegacy
{
	function display($cachable = false, $urlparams = array())
	{
		JFactory::getApplication()->input->set('view', 'default');

		parent::display($cachable, $urlparams);
	}
}