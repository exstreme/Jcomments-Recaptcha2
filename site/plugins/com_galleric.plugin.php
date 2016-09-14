<?php
/**
 * JComments plugin for Galleric component
 *
 * @version 2.3
 * @package JComments
 * @author Constantine Poltyrev (shprota@gmail.com)
 * @copyright (C) 2012 by Constantine Poltyrev (http://shprota.rallycars.ru)
 * @copyright (C) 2012-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_galleric extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$db = JFactory::getDBO();
		$db->setQuery('SELECT name, alias FROM #__galleric_category WHERE id = ' . $id);
		$row = $db->loadObject();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_galleric');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->name;
			$info->link = JRoute::_('index.php?option=com_galleric&view=galleric&layout=lightbox&catid=' . $id . ':' . JApplication::stringURLSafe($row->alias) . $Itemid);
		}

		return $info;
	}
}