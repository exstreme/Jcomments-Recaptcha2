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
 * JComments plugin base class
 */
class JCommentsPlugin
{
	/**
	 * Return the title of an object by given identifier.
	 *
	 * @param int $id A object identifier.
	 * @return string Object title
	 */
	function getObjectTitle($id)
	{
		return JFactory::getApplication()->getCfg('sitename');
	}

	/**
	 * Return the URI to object by given identifier.
	 *
	 * @param int $id A object identifier.
	 * @return string URI of an object
	 */
	function getObjectLink($id)
	{
		return JURI::root(true);
	}

	/**
	 * Return identifier of the object owner.
	 *
	 * @param int $id A object identifier.
	 * @return int Identifier of the object owner, otherwise -1
	 */
	function getObjectOwner($id)
	{
		return -1;
	}

	public static function getItemid($object_group, $link = '')
	{
		static $cache = array();

		$key = 'jc_' . $object_group . '_itemid';

		if (!isset($cache[$key])) {
			$app = JFactory::getApplication('site');

			if (empty($link)) {
				$component = JComponentHelper::getComponent($object_group);
				if (isset($component->id)) {
					$item = $app->getMenu()->getItems('component_id', $component->id, true);
				} else {
					$item = null;
				}
			} else {
				$item = $app->getMenu()->getItems('link', $link, true);
			}

			$cache[$key] = ($item !== null) ? $item->id : 0;
			unset($items);
		}

		return $cache[$key];
	}
}