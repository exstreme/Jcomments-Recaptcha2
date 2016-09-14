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

$user = JFactory::getUser();
$userId = $user->get('id');
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

<form action="<?php echo JRoute::_('index.php?option=com_jcomments&view=blacklists'); ?>" method="post" name="adminForm"
	  id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php endif; ?>
	<div id="j-main-container" class="<?php echo $containerClass; ?>">
		<?php echo $this->loadTemplate('filter'); ?>

		<table class="adminlist table table-striped" cellspacing="1">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="30%" class="left nowrap">
					<?php echo JHTML::_('grid.sort', 'A_BLACKLIST_IP', 'jb.ip', $listDirection, $listOrder); ?>
				</th>
				<th width="30%" class="left">
					<?php echo JHTML::_('grid.sort', 'A_BLACKLIST_REASON', 'jb.reason', $listDirection, $listOrder); ?>
				</th>
				<th width="30%" class="left hidden-phone">
					<?php echo JHTML::_('grid.sort', 'A_BLACKLIST_NOTES', 'jb.notes', $listDirection, $listOrder); ?>
				</th>
				<th width="10%" class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'u.name', $listDirection, $listOrder); ?>
				</th>
				<th width="10%" class="center nowrap hidden-phone">
					<?php echo JHTML::_('grid.sort', 'JGLOBAL_CREATED_DATE', 'jb.created', $listDirection, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'jb.id', $listDirection, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$canEdit = $user->authorise('core.edit', 'com_jcomments');
				$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php echo JHTML::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="nowrap has-context">
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'subscriptions.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canEdit && $canCheckin) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_jcomments&task=blacklist.edit&id=' . (int)$item->id); ?>">
								<?php echo $this->escape($item->ip); ?></a>
						<?php else : ?>
							<?php echo $this->escape($item->ip); ?>
						<?php endif; ?>
					</td>
					<td class="left hidden-phone">
						<?php echo $item->reason; ?>
					</td>
					<td class="left hidden-phone">
						<?php echo $item->notes; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo $item->name; ?>
					</td>
					<td class="center nowrap">
						<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
					</td>
					<td class="center hidden-phone">
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirection; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</div>
</form>