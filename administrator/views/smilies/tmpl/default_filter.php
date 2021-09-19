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

$filterSearch = $this->escape($this->state->get('filter.search'));
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirection = $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();

?>
<?php if (!empty($this->bootstrap)): ?>
<div id="filter-bar" class="btn-toolbar">
	<div class="filter-search btn-group pull-left">
		<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>:</label>
		<input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_search" value="<?php echo $filterSearch; ?>" title="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>"/>
	</div>
	<div class="btn-group hidden-phone">
		<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
		<button class="btn tip hasTooltip" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
	</div>
	<div class="btn-group pull-right hidden-phone">
		<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
	<div class="btn-group pull-right hidden-phone">
		<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
		<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
			<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
			<option value="asc" <?php if ($listDirection == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
			<option value="desc" <?php if ($listDirection == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
		</select>
	</div>
	<div class="btn-group pull-right">
		<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
		<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
			<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
			<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
		</select>
	</div>
</div>
<div class="clearfix"></div>
<?php else: ?>
<fieldset id="filter-bar">
	<div class="filter-search fltlft">
		<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('A_FILTER'); ?>:</label>
		<input type="text" name="filter_search" id="filter_search" value="<?php echo $filterSearch; ?>" title="<?php echo JText::_('A_FILTER'); ?>"/>
		<button class="inputbox" type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		<button class="inputbox" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>
	<?php if (!empty($this->filter) != '') : ?>
	<div class="filter-select fltrt">
		<?php echo $this->filter; ?>
	</div>
	<?php endif; ?>
</fieldset>
<?php endif; ?>
