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

require_once (JCOMMENTS_MODELS.'/object.php');
require_once (JCOMMENTS_CLASSES.'/objectinfo.php');

/**
 * JComments objects frontend helper
 */
class JCommentsObjectHelper
{
	/**
	 * Returns title for given object
	 *
	 * @param int $object_id
	 * @param string $object_group
	 * @param string $language 
	 * @return string
	 */
	public static function getTitle( $object_id, $object_group = 'com_content', $language = null )
	{
		$info = self::getObjectInfo($object_id, $object_group, $language);
		return $info->title;
	}
	
	/**
	 * Returns URI for given object
	 *
	 * @param int $object_id
	 * @param string $object_group
	 * @param string $language
	 * @return string
	 */
	public static function getLink( $object_id, $object_group = 'com_content', $language = null )
	{
		$info = self::getObjectInfo($object_id, $object_group, $language);
		return $info->link;
	}

	/**
	 * Returns identifier of user who is owner of an object
	 *
	 * @param int $object_id
	 * @param string $object_group
	 * @param string $language
	 * @return string
	 */
	public static function getOwner( $object_id, $object_group = 'com_content', $language = null )
	{
		$info = self::getObjectInfo($object_id, $object_group, $language);
		return $info->userid;
	}

	protected static function _call($class, $methodName, $args = array())
	{
		if (!is_callable(array($class, $methodName))) {
			$class = new JCommentsPlugin;
		}

		return call_user_func_array(array($class, $methodName), $args);
	}

	protected static function _loadObjectInfo($object_id, $object_group = 'com_content', $language = null)
	{
		static $plugins = array();
		$object_group = JCommentsSecurity::clearObjectGroup($object_group);

		// get object information via plugins
		if (!isset($plugins[$object_group])) {
			ob_start();
			include_once (JCOMMENTS_SITE.'/plugins/'.$object_group.'.plugin.php');
			ob_end_clean();

			$className = 'jc_' . $object_group;

			if (class_exists($className)) {
				$plugins[$object_group] = $className;
			} else {
				$plugins[$object_group] = 'JCommentsPlugin';
			}
		}

		$className = $plugins[$object_group];
		$class = new $className;

		if (is_callable(array($class, 'getObjectInfo'))) {
			// retrieve object information via getObjectInfo plugin's method
			$info = self::_call($class, 'getObjectInfo', array($object_id, $language));
		} else {
			// retrieve object information via separate plugin's methods (old plugins)
			$info = new JCommentsObjectInfo();

			$info->title = self::_call($class, 'getObjectTitle', array($object_id, $language));
			$info->link = self::_call($class, 'getObjectLink', array($object_id, $language));
			$info->userid = self::_call($class, 'getObjectOwner', array($object_id, $language));
		}

		$info->lang = $language;
		$info->object_id = $object_id;
		$info->object_group = $object_group;

		return $info;
	}

	public static function fetchObjectInfo($object_id, $object_group = 'com_content', $language = null)
	{
		$object = JCommentsModelObject::getObjectInfo($object_id, $object_group, $language);

		if ($object !== false) {
			// use object information stored in database
			$info = new JCommentsObjectInfo($object);
		} else {
			// get object information via plugins
			$info = self::_loadObjectInfo($object_id, $object_group, $language);
			if (!JCommentsModelObject::IsEmpty($info)) {
				$app = JFactory::getApplication();
				if (!$app->isAdmin()) {
					// insert object information
					JCommentsModelObject::setObjectInfo(0, $info);
				}
			}
		}

		return $info;
	}

	/**
	 * Returns object information
	 *
	 * @param int $object_id
	 * @param string $object_group
	 * @param string $language 
	 * @param boolean $useCache
	 * @return	JCommentsObjectInfo
	 */
	public static function getObjectInfo($object_id, $object_group = 'com_content', $language = null, $useCache = true)
	{
		static $info = array();

		if (empty($language)) {
			$language = JCommentsMultilingual::getLanguage();
		}

		$key = md5($object_group.'_'.$object_id.'_'.($language ? $language : ''));

		if (!isset($info[$key])) {
			if ($useCache) {
				$cache = JFactory::getCache('com_jcomments_objects_'.strtolower($object_group), 'callback');
				$info[$key] = $cache->get(array('JCommentsObjectHelper', 'fetchObjectInfo'), array($object_id, $object_group, $language));
			} else {
				$info[$key] = self::fetchObjectInfo($object_id, $object_group, $language);
			}
		}
		return $info[$key];
	}

	/**
	 * Stores object information (inserts new or updates existing)
	 *
	 * @param int $object_id
	 * @param string $object_group
	 * @param string $language 
	 * @param boolean $cleanCache
	 * @param boolean $allowEmpty
	 * @return JCommentsObjectInfo
	 */
	public static function storeObjectInfo($object_id, $object_group = 'com_content', $language = null, $cleanCache = false, $allowEmpty = false)
	{
		if (empty($language)) {
			$language = JCommentsMultilingual::getLanguage();
		}

		$app = JFactory::getApplication();

		// try to load object information from database
		$object = JCommentsModelObject::getObjectInfo($object_id, $object_group, $language);
		$objectId = $object === false ? 0 : $object->id;

		if ($objectId == 0 && $app->isAdmin()) {
			// return empty object because we can not create link in backend
			return new JCommentsObjectInfo();
		}

		// get object information via plugins
		$info = self::_loadObjectInfo($object_id, $object_group, $language);

		if (!JCommentsModelObject::IsEmpty($info) || $allowEmpty) {
			if ($app->isAdmin()) {
				// we do not have to update object's link from backend
				$info->link = null;
			}

			// insert/update object information
			JCommentsModelObject::setObjectInfo($objectId, $info);

			if ($cleanCache) {
				// clean cache for given object group
				$cache = JFactory::getCache('com_jcomments_objects_'.strtolower($object_group));
				$cache->clean();
			}
		}
		return $info;
	}
}