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

$containerClass = empty($this->sidebar) ? '' : 'span10';

?>
<script type="text/javascript">
	jQuery(document).ready(function (val) {
		function toggleReports(val) {
			var controls = ['jform[reports_per_comment]', 'jform[reports_before_unpublish]', 'jform[report_reason_required]'];
			var checked = jQuery('input[name="jform[enable_reports]"]:radio:checked').first();
			if (checked) {
				val = checked.val();
			}
			jQuery.each(controls, function () {
				jQuery('input[name="' + this + '"]').each(function () {
					var group = jQuery(this).closest('.control-group');
					if (group) {
						if (val == 1) {
							group.show();
						} else {
							group.hide();
						}
					}
				});
			});
		}

		function toggleNotifications(val) {
			var controls = ['jform[notification_type][]', 'jform[notification_email]', 'jform[enable_quick_moderation]'];
			var checked = jQuery('input[name="jform[enable_notification]"]:radio:checked').first();
			if (checked) {
				val = checked.val();
			}
			jQuery.each(controls, function () {
				jQuery('[name="' + this + '"]').each(function () {
					var group = jQuery(this).closest('.control-group');
					if (group) {
						if (val == 1) {
							group.show();
						} else {
							group.hide();
						}
					}
				});
			});
		}

		function toggleListLayout(val) {
			var controls = {};
			controls['tree'] = ['jform[comments_tree_order]'];
			controls['list'] = ['jform[comments_list_order]', 'jform[comments_per_page]', 'jform[comments_page_limit]', 'jform[comments_pagination]'];

			var selected = jQuery('select[name="jform[template_view]"]').find(':selected').first();
			if (selected) {
				val = selected.val();
			}

			jQuery.each(controls, function (key, values) {
				jQuery.each(values, function () {
					jQuery('[name="' + this + '"]').each(function () {
						var group = jQuery(this).closest('.control-group');
						if (group) {
							if (key == val) {
								group.show();
							} else {
								group.hide();
							}
						}
					});
				});
			});
		}

		jQuery('input[name="jform[enable_reports]"]:radio').click(function () {
			toggleReports(jQuery(this).val());
		});

		jQuery('input[name="jform[enable_notification]"]:radio').click(function () {
			toggleNotifications(jQuery(this).val());
		});

		jQuery('select[name="jform[template_view]"]').change(function () {
			toggleListLayout(jQuery(this).val());
		});

		toggleReports();
		toggleNotifications();
		toggleListLayout();

		Joomla.submitbutton = function (task) {
			if (task == 'settings.cancel') {
				Joomla.submitform(task, document.getElementById('settings-form'));
			} else if (document.formvalidator.isValid(document.id('settings-form'))) {
				var base64 = '';
				try {
					var query = [];
					jQuery('#settings-form').find(':input').each(function () {
						if (this.name) {
							if (this.type && ('radio' == this.type || 'checkbox' == this.type) && false === this.checked) {
								return;
							}

							var val = jQuery(this).val();
							if (!('undefined' == val || null == val)) {
							} else {
								return;
							}

							query.push(this.name + '=' + encodeURIComponent(val));
						}
					});

					if (query.length > 0) {
						query = query.join('&').replace(/\r\n/g, "\n");

						var a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
						var o = '', u = '', i = 0, chr1, chr2, chr3, enc1, enc2, enc3, enc4;

						for (var n = 0; n < query.length; n++) {
							var c = query.charCodeAt(n);
							if (c < 128) {
								u += String.fromCharCode(c);
							} else if ((c > 127) && (c < 2048)) {
								u += String.fromCharCode((c >> 6) | 192);
								u += String.fromCharCode((c & 63) | 128);
							} else {
								u += String.fromCharCode((c >> 12) | 224);
								u += String.fromCharCode(((c >> 6) & 63) | 128);
								u += String.fromCharCode((c & 63) | 128);
							}
						}
						while (i < u.length) {
							chr1 = u.charCodeAt(i++);
							chr2 = u.charCodeAt(i++);
							chr3 = u.charCodeAt(i++);
							enc1 = chr1 >> 2;
							enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
							enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
							enc4 = chr3 & 63;
							if (isNaN(chr2)) {
								enc3 = enc4 = 64;
							} else if (isNaN(chr3)) {
								enc4 = 64;
							}
							o = o + a.charAt(enc1) + a.charAt(enc2) + a.charAt(enc3) + a.charAt(enc4);
						}
						base64 = o;
					}
				} catch (e) {
				}

				if (base64 != '') {
					var form = jQuery('#settings-save-form');
					if (form) {
						form.find("#base64").val(base64);
						form.submit();

						return;
					}
				}

				Joomla.submitform(task, document.getElementById('settings-form'));
			}
		}

		// Render multi-lingual settings help.
		Joomla.renderMessages({'notice': ['<?php echo JText::_('A_SETTINGS_MULTILANG_DESC', true); ?>']});
	});
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_jcomments&view=settings'); ?>"
	method="post" name="adminForm" id="settings-form" class="form-validate form-horizontal">
	<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
	<?php endif; ?>
	<div id="j-main-container" class="<?php echo $containerClass; ?>">
		<?php echo $this->loadTemplate('filter'); ?>

		<ul class="nav nav-tabs">
			<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('A_COMMON'); ?></a></li>
			<li><a href="#layout" data-toggle="tab"><?php echo JText::_('A_LAYOUT'); ?></a></li>
			<li><a href="#permissions" data-toggle="tab"><?php echo JText::_('A_RIGHTS'); ?></a></li>
			<li><a href="#restrictions" data-toggle="tab"><?php echo JText::_('A_RESTRICTIONS'); ?></a></li>
			<li><a href="#censor" data-toggle="tab"><?php echo JText::_('A_CENSOR'); ?></a></li>
			<li><a href="#messages" data-toggle="tab"><?php echo JText::_('A_MESSAGES'); ?></a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="general">
				<?php echo $this->loadTemplate('general'); ?>
			</div>
			<div class="tab-pane" id="layout">
				<?php echo $this->loadTemplate('layout'); ?>
			</div>
			<div class="tab-pane" id="permissions">
				<?php echo $this->loadTemplate('permissions'); ?>
			</div>
			<div class="tab-pane" id="restrictions">
				<?php echo $this->loadTemplate('restrictions'); ?>
			</div>
			<div class="tab-pane" id="censor">
				<?php echo $this->loadTemplate('censor'); ?>
			</div>
			<div class="tab-pane" id="messages">
				<?php echo $this->loadTemplate('messages'); ?>
			</div>
		</div>
		<div>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>

<form action="<?php echo JRoute::_('index.php?option=com_jcomments'); ?>" method="post" id="settings-save-form">
	<div>
		<input type="hidden" name="base64" id="base64" value=""/>
		<input type="hidden" name="task" value="settings.save"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


