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
 * JComments Content Plugin Helper
 * 
 */
class JCommentsContentPluginHelper
{
	/**
	 *
	 * @param object $row The content item object
	 * @param array $patterns
	 * @param array $replacements
	 * @param boolean $fromText Process 'text' field or introtext/fulltext fields?
	 * @return void
	 */
	protected static function _processTags( &$row, $patterns = array(), $replacements = array(), $fromText = true )
	{
		if (count($patterns) > 0) {
			ob_start();
			if (isset($row->text)) {
				$row->text = preg_replace($patterns, $replacements, $row->text);
			}
			if (isset($row->introtext)) {
				$row->introtext = preg_replace($patterns, $replacements, $row->introtext);
			}
			if (isset($row->fulltext)) {
				$row->fulltext = preg_replace($patterns, $replacements, $row->fulltext);
			}
			ob_end_clean();
		}
	}
	
	/**
	 * Searches given tag in content object
	 *
	 * @param object $row The content item object
	 * @param string $pattern
	 * @param boolean $fromText Process 'text' field or introtext/fulltext fields?
	 * @return boolean True if any tag found, False otherwise
	 */
	protected static function _findTag( &$row, $pattern, $fromText = false )
	{
		if (true == $fromText) {
			return (isset($row->text) && preg_match($pattern, $row->text));
		} else {
			return ((isset($row->introtext) && preg_match($pattern, $row->introtext)) || (isset($row->fulltext) && preg_match($pattern, $row->fulltext)));
		}
	}
	
	/**
	 * Replaces or removes commenting systems tags like {moscomment}, {jomcomment} etc
	 *
	 * @param object $row The content item object
	 * @param boolean $removeTags Remove all 3rd party tags or replace it to JComments tags?
	 * @param boolean $fromText Process 'text' field or introtext/fulltext fields?
	 * @return void
	 */
	public static function processForeignTags( &$row, $removeTags = false, $fromText = true )
	{
		if (false == $removeTags) {
			$patterns = array('#\{(jomcomment|easycomments|KomentoEnable)\}#is', '#\{(\!jomcomment|KomentoDisable)\}#is', '#\{KomentoLock\}#is');
			$replacements = array('{jcomments on}', '{jcomments off}', '{jcomments lock}');
		} else {
			$patterns = array('#\{(jomcomment|easycomments|KomentoEnable|KomentoDisable|KomentoLock)\}#is');
			$replacements = array('');
		}
		
		self::_processTags($row, $patterns, $replacements, $fromText);
	}
	
	/**
	 * Return true if one of text fields contains {jcomments on} tag
	 *
	 * @param object $row Content object
	 * @param boolean $fromText Look field 'text' or 'introtext' & 'fulltext' 
	 * @return boolean True if {jcomments on} found, False otherwise
	 */
	public static function isEnabled( &$row, $fromText = false )
	{
		return self::_findTag($row, '/{jcomments\s+on}/is', $fromText);
	}
	
	/**
	 * Return true if one of text fields contains {jcomments off} tag
	 *
	 * @param object $row Content object
	 * @param boolean $fromText Look field 'text' or 'introtext' & 'fulltext' 
	 * @return boolean True if {jcomments off} found, False otherwise
	 */
	public static function isDisabled( &$row, $fromText = false )
	{
		return self::_findTag($row, '/{jcomments\s+off}/is', $fromText);
	}
	
	/**
	 * Return true if one of text fields contains {jcomments lock} tag
	 *
	 * @param object $row Content object
	 * @param boolean $fromText Look field 'text' or 'introtext' & 'fulltext' 
	 * @return boolean True if {jcomments lock} found, False otherwise
	 */
	public static function isLocked( &$row, $fromText = false )
	{
		return self::_findTag($row, '/{jcomments\s+lock}/is', $fromText);
	}
	
	/**
	 * Clears all JComments tags from content item
	 *
	 * @param object $row Content object
	 * @param boolean $fromText Look field 'text' or 'introtext' & 'fulltext'
	 * @return void
	 */
	public static function clear( &$row, $fromText = false )
	{
		$patterns = array('/{jcomments\s+(off|on|lock)}/is');
		$replacements = array('');
		
		self::_processTags($row, $patterns, $replacements, $fromText);
	}

	/**
	 * Checks if comments are enabled for specified category
	 * 
	 * @param  int $id Category ID
	 * @return boolean
	 */
	public static function checkCategory( $id )
	{
		$config = JCommentsFactory::getConfig();
		$categories = $config->get('enable_categories', '');
		$ids = explode(',', $categories);
		
		return ($categories == '*' || ($categories != '' && in_array($id, $ids)));
	}
}