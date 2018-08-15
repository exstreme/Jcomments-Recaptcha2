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

class jtt_tpl_report_form extends JoomlaTuneTemplate
{
	function render() 
	{
?>
<h4><?php echo JText::_('REPORT_TO_ADMINISTRATOR'); ?></h4>
<form id="comments-report-form" name="comments-report-form" action="javascript:void(null);">
<?php
		if ($this->getVar('isGuest', 1) == 1) {
?>
<p>
	<span>
		<input id="comments-report-form-name" type="text" name="name" value="" maxlength="255" size="22" />
		<label for="comments-report-form-name"><?php echo JText::_('REPORT_NAME'); ?></label>
	</span>
</p>
<?php
		}
?>
<p>
	<span>
		<input id="comments-report-form-reason" type="text" name="reason" value="" maxlength="255" size="22" />
		<label for="comments-report-form-reason"><?php echo JText::_('REPORT_REASON'); ?></label>
	</span>
</p>
<div id="comments-report-form-buttons">
	<div class="btn"><div><a href="#" onclick="jcomments.saveReport();return false;" title="<?php echo JText::_('REPORT_SUBMIT'); ?>"><?php echo JText::_('REPORT_SUBMIT'); ?></a></div></div>
	<div class="btn"><div><a href="#" onclick="jcomments.cancelReport();return false;" title="<?php echo JText::_('REPORT_CANCEL'); ?>"><?php echo JText::_('REPORT_CANCEL'); ?></a></div></div>
	<div style="clear:both;"></div>
</div>
<input type="hidden" name="commentid" value="<?php echo $this->getVar('comment-id'); ?>" />
</form>
<?php
	}
}