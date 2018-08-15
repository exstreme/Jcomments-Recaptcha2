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
 * JComments common text functions
 */
class JCommentsText
{
	public static function formatDate($date = 'now', $format = null, $offset = null)
	{
		if ($format == 'DATETIME_FORMAT') {
			$format = null;
		}

		if (empty($format)) {
			$format = JText::_('DATE_FORMAT_LC1');
		}

		return JHTML::_('date', $date, $format);
	}

	/**
	 * Replaces newlines with HTML line breaks
	 *
	 * @param  $text string The input string.
	 *
	 * @return string Returns the altered string.
	 */
	public static function nl2br($text)
	{
		$text = preg_replace(array('/\r/u', '/^\n+/u', '/\n+$/u'), '', $text);
		$text = str_replace("\n", '<br />', $text);

		return $text;
	}

	/**
	 * Replaces HTML line breaks with newlines
	 *
	 * @param  $text string The input string.
	 *
	 * @return string Returns the altered string.
	 */
	public static function br2nl($text)
	{
		return str_replace('<br />', "\n", $text);
	}

	/**
	 * Escapes input string with slashes to use it in JavaScript
	 *
	 * @param  $text string The input string.
	 *
	 * @return string Returns the altered string.
	 */
	public static function jsEscape($text)
	{
		return addcslashes($text, "\\\\&\"\n\r<>'");
	}

