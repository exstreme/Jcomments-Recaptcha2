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
 * JComments ACL
 */
class JCommentsACL
{
	var $canDelete = 0;
	var $canDeleteOwn = 0;
	var $canDeleteForMyObject = 0;
	var $canEdit = 0;
	var $canEditOwn = 0;
	var $canEditForMyObject = 0;
	var $canPublish = 0;
	var $canPublishForMyObject = 0;
	var $canViewIP = 0;
	var $canViewEmail = 0;
	var $canViewHomepage = 0;
	var $canComment = 0;
	var $canQuote = 0;
	var $canReply = 0;
	var $canVote = 0;
	var $canReport = 0;
	var $canBan = 0;
	var $userID = 0;
	var $userIP = 0;
	var $deleteMode = 0;
	var $userBlocked = 0;

	function JCommentsACL()
	{
		$user = JFactory::getUser();
		$config = JCommentsFactory::getConfig();

		$this->canDelete = $this->check('can_delete');
		$this->canDeleteOwn = $this->check('can_delete_own');
		$this->canDeleteForMyObject = $this->check('can_delete_for_my_object');
		$this->canEdit = $this->check('can_edit');
		$this->canEditOwn = $this->check('can_edit_own');
		$this->canEditForMyObject = $this->check('can_edit_for_my_object');
		$this->canPublish = $this->check('can_publish');
		$this->canPublishForMyObject = $this->check('can_publish_for_my_object');
		$this->canViewIP = $this->check('can_view_ip');
		$this->canViewEmail = $this->check('can_view_email');
		$this->canViewHomepage = $this->check('can_view_homepage');
		$this->canComment = $this->check('can_comment');
		$this->canVote = $this->check('can_vote');
		$this->canReport = intval($this->check('can_report') && $config->getInt('enable_reports'));
		$this->canBan = 0;
		$this->canQuote = intval($this->canComment && $this->check('enable_bbcode_quote'));
		$this->canReply = intval($this->canComment && $this->check('can_reply') && $config->get('template_view') == 'tree');

		$this->userID = (int)$user->id;
		$this->userIP = $_SERVER['REMOTE_ADDR'];
		$this->userBlocked = 0;

		$this->deleteMode = $config->getInt('delete_mode');

		$this->commentsLocked = false;

		if ($config->getInt('enable_blacklist', 0) == 1) {
			$options = array();
			$options['ip'] = $this->getUserIP();
			$options['userid'] = $this->getUserID();
			if (!JCommentsSecurity::checkBlacklist($options)) {
				$this->userBlocked = 1;
				$this->canComment = 0;
				$this->canQuote = 0;
				$this->canReply = 0;
				$this->canVote = 0;
				$this->canBan = 0;
			} else {
				$this->canBan = $this->check('can_ban');
			}
		}
	}

	public static function check($str, $isName = true)
	{
		static $group = null;

		if ($isName) {
			$str = JCommentsFactory::getConfig()->get($str);
		}

		if (!empty($str)) {
			$user = JFactory::getUser();

			$list = explode(',', $str);

			if ($group === null) {
				if ($user->id) {
					$db = JFactory::getDbo();
					// get highest group
					$query = $db->getQuery(true);
					$query->select('a.id');
					$query->from('#__user_usergroup_map AS map');
					$query->leftJoin('#__usergroups AS a ON a.id = map.group_id');
					$query->where('map.user_id = ' . (int)$user->id);
					$query->order('a.lft desc');
					$db->setQuery($query, 0, 1);

					$group = $db->loadResult();
				} else {
					$group = JComponentHelper::getParams('com_users')->get('guest_usergroup', 1);
				}
			}

			if (in_array($group, $list)) {
				return 1;
			}
		}

		return 0;
	}

	function getUserIP()
	{
		return $this->userIP;
	}

	function getUserId()
	{
		return $this->userID;
	}

	function getUserBlocked()
	{
		return $this->userBlocked;
	}

	function getUserAccess()
	{
		static $access = null;

		if (!isset($access)) {
			$user = JFactory::getUser();
			$access = array_unique(JAccess::getAuthorisedViewLevels($user->get('id')));
			$access[] = 0; // for backward compatibility
		}

		return $access;
	}

	function isLocked($obj)
	{
		if (isset($obj) && ($obj != null)) {
			return ($obj->checked_out && $obj->checked_out != $this->userID) ? 1 : 0;
		}

		return 0;
	}

	function isDeleted($obj)
	{
		if (isset($obj) && ($obj != null)) {
			return $obj->deleted ? 1 : 0;
		}

		return 0;
	}

