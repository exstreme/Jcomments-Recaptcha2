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
$containerClass = empty( $this->sidebar) ? '' : 'span10';

$canOrder	= $user->authorise('core.edit.state', 'com_jcomments');
$saveOrder	= $listOrder == 'js.ordering';

if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_jcomments&task=smilies.saveOrderAjax&tmpl=component';
	if (version_compare(JVERSION, '3.0', 'ge')) {
		JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirection), $saveOrderingUrl);
	}
}
// $sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_jcomments&view=smilies'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
<?php endif;?>
    <div id="j-main-container" class="<?php echo $containerClass;?>">
		<?php echo $this->loadTemplate('filter'); ?>

		<div id="jc">

		<table class="adminlist table table-striped" id="articleList" cellspacing="1">
		<thead>
		<tr>
			<?php if (!empty($this->bootstrap)): ?>
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'js.ordering', $listDirection, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
				</th>
			<?php endif; ?>
			<th width="1%" class="hidden-phone">
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
			</th>
			<th width="1%" class="center nowrap">
				<?php echo JHtml::_('grid.sort', 'JSTATUS', 'js.published', $listDirection, $listOrder); ?>
			</th>
			<th width="50%" class="left nowrap">
				<?php echo JHTML::_( 'grid.sort', 'A_SMILIES_HEADING_NAME', 'js.name', $listDirection, $listOrder); ?>
			</th>
			<th width="40%" class="left">
				<?php echo JHTML::_( 'grid.sort', 'A_SMILIES_HEADING_CODE', 'js.code', $listDirection, $listOrder); ?>
			</th>
			<th width="1%" class="center hidden-phone">
				<?php echo JText::_('A_SMILIES_HEADING_IMAGE'); ?>
			</th>
			<?php if (empty($this->bootstrap)): ?>
			<th width="10%" class="center nowrap">
				<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'js.ordering', $listDirection, $listOrder); ?>
				<?php if ($canOrder && $saveOrder): ?>
					<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'smilies.saveorder'); ?>
				<?php endif;?>
			</th>
			<?php endif; ?>
			<th width="1%" class="nowrap hidden-phone">
				<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'js.id', $listDirection, $listOrder); ?>
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
			<?php foreach($this->items as $i => $item) :
				$canEdit = $user->authorise('core.edit', 'com_jcomments');
				$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canChange = $user->authorise('core.edit.state', 'com_jcomments') && $canCheckin;
				?>
			<tr class="row<?php echo $i % 2; ?>">
				<?php if (!empty($this->bootstrap)): ?>
				<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';
						if (!$saveOrder) :
							$disabledLabel    = JText::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
				</td>
				<?php endif; ?>

				<td class="center hidden-phone">
					<?php echo JHTML::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'smilies.', $canChange); ?>
				</td>
				<td class="nowrap has-context">
					<?php if ($item->checked_out) : ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'smilies.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit && $canCheckin) : ?>
                    <a href="<?php echo JRoute::_('index.php?option=com_jcomments&task=smiley.edit&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->name); ?></a>
					<?php else : ?>
					<?php echo $this->escape($item->name); ?>
					<?php endif; ?>
				</td>
				<td class="left hidden-phone">
					<?php echo $item->code; ?>
				</td>
				<td class="center hidden-phone">
					<?php echo JHtml::image($this->liveSmiliesPath . $item->image, ''); ?>
				</td>
				<?php if (empty($this->bootstrap)): ?>
				<td class="left hidden-phone order">
					<?php if ($canChange) : ?>
						<?php if ($saveOrder) : ?>
							<?php if ($listDirection == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, true, 'smilies.orderup', 'JLIB_HTML_MOVE_UP', $saveOrder); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'smilies.orderdown', 'JLIB_HTML_MOVE_DOWN', $saveOrder); ?></span>
							<?php elseif ($listDirection == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, true, 'smilies.orderdown', 'JLIB_HTML_MOVE_UP', $saveOrder); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'smilies.orderup', 'JLIB_HTML_MOVE_DOWN', $saveOrder); ?></span>
							<?php endif; ?>
						<?php endif; ?>
						<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled; ?> class="text-area-order" />
					<?php else : ?>
						<?php echo $item->ordering; ?>
					<?php endif; ?>
				</td>
				<?php endif; ?>
				<td class="center hidden-phone">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		</table>
			</div>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirection; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</div>
</form>