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

/**
 * Main template for JComments. Don't change it without serious reasons ;)
 * Then creating new template you can copy this file to new template's dir without changes
 */
class jtt_tpl_index extends JoomlaTuneTemplate
{
	function render() 
	{
		$object_id = $this->getVar('comment-object_id');
		$object_group = $this->getVar('comment-object_group');

		// comments data is prepared in tpl_list and tpl_comments templates 
		$comments = $this->getVar('comments-list', '');

		// form data is prepared in tpl_form template.
		$form = $this->getVar('comments-form');

		if ($comments != '' || $form != '' || $this->getVar('comments-anticache')) {
			// include comments css (only if we are in administor's panel)
			if ($this->getVar('comments-css', 0) == 1) {
				include_once (JCOMMENTS_HELPERS.'/system.php');
?>
<link href="<?php echo JCommentsSystemPluginHelper::getCSS(); ?>" rel="stylesheet" type="text/css" />
<?php
				if ($this->getVar('direction') == 'rtl') {
					$rtlCSS = JCommentsSystemPluginHelper::getCSS(true);
					if ($rtlCSS != '') {
?>
<link href="<?php echo $rtlCSS; ?>" rel="stylesheet" type="text/css" />
<?php
					}
				}
			}

			// include JComments JavaScript initialization
?>
<?php $script="
<!--
var jcomments=new JComments($object_id, '$object_group','".$this->getVar('ajaxurl')."');
jcomments.setList('comments-list');
//-->";
//</script>
            JFactory::getDocument()->addScriptDeclaration($script); ?>
<?php
			// IMPORTANT: Do not rename this div's id! Some JavaScript functions references to it!
?>
<div id="jc">
<?php
			if ($this->getVar('comments-form-position', 0) == 1) {
				// Display comments form (or link to show form)
				if (isset($form)) {
					echo $form;
				}
			}
?>
<div id="comments"><?php echo $comments; ?></div>
<?php
			if ($this->getVar('comments-form-position', 0) == 0) {
				// Display comments form (or link to show form)
				if (isset($form)) {
					echo $form;
				}
			}
?>
<div id="comments-footer" align="center"><?php echo $this->getVar('support'); ?></div>
<?php
			// Some magic like dynamic comments list loader (anticache) and auto go to anchor script
			$aca = (int) ($this->getVar('comments-gotocomment') == 1);
			$acp = (int) ($this->getVar('comments-anticache') == 1);
			$acf = (int) (($this->getVar('comments-form-link') == 1) && ($this->getVar('comments-form-locked', 0) == 0));

			if ($aca || $acp || $acf) {
?>
<?php
      $script="
<!--
jcomments.setAntiCache($aca,$acp,$acf);
//-->
";
JFactory::getDocument()->addScriptDeclaration($script); ?>
<?php
			}
?>
</div>
<?php
		}
	}
}