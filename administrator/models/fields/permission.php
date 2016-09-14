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

class JFormFieldPermission extends JFormField
{
	public $type = 'Permission';

	protected function getInput()
	{
		$this->element['class'] = $this->element['class'] . ' checkbox';

		$class = $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
		$disabled = ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$guest = (string)$this->element['guest'] == 'false' ? false : true;
		$value = $this->element['value'] ? (string)$this->element['value'] : '1';

		$checked = '';

		if (is_array($this->value)) {
			if (isset($this->value['group'])) {
				$value = $this->value['group'];
			}
			if (isset($this->value['value'])) {
				$checked = ' checked="checked"';
			}
		}

		if ($guest === false) {
			$guest_usergroup = JComponentHelper::getParams('com_users')->get('guest_usergroup', 1);
			if ($value == 1 || $value == $guest_usergroup) {
				$checked = '';
				$disabled = ' disabled="disabled"';
			}
		}

		$onclick = $this->element['onclick'] ? ' onclick="' . (string)$this->element['onclick'] . '"' : '';

		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string)$this->element['label'] : (string)$this->element['name'];
		$text = $this->translateLabel ? JText::_($text) : $text;

		// Build the class for the label.
		$labelClass = !empty($this->description) ? 'hasTip' : '';
		$labelClass = $this->required == true ? $labelClass . ' required' : $labelClass;
		$labelClass = !empty($this->labelClass) ? $labelClass . ' ' . $this->labelClass : $labelClass;
		$labelClass = $labelClass . ' checkbox';

		$title = '';
		if (!empty($this->description)) {
			$title = ' title="' . htmlspecialchars(trim($text, ':') . '::' . ($this->translateDescription ? JText::_($this->description) : $this->description), ENT_COMPAT, 'UTF-8') . '"';
		}

		$html = array();
		$html[] = '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $labelClass . '"' . $title . '>';
		$html[] = '<input type="checkbox" name="' . $this->name . '[]" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"' . $class . $checked . $disabled . $onclick . ' />';
		$html[] = $text;
		$html[] = '</label>';

		return implode('', $html);
	}

	protected function getLabel()
	{
		return '';
	}

	protected function getId($fieldId, $fieldName)
	{
		$value = parent::getId($fieldId, $fieldName);
		$tmp = md5(mt_rand());

		return $value . '_' . $tmp;
	}
}