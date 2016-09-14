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
<?php if (count($this->groups)): ?>
	<div id="permissions-sliders" class="tabbable tabs-left">
		<ul class="nav nav-tabs">
			<?php foreach ($this->groups as $group):
				$active = ($group->value == 1) ? "active" : "";
				?>

				<li class="<?php echo $active; ?>">
					<a href="#permission-<?php echo $group->value; ?>" data-toggle="tab">
						<?php echo str_repeat('<span class="level">&ndash; ', $curLevel = $group->level) . $group->text; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="tab-content">
			<?php foreach ($this->groups as $group):
				$active = ($group->value == 1) ? "active" : "";
				$form = $this->permissionForms[$group->value];
				?>
				<div class="tab-pane <?php echo $active; ?>" id="permission-<?php echo $group->value; ?>">
					<div class="row-fluid">
						<div class="span4">
							<fieldset class="form-horizontal">
								<legend><?php echo JText::_('A_RIGHTS_POST'); ?></legend>
								<?php foreach ($form->getFieldset('post') as $field) : ?>
									<div class="control-group">
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</fieldset>
							<fieldset class="form-horizontal">
								<legend><?php echo JText::_('A_RIGHTS_MISC'); ?></legend>
								<?php foreach ($form->getFieldset('features') as $field) : ?>
									<div class="control-group">
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</fieldset>
						</div>

						<div class="span4">
							<fieldset class="form-horizontal">
								<legend><?php echo JText::_('A_RIGHTS_ADMINISTRATION'); ?></legend>
								<?php foreach ($form->getFieldset('administration') as $field) : ?>
									<div class="control-group">
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</fieldset>

						</div>

						<div class="span4">
							<fieldset class="form-horizontal">
								<legend><?php echo JText::_('A_RIGHTS_BBCODE'); ?></legend>
								<?php foreach ($form->getFieldset('bbcodes') as $field) : ?>
									<div class="control-group">
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</fieldset>

							<fieldset class="form-horizontal">
								<legend><?php echo JText::_('A_RIGHTS_view'); ?></legend>
								<?php foreach ($form->getFieldset('view') as $field) : ?>
									<div class="control-group">
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</fieldset>

						</div>

					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>

