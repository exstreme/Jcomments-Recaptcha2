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

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldJCommentsCaptcha extends JFormFieldList
{
	protected $type = 'JCommentsCaptcha';

	protected function getInput()
	{
		$this->multiple = false;

		return parent::getInput();
	}

	protected function getOptions()
	{
		$options = array();

		JPluginHelper::importPlugin('jcomments');

		if (version_compare(JVERSION, '3.0', 'ge')) {
			$dispatcher = JEventDispatcher::getInstance();
		} else {
			$dispatcher = JDispatcher::getInstance();
		}

		$items = $dispatcher->trigger('onJCommentsCaptchaEngines', array());

		if (is_array($items)) {
			foreach ($items as $item) {
				foreach ($item as $code => $text) {
					$options[] = JHtml::_('select.option', $code, $text);
				}
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}