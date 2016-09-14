<?php
/**
 * JComments - Joomla Comment System
 *
 * @version 3.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 */

defined('JPATH_PLATFORM') or die;

class JFormRuleIp extends JFormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{
		$required = ((string)$element['required'] == 'true' || (string)$element['required'] == 'required');
		if (!$required && empty($value)) {
			return true;
		}

		if (function_exists('filter_var')) {
			return (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
				|| filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6));
		}

		return ($this->is_valid_ipv4($value) || $this->is_valid_ipv6($value));
	}

	protected function is_valid_ipv4($value)
	{
		if (!preg_match("/^([0-9\.]+)$/", strtolower($value))) {
			return false;
		}

		$segments = explode('.', $value);

		if (count($segments) != 4) {
			return false;
		}

		if ((int)$segments[0] == 0) {
			return false;
		}

		foreach ($segments as $segment) {
			if ((int)$segment > 255) {
				return false;
			}
		}

		return true;
	}

	protected function is_valid_ipv6($value)
	{
		// If it contains anything other than hex characters, periods, colons or a / it's not IPV6
		if (!preg_match("/^([0-9a-f\.\/:]+)$/", strtolower($value))) {
			return false;
		}

		// An IPV6 address needs at minimum two colons in it
		if (substr_count($value, ":") < 2) {
			return false;
		}

		// If any of the "octets" are longer than 4 characters it's not valid
		$segments = preg_split("/[:\/]/", $value);
		foreach ($segments as $segment) {
			if (strlen($segment) > 4) {
				return false;
			}
		}

		return true;
	}
}