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
Joomla.submitbutton = function(task)
{
	if (task == 'blacklist.cancel' || document.formvalidator.isValid(document.getElementById('blacklist-form'))) {
		Joomla.submitform(task, document.getElementById('blacklist-form'));
	}
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jcomments&view=blacklist&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="blacklist-form" class="form-validate form-horizontal">
	<?php if(!empty($this->bootstrap)): ?>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#general" data-toggle="tab"><?php echo empty($this->item->id) ? JText::_('A_BLACKLIST_EDIT') : JText::sprintf('A_BLACKLIST_EDIT', $this->item->id);?></a></li>
	</ul>
	<?php endif;?>
	<div class="tab-content">
		<div class="tab-pane active" id="general">
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('ip'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('ip'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('reason'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('reason'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('notes'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('notes'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('created'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('created'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('id'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>