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
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'custombbcode.cancel' || document.formvalidator.isValid(document.getElementById('custombbcode-form'))) {
			Joomla.submitform(task, document.getElementById('custombbcode-form'));
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_jcomments&view=custombbcode&layout=edit&id=' . (int)$this->item->id); ?>"
	method="post" name="adminForm" id="custombbcode-form" class="form-validate form-horizontal">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('A_CUSTOM_BBCODE_EDIT'); ?></a></li>
		<li><a href="#simple" data-toggle="tab"><?php echo JText::_('A_CUSTOM_BBCODE_SIMPLE'); ?></a></li>
		<li><a href="#advanced" data-toggle="tab"><?php echo JText::_('A_CUSTOM_BBCODE_ADVANCED'); ?></a></li>
		<li><a href="#button" data-toggle="tab"><?php echo JText::_('A_CUSTOM_BBCODE_BUTTON'); ?></a></li>
		<li><a href="#permissions" data-toggle="tab"><?php echo JText::_('A_CUSTOM_BBCODE_PERMISSIONS'); ?></a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="general">
			<?php echo $this->loadTemplate('general'); ?>
		</div>
		<div class="tab-pane" id="simple">
			<?php echo $this->loadTemplate('simple'); ?>
		</div>
		<div class="tab-pane" id="advanced">
			<?php echo $this->loadTemplate('advanced'); ?>
		</div>
		<div class="tab-pane" id="button">
			<?php echo $this->loadTemplate('button'); ?>
		</div>
		<div class="tab-pane" id="permissions">
			<?php echo $this->loadTemplate('permissions'); ?>
		</div>
	</div>
	<div>
		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>