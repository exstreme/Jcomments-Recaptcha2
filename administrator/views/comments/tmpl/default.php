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

use Joomla\String\StringHelper;

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

<form action="<?php echo JRoute::_('index.php?option=com_jcomments&view=comments'); ?>" method="post" name="adminForm"
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
				<th width="1%" class="center nowrap">
					<?php echo JHTML::_('grid.sort', 'JSTATUS', 'jc.published', $listDirection, $listOrder); ?>
				</th>
				<th width="30%" class="left nowrap">
					<?php echo JHTML::_('grid.sort', 'A_COMMENT_TITLE', 'jc.title', $listDirection, $listOrder); ?>
				</th>
				<th width="10%" class="left hidden-phone">
					<?php echo JHTML::_('grid.sort', 'A_COMMENT_NAME', 'jc.name', $listDirection, $listOrder); ?>
				</th>
				<th width="10%" class="left hidden-phone">
					<?php echo JHTML::_('grid.sort', 'A_COMPONENT', 'jc.object_group', $listDirection, $listOrder); ?>
				</th>
				<th width="20%" class="left hidden-phone">
					<?php echo JHTML::_('grid.sort', 'A_COMMENT_OBJECT_TITLE', 'jo.title', $listDirection, $listOrder); ?>
				</th>
				<th width="10%" class="left hidden-phone">
					<?php echo JHTML::_('grid.sort', 'A_COMMENT_DATE', 'jc.date', $listDirection, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'jc.id', $listDirection, $listOrder); ?>
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
			<?php foreach ($this->items as $i => $item) :
				$canEdit = $user->authorise('core.edit', 'com_jcomments');
				$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canChange = $user->authorise('core.edit.state', 'com_jcomments') && $canCheckin;

				$title = $item->title;

				if (empty($title)) {
					if ($this->state->get('config.comment_title', 0) == 1 && !empty($item->object_title)) {
						$title = JText::_('A_COMMENT_TITLE_RE') . ' ' . $item->object_title;
					} else {
						$title = JCommentsText::cleanText(strip_tags($item->comment));
						$title = StringHelper::substr($title, 0, 200);
					}
				}
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php echo JHTML::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'comments.', $canChange); ?>
					</td>
					<td class="left has-context">
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'comments.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canEdit && $canCheckin) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_jcomments&task=comment.edit&id=' . (int)$item->id); ?>">
								<?php echo $this->escape($title); ?></a>
						<?php else : ?>
							<?php echo $this->escape($title); ?>
						<?php endif; ?>
					</td>
					<td class="left hidden-phone">
						<?php echo $item->name; ?><br />
						<?php echo $item->ip; ?>
					</td>
					<td class="left hidden-phone">
						<?php echo $item->object_group; ?>
					</td>
					<td class="left hidden-phone">
						<?php if (isset($item->object_link)) : ?>
							<a href="<?php echo $item->object_link; ?>"
							   title="<?php echo htmlspecialchars($item->object_title); ?>"
							   target="_blank"><?php echo $item->object_title; ?></a>
						<?php else: ?>
							<?php echo $item->object_title; ?>
						<?php endif; ?>
					</td>
					<td class="left hidden-phone">
						<?php echo JHtml::_('date', $item->date, 'Y-m-d H:i:s'); ?>
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