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

/**
 * JComments CustomBBCode
 */
class JCommentsCustomBBCode
{
	var $codes = array();
	var $patterns = array();
	var $filter_patterns = array();
	var $html_replacements = array();
	var $text_replacement = array();

	function __construct()
	{
		$this->patterns = array();
		$this->html_replacements = array();
		$this->text_replacements = array();
		$this->codes = array();

		ob_start();
		
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jcomments_custom_bbcodes'));
		$query->where($db->quoteName('published') . ' = 1');
		$query->order($db->escape('ordering'));
		$db->setQuery($query);

		$codes = $db->loadObjectList();

		if (count($codes)) {
			foreach ($codes as $code) {

				// fix \w pattern issue for UTF-8 encoding
				// details: http://www.phpwact.org/php/i18n/utf-8#w_w_b_b_meta_characters
				$code->pattern = preg_replace('#(\\\w)#u', '\p{L}', $code->pattern);

				// check button permission
				if (JCommentsACL::check($code->button_acl, false)) {
					if ($code->button_image != '') {
						if (strpos($code->button_image, JURI::base()) === false) {
							$code->button_image = JURI::base() . trim($code->button_image, '/');
						}
					}
					$this->codes[] = $code;
				} else {
					$this->filter_patterns[] = '#' . $code->pattern . '#ismu';
				}

				$this->patterns[] = '#' . $code->pattern . '#ismu';
				$this->html_replacements[] = $code->replacement_html;
				$this->text_replacements[] = $code->replacement_text;
			}
		}
		ob_end_clean();
	}

	public function enabled()
	{
		//return count($this->codes) > 0;
		return 1;
	}

	public function getList()
	{
		return $this->codes;
	}

	public function filter($str, $forceStrip = false)
	{
		if (count($this->filter_patterns)) {
			ob_start();
			$filter_replacement = $this->text_replacements;
			$str = preg_replace($this->filter_patterns, $filter_replacement, $str);
			ob_end_clean();
		}
		if ($forceStrip === true) {
			ob_start();
			$str = preg_replace($this->patterns, $this->text_replacements, $str);
			ob_end_clean();
		}

		return $str;
	}

	public function replace($str, $textReplacement = false)
	{
		if (count($this->patterns)) {
			ob_start();
			$str = preg_replace($this->patterns, ($textReplacement ? $this->text_replacements : $this->html_replacements), $str);
			ob_end_clean();
		}

		return $str;
	}
}