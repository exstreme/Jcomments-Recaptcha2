<?php
/**
 * JComments plugin for CommunityBuilder profiles support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_comprofiler extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$db = JFactory::getDBO();

		$query = 'SELECT u.id, u.name, u.username, cb.firstname, cb.lastname, cb.middlename'
			. ' FROM #__users AS u '
			. ' JOIN #__comprofiler AS cb ON cb.user_id = u.id '
			. ' WHERE u.id = ' . intval($id)
			;

		$db->setQuery( $query );
		$user = $db->loadObject();

		$info = new JCommentsObjectInfo();

		if (!empty($user)) {
			$Itemid = self::getItemid('com_comprofiler', 'index.php?option=com_comprofiler');
			$Itemid = $Itemid > 0 ? '&Itemid='.$Itemid : '';

			$title = trim(preg_replace('#\s+#', ' ', $user->lastname . ' ' . $user->firstname . ' ' . $user->middlename));
			if ($title == '') {
				$title = $user->name;
			}

			$info->title = $title;
			$info->userid = $user->id;
			$info->link = JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $user->id . $Itemid);
		}

		return $info;
	}
}