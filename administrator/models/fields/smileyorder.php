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

class JFormFieldSmileyOrder extends JFormField
{
	protected $type = 'SmileyOrder';

	protected function getInput()
	{
		$html = array();
		$attr = '';

		$attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
		$attr .= ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';

		$attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

		$smileyId = (int)$this->form->getValue('id');

		$query = 'SELECT ordering AS value, name AS text' .
			' FROM #__jcomments_smilies' .
			' ORDER BY ordering';

		if ((string)$this->element['readonly'] == 'true') {
			$html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $smileyId ? 0 : 1);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		} else {
			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $smileyId ? 0 : 1);
		}

		return implode($html);
	}
}