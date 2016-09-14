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

abstract class JHtmlJComments
{
	protected static $loaded = array();

	public static function stylesheet()
	{
		if (!empty(self::$loaded[__METHOD__])) {
			return;
		}

		$document = JFactory::getDocument();
		$document->addStylesheet(JURI::root(true) . '/administrator/components/com_jcomments/assets/css/style.css', 'text/css', null);

		if (version_compare(JVERSION, '3.0', 'lt')) {
			$document->addStylesheet(JURI::root(true) . '/administrator/components/com_jcomments/assets/css/legacy.css', 'text/css', null);
		}

		if (JFactory::getLanguage()->isRTL()) {
			$document->addStylesheet(JURI::root(true) . '/administrator/components/com_jcomments/assets/css/style_rtl.css', 'text/css', null);
		}

		self::$loaded[__METHOD__] = true;

		return;
	}

	public static function jquery()
	{
		if (!empty(self::$loaded[__METHOD__])) {
			return;
		}

		if (version_compare(JVERSION, '3.0', 'lt')) {
			$document = JFactory::getDocument();
			$document->addScript(JURI::root(true) . '/administrator/components/com_jcomments/assets/js/jquery.js');
			$document->addScript(JURI::root(true) . '/administrator/components/com_jcomments/assets/js/jquery-noconflict.js');
		} else {
			JHtml::_('jquery.framework');
		}

		self::$loaded[__METHOD__] = true;

		return;
	}

	public static function bootstrap()
	{
		if (!empty(self::$loaded[__METHOD__])) {
			return;
		}

		if (version_compare(JVERSION, '3.0', 'lt')) {
			JHtml::_('jcomments.jquery');

			$document = JFactory::getDocument();
			$document->addStylesheet(JURI::root(true) . '/administrator/components/com_jcomments/assets/css/bootstrap-legacy.css');
			$document->addScript(JURI::root(true) . '/administrator/components/com_jcomments/assets/js/bootstrap-legacy.js');
		} else {
			JHtml::_('bootstrap.framework');
		}

		self::$loaded[__METHOD__] = true;

		return;
	}

	public static function modal($name = '', $text = '', $url = '', $title = '', $onClose = '', $iconClass = 'out-2', $buttonClass = 'btn-small', $width = 500, $height = 300)
	{

		if (strlen($title) == 0) {
			$title = $text;
		}

		$text = JText::_($text);
		$title = JText::_($title);

		$html = '';

		if (version_compare(JVERSION, '3.0', 'lt')) {
			$rel = "{handler: 'iframe', size: {x: $width, y: $height}, onClose: function() {" . $onClose . "}}";

			$html .= "<a class=\"modal btn " . $buttonClass . "\" href=\"$url\" rel=\"" . $rel . "\">\n";
			$html .= "<i class=\"icon-" . $iconClass . "\">\n</i>\n";
			$html .= "$text\n";
			$html .= '</a>';
		} else {
			$html = "<button class=\"btn btn-micro " . $buttonClass . "\" data-toggle=\"modal\" data-target=\"#modal-" . $name . "\">\n";
			$html .= "<i class=\"icon-" . $iconClass . "\">\n</i>\n";
			$html .= "$text\n";
			$html .= "</button>\n";

			// Build the options array for the modal
			$params = array();
			$params['title'] = $title;
			$params['url'] = (substr($url, 0, 4) !== 'http') ? JURI::base() . $url : $url;
			$params['height'] = $height;
			$params['width'] = $width;
			$html .= JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params);

			// If an $onClose event is passed, add it to the modal JS object
			if (strlen($onClose) >= 1) {
				$html .= "<script>\n";
				$html .= "jQuery('#modal-" . $name . "').on('hide', function () {\n";
				$html .= $onClose . ";\n";
				$html .= "}";
				$html .= ");";
				$html .= "</script>\n";
			}
		}

		echo $html;
	}

	public static function usergroups($name, $selected, $checkSuperAdmin = false)
	{
		static $count;

		$count++;

		$isSuperAdmin = JFactory::getUser()->authorise('core.admin');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, COUNT(DISTINCT b.id) AS level');
		$query->from($db->quoteName('#__usergroups') . ' AS a');
		$query->join('LEFT', $db->quoteName('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
		$query->group('a.id, a.title, a.lft, a.rgt, a.parent_id');
		$query->order('a.lft ASC');
		$db->setQuery($query);
		$groups = $db->loadObjectList();

		$html = array();

		for ($i = 0, $n = count($groups); $i < $n; $i++) {
			$item = & $groups[$i];

			// If checkSuperAdmin is true, only add item if the user is superadmin or the group is not super admin
			if ((!$checkSuperAdmin) || $isSuperAdmin || (!JAccess::checkGroup($item->id, 'core.admin'))) {
				// Setup  the variable attributes.
				$eid = $count . 'group_' . $item->id;

				// Don't call in_array unless something is selected
				$checked = '';
				if ($selected) {
					$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';
				}
				$rel = ($item->parent_id > 0) ? ' rel="' . $count . 'group_' . $item->parent_id . '"' : '';

				// Build the HTML for the item.
				$html[] = '	<div class="control-group">';
				$html[] = '		<div class="controls">';
				$html[] = '			<label class="checkbox" for="' . $eid . '">';
				$html[] = '			<input type="checkbox" name="' . $name . '[]" value="' . $item->id . '" id="' . $eid . '"';
				$html[] = '					' . $checked . $rel . ' />';
				$html[] = '			' . str_repeat('<span class="gi">|&mdash;</span>', $item->level) . $item->title;
				$html[] = '			</label>';
				$html[] = '		</div>';
				$html[] = '	</div>';
			}
		}

		return implode("\n", $html);
	}
}