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
	if (task == 'smiley.cancel' || document.formvalidator.isValid(document.getElementById('smiley-form'))) {
		Joomla.submitform(task, document.getElementById('smiley-form'));
	}
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jcomments&view=smiley&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="smiley-form" class="form-validate form-horizontal">
	<?php if(!empty($this->bootstrap)): ?>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('A_SMILIES_SMILEY_DETAILS');?></a></li>
	</ul>
	<?php endif;?>
	<div class="tab-content">
		<div class="tab-pane active" id="general">
			<div class="row-fluid">
				<div class="span6">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('code'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('code'); ?>
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
							<?php echo $this->form->getLabel('image'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image'); ?>
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
							<?php echo $this->form->getLabel('ordering'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('ordering'); ?>
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