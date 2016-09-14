<?php
/**
 * JComments plugin for RSEvents!PRO objects support
 *
 * @version 1.0
 * @package JComments
 * @author Webcanyon (www.webcanyon.be) - based on work of Oregon
 * @copyright (C) 2014 by Webcanyon
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 
 */

defined('_JEXEC') or die;

class jc_com_rseventspro extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, name, owner FROM #__rseventspro_eventsWHERE id = ' . $id);
		$row = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($row)) {
			$Itemid = self::getItemid('com_rseventspro');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$info->title = $row->name;
			$info->userid = $row->owner;
			$info->link = JRoute::_('index.php?option=com_rseventspro&view=rseventspro&layout=show&id='.$row->id);
		}

		return $info;
	}
}
