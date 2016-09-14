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

jimport('joomla.plugin.plugin');

/**
 * Provides button to insert {jcomments on} into content edit box
 */
class plgButtonJCommentsOn extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_editors-xtd_jcommentson', JPATH_ADMINISTRATOR);
	}

	function onDisplay($name)
	{
		$getContent = $this->_subject->getContent($name);
		$js = "
				function insertJCommentsOn(editor) {
					var content = $getContent
					if (content.match(/{jcomments on}/)) {
						return false;
					} else {
						jInsertEditorText('{jcomments on}', editor);
					}
				}
				";

		$document = JFactory::getDocument();
		$document->addScriptDeclaration($js);

		$button = new JObject();
		$button->set('class', 'btn');
		$button->set('modal', false);
		$button->set('onclick', 'insertJCommentsOn(\'' . $name . '\');return false;');
		$button->set('text', JText::_('PLG_EDITORS-XTD_JCOMMENTSON_BUTTON_JCOMMENTSON'));
		$button->set('name', 'blank');
		$button->set('link', '#');

		return $button;
	}
}