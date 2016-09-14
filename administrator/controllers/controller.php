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

jimport('joomla.application.component.controller');

if (version_compare(JVERSION, '3.0', 'ge')) {
	class JCommentsControllerLegacy extends JControllerLegacy
	{
		public function display($cachable = false, $urlparams = array())
		{
			parent::display($cachable, $urlparams);
		}
	}
} else {
	class JCommentsControllerLegacy extends JController
	{
		protected $input;

		public function __construct($config = array())
		{
			parent::__construct($config);

			$this->input = JFactory::getApplication()->input;
		}

		public function display($cachable = false, $urlparams = false)
		{
			parent::display($cachable, $urlparams);
		}

		public function getInput()
		{
			return $this->input;
		}
	}
}