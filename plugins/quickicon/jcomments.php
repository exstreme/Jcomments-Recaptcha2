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

class plgQuickiconJComments extends JPlugin
{
	public function onGetIcons($context)
	{
		if ($context == $this->params->get('context', 'mod_quickicon') 
		&& JFactory::getUser()->authorise('core.manage', 'com_jcomments')) {

			JFactory::getLanguage()->load('com_jcomments.sys', JPATH_ADMINISTRATOR, 'en-GB', true);
			$this->loadLanguage('com_jcomments.sys', JPATH_ADMINISTRATOR);

			$text = $this->params->get('displayedtext');
			if (empty($text)) {
				$text = JText::_('COM_JCOMMENTS');
			}

			if (version_compare(JVERSION, '3.0', '>')) {
				$image = 'comments';
			} else {
				$image = JURI::base().'components/com_jcomments/assets/images/icon-48-jcomments.png';
			}
			
			return array(
				array(
					'link' => 'index.php?option=com_jcomments',
					'image' => $image,
					'text' => $text,
					'access' => array('core.manage', 'com_jcomments'),
					'id' => 'plg_quickicon_jcomments'
					)
				);
		}
	}
}