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
<?php if (empty($this->bootstrap)): ?>
<fieldset id="filter-bar">
	<?php if (!empty($this->filter) != '') : ?>
	<div class="filter-select fltlft">
		<label for="lang"><?php echo JText::_('JFIELD_LANGUAGE_LABEL'); ?></label>
		<?php echo $this->filter; ?>
	</div>
	<?php endif; ?>
</fieldset>
<?php endif; ?>
