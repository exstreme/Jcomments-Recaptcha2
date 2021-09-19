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
		if (task == 'blacklist.cancel' || document.formvalidator.isValid(document.getElementById('comment-form'))) {
			Joomla.submitform(task, document.getElementById('comment-form'));
		}
	};
	<?php if (count($this->reports)): ?>
	(function ($) {
		$(document).ready(function () {
			$('#reports').find('table td a').bind('click', function () {
				var id = $(this).data('report-id');
				var row = $(this).closest('tr');
				if (id) {
					$.post('index.php?option=com_jcomments&task=comment.deleteReportAjax&tmpl=component', {id: id})
						.done(function (result) {
							if (result == 0) {
								location.reload();
							} else if (result > 0) {
								if (row) {
									row.remove();
								}
							}
						});
				}
			});
		});
	})(jQuery);
	<?php endif;?>
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_jcomments&view=comment&layout=edit&id=' . (int)$this->item->id); ?>"
	method="post" name="adminForm" id="comment-form" class="form-validate form-horizontal">
	<?php if (count($this->reports)): ?>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('A_COMMENT_EDIT'); ?></a></li>
			<li><a href="#reports" data-toggle="tab"><?php echo JText::_('A_REPORTS_LIST'); ?></a></li>
		</ul>
	<?php endif; ?>
	<div class="tab-content">
		<div class="tab-pane active" id="general">
			<div class="row-fluid">
				<div class="span6">
					<?php if (!empty($this->item->title)): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title'); ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('comment'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('comment'); ?>
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('userid'); ?>
						</div>
						<div class="controls">
							<?php if ($this->item->userid): ?>
								<?php echo $this->form->getInput('userid'); ?>
							<?php else: ?>
								<?php echo $this->form->getInput('name'); ?>
							<?php endif; ?>
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

					<?php if (!empty($this->item->homepage)): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('homepage'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('homepage'); ?>
							</div>
						</div>
					<?php endif; ?>

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
							<?php echo $this->form->getLabel('date'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('date'); ?>
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

		<?php if (count($this->reports)): ?>
			<div class="tab-pane" id="reports">
				<table class="adminlist table table-striped" cellspacing="1">
					<thead>
					<tr>
						<th width="5">#</th>
						<th width="60%"><?php echo JText::_('A_REPORTS_REPORT_REASON'); ?></th>
						<th width="20%"><?php echo JText::_('A_REPORTS_REPORT_NAME'); ?></th>
						<th width="20%"><?php echo JText::_('A_REPORTS_REPORT_DATE'); ?></th>
						<th></th>
					</tr>
					</thead>
					<tbody>
					<?php
					$i = 1;
					foreach ($this->reports as $report) {
						?>
						<tr>
							<td>
								<?php echo $i; ?>
							</td>
							<td>
								<?php echo $report->reason; ?>
							</td>
							<td>
								<?php echo $report->name; ?><br/ ><?php echo $report->ip; ?>
							</td>
							<td>
								<?php echo JHtml::_('date', $report->date, 'Y-m-d H:i:s'); ?>
							</td>
							<td>
								<a title="<?php echo JText::_('A_REPORTS_REMOVE_REPORT'); ?>" href="#"
								   data-report-id="<?php echo $report->id; ?>">
									<i class="icon-remove"></i>
								</a>
							</td>
						</tr>
						<?php
						$i++;
					}
					?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>