	function isObjectOwner($obj)
	{
		if (is_null($obj)) {
			return false;
		} else {
			$objectOwner = $this->userID ? JCommentsObjectHelper::getOwner($obj->object_id, $obj->object_group) : 0;

			return $this->userID ? ($this->userID == $objectOwner) : false;
		}
	}

	function canDelete($obj)
	{
		return (($this->canDelete || ($this->canDeleteForMyObject && $this->isObjectOwner($obj))
				|| ($this->canDeleteOwn && ($obj->userid == $this->userID)))
			&& (!$this->isLocked($obj)) && (!$this->isDeleted($obj) || $this->deleteMode == 0)) ? 1 : 0;
	}

	function canEdit($obj)
	{
		return (($this->canEdit || ($this->canEditForMyObject && $this->isObjectOwner($obj))
				|| ($this->canEditOwn && ($obj->userid == $this->userID)))
			&& (!$this->isLocked($obj)) && (!$this->isDeleted($obj))) ? 1 : 0;
	}

	function canPublish($obj = null)
	{
		return (($this->canPublish || ($this->canPublishForMyObject && $this->isObjectOwner($obj)))
			&& (!$this->isLocked($obj)) && (!$this->isDeleted($obj))) ? 1 : 0;
	}

	function canPublishForObject($object_id, $object_group)
	{
		return ($this->userID
			&& $this->canPublishForMyObject
			&& $this->userID == JCommentsObjectHelper::getOwner($object_id, $object_group)) ? 1 : 0;
	}

	function canViewIP($obj = null)
	{
		if (is_null($obj)) {
			return ($this->canViewIP) ? 1 : 0;
		} else {
			return ($this->canViewIP && ($obj->ip != '') && (!$this->isDeleted($obj))) ? 1 : 0;
		}
	}

	function canViewEmail($obj = null)
	{
		if (is_null($obj)) {
			return ($this->canViewEmail) ? 1 : 0;
		} else {
			return ($this->canViewEmail && ($obj->email != '')) ? 1 : 0;
		}
	}

	function canViewHomepage($obj = null)
	{
		if (is_null($obj)) {
			return ($this->canViewHomepage) ? 1 : 0;
		} else {
			return ($this->canViewHomepage && ($obj->homepage != '')) ? 1 : 0;
		}
	}

	function canComment()
	{
		return $this->canComment;
	}

	function canQuote($obj = null)
	{
		if (is_null($obj)) {
			return $this->canQuote && !$this->commentsLocked;
		} else {
			return ($this->canQuote && !$this->commentsLocked && (!isset($obj->_disable_quote)) && (!$this->isDeleted($obj))) ? 1 : 0;
		}
	}

	function canReply($obj = null)
	{
		if (is_null($obj)) {
			return $this->canReply && !$this->commentsLocked;
		} else {
			return ($this->canReply && !$this->commentsLocked && (!isset($obj->_disable_reply)) && (!$this->isDeleted($obj))) ? 1 : 0;
		}
	}

	function canVote($obj)
	{
		if ($this->userID) {
			return ($this->canVote && $obj->userid != $this->userID && !isset($obj->voted) && (!$this->isDeleted($obj)));
		} else {
			return ($this->canVote && $obj->ip != $this->userIP && !isset($obj->voted) && (!$this->isDeleted($obj)));
		}

	}

	function canReport($obj = null)
	{
		if (is_null($obj)) {
			return $this->canReport;
		} else {
			return ($this->canReport && (!isset($obj->_disable_report)) && (!$this->isDeleted($obj))) ? 1 : 0;
		}
	}

	function canModerate($obj)
	{
		return ($this->canEdit($obj) || $this->canDelete($obj)
			|| $this->canPublish($obj) || $this->canViewIP($obj) || $this->canBan($obj)) && (!$this->isDeleted($obj) || $this->deleteMode == 0);
	}

	function canBan($obj = null)
	{
		if (is_null($obj)) {
			return $this->canBan;
		} else {
			return ($this->canBan && (!$this->isDeleted($obj))) ? 1 : 0;
		}
	}

	function setCommentsLocked($value)
	{
		$this->commentsLocked = $value;

		//$this->canComment = $this->canComment && !$this->commentsLocked;
		$this->canQuote = $this->canQuote && !$this->commentsLocked;
		$this->canReply = $this->canReply && !$this->commentsLocked;
	}

	function isCommentsLocked()
	{
		return $this->commentsLocked;
	}
}