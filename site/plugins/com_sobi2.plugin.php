<?php
/**
 * JComments plugin for SOBI2 objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_sobi2 extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$query = "SELECT i.itemid as id, i.title, i.owner"
			. " FROM #__sobi2_item as i"
			. " WHERE i.itemid = " . $id;
		$db->setQuery($query);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$configFile = JPATH_SITE.'/components/com_sobi2/config.class.php';
			if (is_file($configFile)) {
				require_once($configFile);

				$config = sobi2Config::getInstance();

				$info->title = self::getSobiStr($row->title);
				$info->access = NULL;
				$info->userid = $row->owner;
				$info->link = sobi2Config::sef('index.php?option=com_sobi2&amp;sobi2Task=sobi2Details&amp;sobi2Id=' . $id . '&amp;Itemid=' . $config->sobi2Itemid);
			}
		}

		return $info;
	}

	/**
	 * reversing MySQL injection filter
	 *
	 * @param string $string - string to decode
	 * @return string
	 */
	protected static function getSobiStr( $string )
	{
		if( $string ) {
			$iso = defined("_ISO") ? explode( '=', _ISO ) : array( null, "UTF-8");
			if(strtoupper($iso[1]) != "UTF-8" ) {
				$string = stripslashes(stripslashes(html_entity_decode($string, ENT_COMPAT, 'UTF-8')));
			}
			else {
				$string = stripslashes(stripslashes($string));
			}
			if( !strstr( "<script", $string  ) ) {
				$string = str_replace( "& ", "&amp; ", $string );
			}
		}
		return  $string;
	}
}