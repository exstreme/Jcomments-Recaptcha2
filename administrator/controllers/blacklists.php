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

class JCommentsControllerBlacklists extends JCommentsControllerList
{
	function display($cachable = false, $urlparams = array())
	{
		$this->input->set('view', 'blacklists');

		parent::display($cachable, $urlparams);
	}

	public function getModel($name = 'Blacklists', $prefix = 'JCommentsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}