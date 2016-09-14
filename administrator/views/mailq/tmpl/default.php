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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirection = $this->escape($this->state->get('list.direction'));
$containerClass = empty($this->sidebar) ? '' : 'span10';

?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jcomments&view=mailq'); ?>" method="post" name="adminForm"
	  id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php endif; ?>
	<div id="j-main-container" class="<?php echo $containerClass; ?>">
		<?php echo $this->loadTemplate('filter'); ?>

		<div id="jc">

			<table class="adminlist table table-striped" id="articleList" cellspacing="1">
				<thead>
				<tr>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="20%" class="left nowrap">
						<?php echo JHTML::_('grid.sort', 'A_MAILQ_HEADING_NAME', 'name', $listDirection, $listOrder); ?>
					</th>
					<th width="20%" class="left">
						<?php echo JHTML::_('grid.sort', 'A_MAILQ_HEADING_EMAIL', 'email', $listDirection, $listOrder); ?>
					</th>
					<th width="45%" class="center hidden-phone">
						<?php echo JHTML::_('grid.sort', 'A_MAILQ_HEADING_SUBJECT', 'subject', $listDirection, $listOrder); ?>
					</th>
					<th width="5%" class="center nowrap hidden-phone">
						<?php echo JHTML::_('grid.sort', 'A_MAILQ_HEADING_PRIORITY', 'priority', $listDirection, $listOrder); ?>
					</th>
					<th width="5%" class="center nowrap hidden-phone">
						<?php echo JHTML::_('grid.sort', 'A_MAILQ_HEADING_ATTEMPTS', 'attempts', $listDirection, $listOrder); ?>
					</th>
					<th width="5%" class="center nowrap hidden-phone">
						<?php echo JHTML::_('grid.sort', 'A_MAILQ_HEADING_CREATED', 'created', $listDirection, $listOrder); ?>
					</th>

					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirection, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="8">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php if ($this->items): ?>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center hidden-phone">
								<?php echo JHTML::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="nowrap has-context">
								<?php echo $this->escape($item->name); ?>
							</td>
							<td class="left hidden-phone">
								<?php echo $this->escape($item->email); ?>
							</td>
							<td class="left hidden-phone">
								<?php echo $this->escape($item->subject); ?>
							</td>
							<td class="left hidden-phone">
								<?php echo $item->priority; ?>
							</td>
							<td class="left hidden-phone">
								<?php echo $item->attempts; ?>
							</td>
							<td class="left hidden-phone">
								<?php echo $item->created; ?>
							</td>
							<td class="center hidden-phone">
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="8" class="center">
							<?php echo JText::_('A_MAILQ_NO_PENDING_EMAILS_YET'); ?>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirection; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</div>
</form>