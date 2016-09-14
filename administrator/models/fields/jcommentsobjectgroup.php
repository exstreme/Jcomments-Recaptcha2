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

jimport('joomla.filesystem.folder');
jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldJCommentsObjectGroup extends JFormFieldList
{
	protected $type = 'JCommentsObjectGroup';

	protected function getInput()
	{
		$attr = '';
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		$options = (array) $this->getOptions();

		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
	}

	protected function getOptions()
	{
		$options = array();

		$db = JFactory::getDBO();
		$db->setQuery('SELECT DISTINCT `element` FROM `#__extensions` WHERE `type` = "component" ORDER BY `element`;');
		$components = $db->loadColumn();

		$plugins = JFolder::files(JPATH_SITE . '/components/com_jcomments/plugins/', '\.plugin\.php', true, false);

		if (is_array($plugins)) {
			foreach ($plugins as $plugin) {
				$pluginName = str_replace('.plugin.php', '', $plugin);
				foreach ($components as $component) {
					if ($pluginName == $component || strpos($pluginName, $component . '_') !== false) {
						$options[] = JHTML::_('select.option', $pluginName, $pluginName);
					}
				}
			}
		} else {
			$options[] = JHTML::_('select.option', 'com_jcomments', 'com_jcomments');
		}

		return $options;
	}
}