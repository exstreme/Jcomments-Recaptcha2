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
 * JComments BBCode
 */
class JCommentsBBCode
{
	var $_codes = array();

	function __construct()
	{
		ob_start();
		$this->registerCode('b');
		$this->registerCode('i');
		$this->registerCode('u');
		$this->registerCode('s');
		$this->registerCode('url');
		$this->registerCode('img');
		$this->registerCode('list');
		$this->registerCode('hide');
		$this->registerCode('quote');
		$this->registerCode('code');
		ob_end_clean();
	}

	function registerCode($str)
	{
		$acl = JCommentsFactory::getACL();
		$this->_codes[$str] = $acl->check('enable_bbcode_' . $str);
	}

	function getCodes()
	{
		return array_keys($this->_codes);
	}

	function enabled()
	{
		static $enabled = null;

		if ($enabled == null) {
			foreach ($this->_codes as $code => $_enabled) {
				if ($_enabled == 1 && $code != 'quote') {
					$enabled = $_enabled;
					break;
				}
			}
		}

		return $enabled;
	}

	function canUse($str)
	{
		return $this->_codes[$str] ? 1 : 0;
	}

	function filter($str, $forceStrip = false)
	{
		ob_start();
		$patterns = array();
		$replacements = array();

		// disabled BBCodes
		$patterns[] = '/\[email\](.*?)\[\/email\]/iu';
		$replacements[] = ' \\1';
		$patterns[] = '/\[sup\](.*?)\[\/sup\]/iu';
		$replacements[] = ' \\1';
		$patterns[] = '/\[sub\](.*?)\[\/sub\]/iu';
		$replacements[] = ' \\1';

		//empty tags
		foreach ($this->_codes as $code => $enabled) {
			$patterns[] = '/\[' . $code . '\]\[\/' . $code . '\]/iu';
			$replacements[] = '';
		}
		// B
		if (($this->canUse('b') == 0) || ($forceStrip)) {
			$patterns[] = '/\[b\](.*?)\[\/b\]/iu';
			$replacements[] = '\\1';
		}

		// I
		if (($this->canUse('i') == 0) || ($forceStrip)) {
			$patterns[] = '/\[i\](.*?)\[\/i\]/iu';
			$replacements[] = '\\1';
		}

		// U
		if (($this->canUse('u') == 0) || ($forceStrip)) {
			$patterns[] = '/\[u\](.*?)\[\/u\]/iu';
			$replacements[] = '\\1';
		}

		// S
		if (($this->canUse('s') == 0) || ($forceStrip)) {
			$patterns[] = '/\[s\](.*?)\[\/s\]/iu';
			$replacements[] = '\\1';
		}

		// URL
		if (($this->canUse('url') == 0) || ($forceStrip)) {
			$patterns[] = '/\[url\](.*?)\[\/url\]/iu';
			$replacements[] = '\\1';
			$patterns[] = '/\[url=([^\s<\"\'\]]*?)\](.*?)\[\/url\]/iu';
			$replacements[] = '\\2: \\1';
		}

		// IMG
		if (($this->canUse('img') == 0) || ($forceStrip)) {
			$patterns[] = '/\[img\](.*?)\[\/img\]/iu';
			$replacements[] = '\\1';
		}

		// HIDE
		if (($this->canUse('hide') == 0) || ($forceStrip)) {
			$patterns[] = '/\[hide\](.*?)\[\/hide\]/iu';
			$user = JFactory::getUser();
			if ($user->id) {
				$replacements[] = '\\1';
			} else {
				$replacements[] = '';
			}
		}

		// CODE
		if ($forceStrip) {
			$codePattern = '#\[code\=?([a-z0-9]*?)\](.*?)\[\/code\]#ismu';
			$patterns[] = $codePattern;
			$replacements[] = '\\2';
		}

		$str = preg_replace($patterns, $replacements, $str);

		// LIST
		if (($this->canUse('list') == 0) || ($forceStrip)) {
			$matches = array();
			$matchCount = preg_match_all('/\[list\](<br\s?\/?\>)*(.*?)(<br\s?\/?\>)*\[\/list\]/isu',
				$str, $matches);
			for ($i = 0; $i < $matchCount; $i++) {
				$textBefore = preg_quote($matches[2][$i]);
				$textAfter = preg_replace('#(<br\s?\/?\>)*\[\*\](<br\s?\/?\>)*#isu', "<br />",
					$matches[2][$i]);
				$textAfter = preg_replace('#^<br />#isu', '', $textAfter);
				$textAfter = preg_replace('#(<br\s?\/?\>)+#isu', '<br />', $textAfter);
				$str = preg_replace('#\[list\](<br\s?\/?\>)*' . $textBefore . '(<br\s?\/?\>)*\[/list\]#isu',
					"\n$textAfter\n", $str);
			}
			$matches = array();
			$matchCount = preg_match_all('#\[list=(a|A|i|I|1)\](<br\s?\/?\>)*(.*?)(<br\s?\/?\>)*\[\/list\]#isu',
				$str, $matches);
			for ($i = 0; $i < $matchCount; $i++) {
				$textBefore = preg_quote($matches[3][$i]);
				$textAfter = preg_replace('#(<br\s?\/?\>)*\[\*\](<br\s?\/?\>)*#isu', '<br />',
					$matches[3][$i]);
				$textAfter = preg_replace('#^<br />#u', "", $textAfter);
				$textAfter = preg_replace('#(<br\s?\/?\>)+#u', '<br />', $textAfter);
				$str = preg_replace('#\[list=(a|A|i|I|1)\](<br\s?\/?\>)*' . $textBefore . '(<br\s?\/?\>)*\[/list\]#isu',
					"\n$textAfter\n", $str);
			}
		}

		if ($forceStrip) {
			// QUOTE
			$quotePattern = '#\[quote\s?name=\"([^\"\'\<\>\(\)]+)+\"\](<br\s?\/?\>)*(.*?)(<br\s?\/?\>)*\[\/quote\]#iu';
			$quoteReplace = ' ';
			while (preg_match($quotePattern, $str)) {
				$str = preg_replace($quotePattern, $quoteReplace, $str);
			}
			$quotePattern = '#\[quote[^\]]*?\](<br\s?\/?\>)*([^\[]+)(<br\s?\/?\>)*\[\/quote\]#iu';
			$quoteReplace = ' ';
			while (preg_match($quotePattern, $str)) {
				$str = preg_replace($quotePattern, $quoteReplace, $str);
			}

			$str = preg_replace('#\[\/?(b|strong|i|em|u|s|del|sup|sub|url|img|list|quote|code|hide)\]#isu',
				'', $str);
		}

		$str = trim(preg_replace('#( ){2,}#iu', '\\1', $str));

		ob_end_clean();

		return $str;
	}


