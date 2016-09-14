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

JTable::addIncludePath(JPATH_COMPONENT . '/tables');

class JCommentsModelSubscription extends JCommentsModelForm
{
	public function getTable($type = 'Subscription', $prefix = 'JCommentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_jcomments.subscription', 'subscription', array('control' => 'jform',
																			  'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		if (!$this->canEditState((object)$data)) {
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_jcomments.edit.subscription.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}
}