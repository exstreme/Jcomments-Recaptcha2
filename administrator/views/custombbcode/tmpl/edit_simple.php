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
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('simple_pattern'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('simple_pattern'); ?>
	</div>
	<div class="controls">
		<?php echo JHtml::_('custombbcodes.sample', '[highlight={SIMPLETEXT1}]{SIMPLETEXT2}[/highlight]'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('simple_replacement_html'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('simple_replacement_html'); ?>
	</div>
	<div class="controls">
		<?php echo JHtml::_('custombbcodes.sample', '&lt;span style="color: {SIMPLETEXT1};"&gt;{SIMPLETEXT2}&lt;/span&gt;'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('simple_replacement_text'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('simple_replacement_text'); ?>
	</div>
	<div class="controls">
		<?php echo JHtml::_('custombbcodes.sample', '{SIMPLETEXT2}'); ?>
	</div>
</div>
