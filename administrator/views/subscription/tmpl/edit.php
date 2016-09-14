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
	if (task == 'subscription.cancel' || document.formvalidator.isValid(document.id('subscription-form'))) {
		Joomla.submitform(task, document.getElementById('subscription-form'));
	}
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jcomments&view=subscription&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="subscription-form" class="form-validate form-horizontal">
	<?php if(!empty($this->bootstrap)): ?>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#general" data-toggle="tab"><?php echo empty($this->item->id) ? JText::_('A_SUBSCRIPTION_NEW') : JText::sprintf('A_SUBSCRIPTION_EDIT', $this->item->id);?></a></li>
	</ul>
	<?php endif;?>
	<div class="tab-content">
		<div class="tab-pane active" id="general">
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('object_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('object_id'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('object_group'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('object_group'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('name'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('email'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('email'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('published'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('published'); ?>
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