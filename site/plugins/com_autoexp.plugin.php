<?php
/**
 * JComments plugin for AutoEXP (www.feellove.eu)
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_autoexp extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$query = "'SELECT a.model_name, m.name AS mark "
			. "\n FROM #__autoexp_add AS a"
			. "\n LEFT JOIN #__autoexp_mark AS m ON m.id = a.mark_id"
			. "\n WHERE id = " . $id
			;
		$db->setQuery($query);

		$data = null;

		if (JCOMMENTS_JVERSION == '1.5') {
			$data = $db->loadObject();
		} else {
			$db->loadObject($data);
		}

		if ($data != null) {
			return empty($data->model_name) ? (isset($data->mark) ? $data->mark : '') : $data->model_name;
		} else {
			return '';
		}
	}

	function getObjectLink($id)
	{
		$_Itemid = self::getItemid( 'com_autoexp' );

		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT mark_id FROM #__autoexp_add WHERE id = ' . $id );
		$catid = $db->loadResult();

		$link = JRoute::_('index.php?option=com_autoexp&amp;page=show_adds&amp;catid='.$catid.'&amp;adid=' . $id . '&amp;Itemid=' . $_Itemid );
		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT user_id FROM #__autoexp_add WHERE id = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}
}