	function replace($str)
	{
		ob_start();

		$config = JCommentsFactory::getConfig();

		$patterns = array();
		$replacements = array();

		// B
		$patterns[] = '/\[b\](.*?)\[\/b\]/iu';
		$replacements[] = '<strong>\\1</strong>';

		// I
		$patterns[] = '/\[i\](.*?)\[\/i\]/iu';
		$replacements[] = '<em>\\1</em>';

		// U
		$patterns[] = '/\[u\](.*?)\[\/u\]/iu';
		$replacements[] = '<u>\\1</u>';

		// S
		$patterns[] = '/\[s\](.*?)\[\/s\]/iu';
		$replacements[] = '<del>\\1</del>';

		// SUP
		$patterns[] = '/\[sup\](.*?)\[\/sup\]/iu';
		$replacements[] = '<sup>\\1</sup>';

		// SUB
		$patterns[] = '/\[sub\](.*?)\[\/sub\]/iu';
		$replacements[] = '<sub>\\1</sub>';

		// URL (local)
		$liveSite = JURI::base();

		$patterns[] = '#\[url\](' . preg_quote($liveSite, '#') . '[^\s<\"\']*?)\[\/url\]#iu';
		$replacements[] = '<a href="\\1" target="_blank">\\1</a>';

		$patterns[] = '#\[url=(' . preg_quote($liveSite, '#') . '[^\s<\"\'\]]*?)\](.*?)\[\/url\]#iu';
		$replacements[] = '<a href="\\1" target="_blank">\\2</a>';

		$patterns[] = '/\[url=(\#|\/)([^\s<\"\'\]]*?)\](.*?)\[\/url\]/iu';
		$replacements[] = '<a href="\\1\\2" target="_blank">\\3</a>';


		// URL (external)
		$patterns[] = '#\[url\](http:\/\/)?([^\s<\"\']*?)\[\/url\]#iu';
		$replacements[] = '<a href="http://\\2" rel="external nofollow" target="_blank">\\2</a>';

		$patterns[] = '/\[url=([a-z]*\:\/\/)([^\s<\"\'\]]*?)\](.*?)\[\/url\]/iu';
		$replacements[] = '<a href="\\1\\2" rel="external nofollow" target="_blank">\\3</a>';

		$patterns[] = '/\[url=([^\s<\"\'\]]*?)\](.*?)\[\/url\]/iu';
		$replacements[] = '<a href="http://\\1" rel="external nofollow" target="_blank">\\2</a>';

		$patterns[] = '#\[url\](.*?)\[\/url\]#iu';
		$replacements[] = '\\1';

		// EMAIL
		$patterns[] = '#\[email\]([^\s\<\>\(\)\"\'\[\]]*?)\[\/email\]#iu';
		$replacements[] = '\\1';

		// IMG
		$patterns[] = '#\[img\]([a-z]*\:\/\/)([^\s\<\>\(\)\"\']*?)\[\/img\]#iu';
		$replacements[] = '<img class="img" src="\\1\\2" alt="" border="0" />';

		$patterns[] = '#\[img\](.*?)\[\/img\]#iu';
		$replacements[] = '\\1';

		// HIDE
		$patterns[] = '/\[hide\](.*?)\[\/hide\]/iu';
		$user = JFactory::getUser();
		if ($user->id) {
			$replacements[] = '\\1';
		} else {
			$replacements[] = '<span class="hidden">' . JText::_('BBCODE_MESSAGE_HIDDEN_TEXT') . '</span>';
		}

		// CODE
		$geshiEnabled = $config->getInt('enable_geshi', 0);
		$codePattern = '#\[code\=?([a-z0-9]*?)\](.*?)\[\/code\]#ismu';
		$geshiLibrary = JPATH_SITE . '/plugins/content/geshi/geshi/geshi.php';

		$geshiEnabled = $geshiEnabled && is_file($geshiLibrary);

		if ($geshiEnabled) {
			require_once($geshiLibrary);

			if (!function_exists('jcommentsProcessGeSHi')) {
				function jcommentsProcessGeSHi($matches)
				{
					$lang = $matches[1] != '' ? $matches[1] : 'php';
					$text = $matches[2];
					$html_entities_match = array('#\<br \/\>#', "#<#", "#>#", "|&#39;|", '#&quot;#', '#&nbsp;#');
					$html_entities_replace = array("\n", '&lt;', '&gt;', "'", '"', ' ');
					$text = preg_replace($html_entities_match, $html_entities_replace, $text);
					$text = preg_replace('#(\r|\n)*?$#ism', '', $text);
					$text = str_replace('&lt;', '<', $text);
					$text = str_replace('&gt;', '>', $text);

					$geshi = new GeSHi($text, $lang);
					$text = $geshi->parse_code();

					return '[code]' . $text . '[/code]';
				}
			}

			$patterns[] = $codePattern;
			$replacements[] = '<span class="code">' . JText::_('COMMENT_TEXT_CODE') . '</span>\\2';
			$str = preg_replace_callback($codePattern, 'jcommentsProcessGeSHi', $str);
		} else {
			$patterns[] = $codePattern;
			$replacements[] = '<span class="code">' . JText::_('COMMENT_TEXT_CODE') . '</span><code>\\2</code>';

			if (!function_exists('jcommentsProcessCode')) {
				function jcommentsProcessCode($matches)
				{
					$text = htmlspecialchars(trim($matches[0]));
					$text = str_replace("\r", '', $text);
					$text = str_replace("\n", '<br />', $text);

					return $text;
				}
			}
			$str = preg_replace_callback($codePattern, 'jcommentsProcessCode', $str);
		}

		$str = preg_replace($patterns, $replacements, $str);

		// QUOTE
		$quotePattern = '#\[quote\s?name=\"([^\"\<\>\(\)]*?)\"\](<br\s?\/?\>)*?(.*?)(<br\s?\/?\>)*\[\/quote\](<br\s?\/?\>)*?#ismu';
		$quoteReplace = '<span class="quote">' . JText::sprintf('COMMENT_TEXT_QUOTE_EXTENDED', '\\1') . '</span><blockquote><div>\\3</div></blockquote>';

		while (preg_match($quotePattern, $str)) {
			$str = preg_replace($quotePattern, $quoteReplace, $str);
		}

		$quotePattern = '#\[quote[^\]]*?\](<br\s?\/?\>)*([^\[]+)(<br\s?\/?\>)*\[\/quote\](<br\s?\/?\>)*?#ismUu';
		$quoteReplace = '<span class="quote">' . JText::_('COMMENT_TEXT_QUOTE') . '</span><blockquote><div>\\2</div></blockquote>';
		while (preg_match($quotePattern, $str)) {
			$str = preg_replace($quotePattern, $quoteReplace, $str);
		}

		// LIST
		$matches = array();
		$matchCount = preg_match_all('#\[list\](<br\s?\/?\>)*(.*?)(<br\s?\/?\>)*\[\/list\]#iu', $str, $matches);
		for ($i = 0; $i < $matchCount; $i++) {
			$textBefore = preg_quote($matches[2][$i]);
			$textAfter = preg_replace('#(<br\s?\/?\>)*\[\*\](<br\s?\/?\>)*#isu', "</li><li>", $matches[2][$i]);
			$textAfter = preg_replace('#^</?li>#u', '', $textAfter);
			$textAfter = str_replace("\n</li>", "</li>", $textAfter . "</li>");
			$str = preg_replace('#\[list\](<br\s?\/?\>)*' . $textBefore . '(<br\s?\/?\>)*\[/list\]#isu', "<ul>$textAfter</ul>", $str);
		}

		$matches = array();
		$matchCount = preg_match_all('#\[list=(a|A|i|I|1)\](<br\s?\/?\>)*(.*?)(<br\s?\/?\>)*\[\/list\]#isu', $str, $matches);
		for ($i = 0; $i < $matchCount; $i++) {
			$textBefore = preg_quote($matches[3][$i]);
			$textAfter = preg_replace('#(<br\s?\/?\>)*\[\*\](<br\s?\/?\>)*#isu', "</li><li>", $matches[3][$i]);
			$textAfter = preg_replace('#^</?li>#u', '', $textAfter);
			$textAfter = str_replace("\n</li>", "</li>", $textAfter . "</li>");
			$str = preg_replace('#\[list=(a|A|i|I|1)\](<br\s?\/?\>)*' . $textBefore . '(<br\s?\/?\>)*\[/list\]#isu', "<ol type=\\1>$textAfter</ol>", $str);
		}

		$str = preg_replace('#\[\/?(b|i|u|s|sup|sub|url|img|list|quote|code|hide)\]#iu', '', $str);
		ob_end_clean();

		return $str;
	}

	function removeQuotes($text)
	{
		$text = preg_replace(array('#\n?\[quote.*?\].+?\[\/quote\]\n?#isu', '#\[\/quote\]#isu'), '', $text);
		$text = preg_replace('#<br />+#is', '', $text);

		return $text;
	}

	function removeHidden($text)
	{
		$text = preg_replace('#\[hide\](.*?)\[\/hide\]#isu', '', $text);
		$text = preg_replace('#<br />+#is', '', $text);

		return $text;
	}
}