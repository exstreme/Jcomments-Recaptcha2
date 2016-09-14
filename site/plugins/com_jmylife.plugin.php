<?php
/**
 * JComments plugin for JMyLife support
 *
 * @version 2.1
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_jmylife extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( "SELECT CASE WHEN title IS NULL OR title = '' THEN SUBSTRING(`fulltext`, 1, 20) ELSE `title` END as title, id FROM #__jmylife_stories WHERE id = " . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT cat.`name` AS catname, cat.`id` AS catid, s.`title`, s.`user_alias` FROM `#__jmylife_categories` AS cat LEFT JOIN `#__jmylife_stories` AS s ON ( cat.`id` = s.`catid` ) WHERE s.`id` = '.$id.' GROUP BY cat.`id`' );

		$_Itemid = '';

		if($data = $db->loadObject()) {
			if( !defined( 'JPATH_JMYLIFE' ) ) define( 'JPATH_JMYLIFE', JPATH_ROOT.DS.'components'.DS.'com_jmylife' );
			if( !defined( 'JPATH_JMYLIFELIBS' ) ) define( 'JPATH_JMYLIFELIBS', JPATH_JMYLIFE.DS.'libraries' );
			if (is_file(JPATH_JMYLIFELIBS.DS.'common.php')) {
				require_once( JPATH_JMYLIFELIBS.DS.'common.php' );
				require_once( JPATH_JMYLIFELIBS.DS.'configuration.php' );
				$_Itemid = '&Itemid='.JmylifeCommon::getStoryItemid($data);
			}
		}
		$link = JRoute::_('index.php?option=com_jmylife&view=item&id='.$id.$_Itemid);
		return $link;
        }

	function getObjectOwner($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT user_id FROM #__jmylife_stories WHERE id = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}
}