	/**
	 * @param string $str    The input string.
	 * @param int $width    The column width.
	 * @param string $break    The line is broken using the optional break parameter.
	 * @param bool $cut    If the cut is set to TRUE, the string is always wrapped at the specified width. So if you have a word that is larger than the given width, it is broken apart.
	 *
	 * @return string
	 */
	public static function wordwrap($str, $width, $break, $cut = false)
	{
		if (!$cut) {
			$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){' . $width . ',}\b#U';
		} else {
			$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){' . $width . '}#';
		}

		$i = 1;
		$j = ceil(JCommentsText::strlen($str) / $width);
		$return = '';

		while ($i < $j) {
			preg_match($regexp, $str, $matches);
			$return .= $matches[0] . $break;
			$str = JString::substr($str, JCommentsText::strlen($matches[0]));
			$i++;
		}

		return $return . $str;
	}

	/**
	 * Inserts a separator in a very long continuous sequences of characters
	 *
	 * @param string $text The input string.
	 * @param int $maxLength The maximum length of sequence.
	 * @param string $customBreaker The custom string to be used as breaker.
	 *
	 * @return string Returns the altered string.
	 */
	public static function fixLongWords($text, $maxLength, $customBreaker = '')
	{
		$maxLength = (int)min(65535, $maxLength);

		if ($maxLength > 5) {
			ob_start();
			if ($customBreaker == '') {
				if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false) {
					$breaker = '<span style="margin: 0 -0.65ex 0 -1px;padding:0;"> </span>';
				} else {
					$breaker = '<span style="font-size:0;padding:0;margin:0;"> </span>';
				}
			} else {
				$breaker = $customBreaker;
			}

			$plainText = $text;
			$plainText = preg_replace(_JC_REGEXP_EMAIL, '', $plainText);
			$plainText = preg_replace('#<br\s?/?>#isu', '', $plainText);
			$plainText = preg_replace('#<img[^\>]+/>#isu', '', $plainText);
			$plainText = preg_replace('#<a.*?>(.*?)</a>#isu', '', $plainText);
			$plainText = preg_replace('#<span class="quote">(.*?)</span>#isu', '', $plainText);
			$plainText = preg_replace('#<span[^\>]*?>(.*?)</span>#isu', '\\1', $plainText);
			$plainText = preg_replace('#<pre.*?>(.*?)</pre>#isUu', '', $plainText);
			$plainText = preg_replace('#<blockquote.*?>(.*?)</blockquote>#isUu', '\\1 ', $plainText);
			$plainText = preg_replace('#<code.*?>(.*?)</code>#isUu', '', $plainText);
			$plainText = preg_replace('#<embed.*?>(.*?)</embed>#isu', '', $plainText);
			$plainText = preg_replace('#<object.*?>(.*?)</object>#isu', '', $plainText);
			$plainText = preg_replace('#<iframe.*?>(.*?)</iframe>#isu', '', $plainText);
			$plainText = preg_replace('#(^|\s|\>|\()((http://|https://|news://|ftp://|www.)\w+[^\s\[\]\<\>\"\'\)]+)#iu', '\\1', $plainText);
			$plainText = preg_replace('#<(b|strong|i|em|u|s|del|sup|sub|li)>(.*?)</(b|strong|i|em|u|s|del|sup|sub|li)>#isu', '\\2 ', $plainText);

			$words = explode(' ', $plainText);

			foreach ($words as $word) {
				if (JCommentsText::strlen($word) > $maxLength) {
					$text = str_replace($word, JCommentsText::wordwrap($word, $maxLength, $breaker, true), $text);
				}
			}
			ob_end_clean();

		}

		return $text;
	}

	public static function url($s)
	{
		if (isset($s) && preg_match('/^((http|https|ftp):\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}((:[0-9]{1,5})?\/.*)?$/i',
				$s)
		) {
			$url = preg_replace('|[^a-z0-9-~+_.?#=&;,/:]|i', '', $s);
			$url = str_replace(';//', '://', $url);
			if ($url != '') {
				$url = (!strstr($url, '://')) ? 'http://' . $url : $url;
				$url = preg_replace('/&([^#])(?![a-z]{2,8};)/', '&#038;$1', $url);

				return $url;
			}
		}

		return '';
	}

	public static function censor($text)
	{
		if (!empty($text)) {
			ob_start();
			$config = JCommentsFactory::getConfig();

			$words = $config->get('badwords');
			$replaceWord = $config->get('censor_replace_word', '***');

			if (!empty($words)) {
				$words = preg_replace("#,+#", ',', preg_replace("#[\n|\r]+#", ',', $words));
				$words = explode(",", $words);
				if (is_array($words)) {
					for ($i = 0, $n = count($words); $i < $n; $i++) {
						$word = trim($words[$i]);
						if ($word != '') {
							$word = str_replace('#', '\#', str_replace('\#', '#', $word));
							$txt = trim(preg_replace('#' . $word . '#ismu', $replaceWord, $text));
							// make safe from dummy bad words list
							if ($txt != '') {
								$text = $txt;
							}
						}
					}
				}
			}
			ob_end_clean();
		}

		return $text;
	}

	/**
	 * Cleans text of all formatting and scripting code
	 *
	 * @param  $text string The input string.
	 *
	 * @return string Returns the altered string.
	 */
	public static function cleanText($text)
	{
		$text = JCommentsFactory::getBBCode()->filter($text, true);

		if (JCommentsFactory::getConfig()->getInt('enable_custom_bbcode')) {
			$text = JCommentsFactory::getCustomBBCode()->filter($text, true);
		}

		$text = str_replace('<br />', ' ', $text);
		$text = preg_replace('#(\s){2,}#ismu', '\\1', $text);
		$text = preg_replace('#<script[^>]*>.*?</script>#ismu', '', $text);
		$text = preg_replace('#<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>#ismu', '\2 (\1)', $text);
		$text = preg_replace('#<!--.+?-->#ismu', '', $text);
		$text = preg_replace('#&nbsp;#ismu', ' ', $text);
		$text = preg_replace('#&amp;#ismu', ' ', $text);
		$text = preg_replace('#&quot;#ismu', ' ', $text);
		$text = strip_tags($text);
		$text = htmlspecialchars($text);
		$text = html_entity_decode($text);

		return $text;
	}

	public static function strlen($str)
	{
		return strlen(utf8_decode($str));
	}

	public static function substr($text, $length = 0)
	{
		return JString::substr($text, 0, $length);
	}
}