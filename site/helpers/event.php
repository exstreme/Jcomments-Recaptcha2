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
use Joomla\CMS\Factory;
/**
 * JComments Event Helper
 */
class JCommentsEventHelper
{
	/**
	 * Triggers an event by dispatching arguments to all observers that handle
	 * the event and returning their return values.
	 *
	 * @param string $event The event name
	 * @param array $args An array of arguments
	 * @return array An array of results from each function call
	 */
	public static function trigger($event, $args = null)
	{
		static $initialised = false;

		$result = array();

		if (JCommentsFactory::getConfig()->getInt('enable_plugins') == 1) {
			if (!$initialised) {
				JPluginHelper::importPlugin('jcomments');
				$initialised = true;
			}

			if (version_compare(JVERSION, '4.0', 'lt')) {
				$dispatcher = JEventDispatcher::getInstance();
				if (is_array($args)) {
					$result = $dispatcher->trigger($event, $args);
				} else {
					$result = $dispatcher->trigger($event);
				}
			} else {
				if (is_array($args)) {
					$result = Factory::getApplication()->triggerEvent($event, $args);
				} else {
					$result = Factory::getApplication()->triggerEvent($event);
				}
			}
	

		}
		return $result;
	}
}