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
 * JComments smilies support
 */
class JCommentsSmilies
{
	protected $_smilies = array();
	protected $_replacements = array();

	public function __construct()
	{
		if (count($this->_replacements) == 0) {
			$config = JCommentsFactory::getConfig();

			$path = JUri::root(true) . '/' . trim(str_replace('\\', '/', $config->get('smilies_path')), '/') . '/';
			$smilies = $config->get('smilies');

			if (!empty($smilies)) {
				$values = explode("\n", $smilies);
				foreach ($values as $value) {
					list ($code, $image) = explode("\t", $value);
					$this->_smilies[$code] = $image;
				}
			}

			$list = $this->_smilies;
			uksort($list, array($this, 'compare'));

			foreach ($list as $code => $image) {
				$this->_replacements['code'][] = '#(^|\s|\n|\r|\>)(' . preg_quote($code, '#') . ')(\s|\n|\r|\<|$)#ismu';
				$this->_replacements['icon'][] = '\\1 \\2 \\3';
				$this->_replacements['code'][] = '#(^|\s|\n|\r|\>)(' . preg_quote($code, '#') . ')(\s|\n|\r|\<|$)#ismu';
				$this->_replacements['icon'][] = '\\1<img src="' . $path . $image . '" alt="' . htmlspecialchars($code) . '" />\\3';
			}
		}
	}

	public function compare($a, $b)
	{
		if (strlen($a) == strlen($b)) {
			return 0;
		}

		return (strlen($a) > strlen($b)) ? -1 : 1;
	}

	public function getList()
	{
		return $this->_smilies;
	}

	public function replace($str)
	{
		if (count($this->_replacements) > 0) {
			$str = JCommentsText::br2nl($str);
			$str = preg_replace($this->_replacements['code'], $this->_replacements['icon'], $str);
			$str = JCommentsText::nl2br($str);
		}

		return $str;
	}

	public function strip($str)
	{
		if (count($this->_replacements) > 0) {
			$str = JCommentsText::br2nl($str);
			$str = preg_replace($this->_replacements['code'], '\\1\\3', $str);
			$str = JCommentsText::nl2br($str);
		}

		return $str;
	}
}