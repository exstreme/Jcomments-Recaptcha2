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

class JFormFieldJCommentsTemplate extends JFormFieldList
{
	protected $type = 'JCommentsTemplate';

	protected function getInput()
	{
		$this->multiple = false;

		return parent::getInput();
	}

	protected function getOptions()
	{
		$options = array();

		$folders = JFolder::folders(JPATH_ROOT . '/components/com_jcomments/tpl/');
		if (is_array($folders)) {
			foreach ($folders as $folder) {
				$options[] = JHtml::_('select.option', $folder, $folder);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}