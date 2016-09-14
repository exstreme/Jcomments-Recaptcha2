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
 * Joomla plugins helper
 */
class JCommentsPluginHelper
{
	/**
	 * Gets the parameter object for a plugin
	 *
	 * @param string $pluginName The plugin name
	 * @param string $type The plugin type, relates to the sub-directory in the plugins directory
	 * @return JParameter A JParameter object
	 */
	public static function getParams($pluginName, $type = 'content')
	{
		$plugin	= JPluginHelper::getPlugin($type, $pluginName);
 		if (is_object($plugin)) {
			$pluginParams = new JRegistry($plugin->params);
		} else {
			$pluginParams = new JRegistry('');
		}
		return $pluginParams;
	}
}