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

JHtml::_('behavior.keepalive');
if (version_compare(JVERSION, '3.4', 'ge')) {
    JHtml::_('behavior.formvalidator');
} else {
    JHtml::_('behavior.formvalidation');
}


/**
 * Comments form template
 */
class jtt_tpl_form extends JoomlaTuneTemplate
{
    function render()
    {
        if ($this->getVar('comments-form-message', 0) == 1) {
            $this->getMessage($this->getVar('comments-form-message-text'));
            return;
        }

        if ($this->getVar('comments-form-link', 0) == 1) {
            $this->getCommentsFormLink();
            return;
        }

        $this->getCommentsFormFull();
    }

    /*
     *
     * Displays full comments form (with smilies, bbcodes and other stuff)
     *
     */
    function getCommentsFormFull()
    {
        $object_id = $this->getVar('comment-object_id');
        $object_group = $this->getVar('comment-object_group');

        $htmlBeforeForm = $this->getVar('comments-html-before-form');
        $htmlAfterForm = $this->getVar('comments-html-after-form');

        $htmlFormPrepend = $this->getVar('comments-form-html-prepend');
        $htmlFormAppend = $this->getVar('comments-form-html-append');

        ?>
        <span><?php echo JText::_('FORM_HEADER'); ?></span>
        <?php
        if ($this->getVar('comments-form-policy', 0) == 1) {
            ?>
            <div class="comments-policy"><?php echo $this->getVar('comments-policy'); ?></div>
            <?php
        }
        ?>
        <?php echo $htmlBeforeForm; ?>
        <a id="addcomments" href="#addcomments"></a>
        <form id="comments-form" name="comments-form" action="javascript:void(null);">
            <?php
            $this->getFormFields($htmlFormPrepend);
            ?>
            <?php
            if ($this->getVar('comments-form-user-name', 1) == 1) {
                $text = ($this->getVar('comments-form-user-name-required', 1) == 0) ? JText::_('FORM_NAME') : JText::_('FORM_NAME_REQUIRED');
                ?>
                <p>
	<span>
		<input id="comments-form-name" type="text" name="name" value=""
               maxlength="<?php echo $this->getVar('comment-name-maxlength'); ?>" size="22" tabindex="1"/>
		<label for="comments-form-name"><?php echo $text; ?></label>
	</span>
                </p>
                <?php
            }
            if ($this->getVar('comments-form-user-email', 1) == 1) {
                $text = ($this->getVar('comments-form-email-required', 1) == 0) ? JText::_('FORM_EMAIL') : JText::_('FORM_EMAIL_REQUIRED');
                ?>
                <p>
	<span>
		<input id="comments-form-email" type="text" name="email" value="" size="22" tabindex="2"/>
		<label for="comments-form-email"><?php echo $text; ?></label>
	</span>
                </p>
                <?php
            }
            if ($this->getVar('comments-form-user-homepage', 0) == 1) {
                $text = ($this->getVar('comments-form-homepage-required', 1) == 0) ? JText::_('FORM_HOMEPAGE') : JText::_('FORM_HOMEPAGE_REQUIRED');
                ?>
                <p>
	<span>
		<input id="comments-form-homepage" type="text" name="homepage" value="" size="22" tabindex="3"/>
		<label for="comments-form-homepage"><?php echo $text; ?></label>
	</span>
                </p>
                <?php
            }
            if ($this->getVar('comments-form-title', 0) == 1) {
                $text = ($this->getVar('comments-form-title-required', 1) == 0) ? JText::_('FORM_TITLE') : JText::_('FORM_TITLE_REQUIRED');
                ?>
                <p>
	<span>
		<input id="comments-form-title" type="text" name="title" value="" size="22" tabindex="4"/>
		<label for="comments-form-title"><?php echo $text; ?></label>
	</span>
                </p>
                <?php
            }
            ?>
            <p>
	<span>
		<textarea id="comments-form-comment" name="comment" cols="65" rows="8" tabindex="5"></textarea>
	</span>
            </p>
            <?php
            if ($this->getVar('comments-form-subscribe', 0) == 1) {
                ?>
                <p>
	<span>
		<input class="checkbox" id="comments-form-subscribe" type="checkbox" name="subscribe" value="1" tabindex="5"/>
		<label for="comments-form-subscribe"><?php echo JText::_('FORM_SUBSCRIBE'); ?></label><br/>
	</span>
                </p>
                <?php
            }
            if ($this->getVar('var_show_checkbox_terms_of_use', 0) == 1) {
                ?>
                <p id="checkbox_terms_of_use_css">
	<span>
		<input class="checkbox" id="show_checkbox_terms_of_use" type="checkbox" name="name_checkbox_terms_of_use"
               value="1" tabindex="5"/>
		<label for="show_checkbox_terms_of_use"><?php echo JText::_('FORM_ACCEPT_TERMS_OF_USE'); ?></label><br/>
	</span>
                </p>
                <?php
            }
            if ($this->getVar('comments-form-captcha', 0) == 1) {
                $html = $this->getVar('comments-form-captcha-html');
                if ($html == 'kcaptcha') {
                    $link = JCommentsFactory::getLink('captcha');
                    ?>
                    <p>
	<span>
		<img class="captcha" id="comments-form-captcha-image" src="<?php echo $link; ?>" width="121" height="60"
             alt="<?php echo JText::_('FORM_CAPTCHA'); ?>"/><br/>
		<span class="captcha" id="captcha-span-jc"><?php echo JText::_('FORM_CAPTCHA_REFRESH'); ?></span><br/>
		<input class="captcha" id="comments-form-captcha" type="text" name="captcha_refid" value="" size="5"
               tabindex="6"/><br/>
	</span>
                    </p>
                    <?php
                } else {
                    $recaptcha = JCaptcha::getInstance($html, array('namespace' => 'jcomments'));
                    echo $recaptcha->display('recaptcha', 'dynamic_recaptcha_1', 'g-recaptcha');
                    ?>
                    <?php
                }
            }
            ?>
            <?php
            $this->getFormFields($htmlFormAppend);
            ?>
            <div id="comments-form-buttons">
                <div class="btn" id="comments-form-send">
                    <div><a href="#" tabindex="7"
                            title="<?php echo JText::_('FORM_SEND_HINT'); ?>"><?php echo JText::_('FORM_SEND'); ?></a>
                    </div>
                </div>
                <div class="btn" id="comments-form-cancel" style="display:none;">
                    <div><a href="#" tabindex="8"
                            title="<?php echo JText::_('FORM_CANCEL'); ?>"><?php echo JText::_('FORM_CANCEL'); ?></a>
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
            <div>
                <input type="hidden" name="object_id" value="<?php echo $object_id; ?>"/>
                <input type="hidden" name="object_group" value="<?php echo $object_group; ?>"/>
            </div>
        </form>
        <!--<script type="text/javascript">-->
        <?php
        $script = "
function JCommentsInitializeForm()
{
	var jcEditor = new JCommentsEditor('comments-form-comment', true);
";
        if ($this->getVar('comments-form-bbcode', 0) == 1) {
            $bbcodes = array('b' => array(0 => JText::_('FORM_BBCODE_B'), 1 => JText::_('BBCODE_HINT_ENTER_TEXT'))
            , 'i' => array(0 => JText::_('FORM_BBCODE_I'), 1 => JText::_('BBCODE_HINT_ENTER_TEXT'))
            , 'u' => array(0 => JText::_('FORM_BBCODE_U'), 1 => JText::_('BBCODE_HINT_ENTER_TEXT'))
            , 's' => array(0 => JText::_('FORM_BBCODE_S'), 1 => JText::_('BBCODE_HINT_ENTER_TEXT'))
            , 'img' => array(0 => JText::_('FORM_BBCODE_IMG'), 1 => JText::_('BBCODE_HINT_ENTER_FULL_URL_TO_THE_IMAGE'))
            , 'url' => array(0 => JText::_('FORM_BBCODE_URL'), 1 => JText::_('BBCODE_HINT_ENTER_FULL_URL'))
            , 'hide' => array(0 => JText::_('FORM_BBCODE_HIDE'), 1 => JText::_('BBCODE_HINT_ENTER_TEXT_TO_HIDE_IT_FROM_UNREGISTERED'))
            , 'quote' => array(0 => JText::_('FORM_BBCODE_QUOTE'), 1 => JText::_('BBCODE_HINT_ENTER_TEXT_TO_QUOTE'))
            , 'list' => array(0 => JText::_('FORM_BBCODE_LIST'), 1 => JText::_('BBCODE_HINT_ENTER_LIST_ITEM_TEXT'))
            );

            foreach ($bbcodes as $k => $v) {
                if ($this->getVar('comments-form-bbcode-' . $k, 0) == 1) {
                    $title = trim(JCommentsText::jsEscape($v[0]));
                    $text = trim(JCommentsText::jsEscape($v[1]));
                    $script .= "
	jcEditor.addButton('$k','$title','$text');
";
                }
            }
        }

        $customBBCodes = $this->getVar('comments-form-custombbcodes');
        if (!empty($customBBCodes)) {
            foreach ($customBBCodes as $code) {
                if ($code->button_enabled) {
                    $k = 'custombbcode' . $code->id;
                    $title = trim(JCommentsText::jsEscape($code->button_title));
                    $text = empty($code->button_prompt) ? JText::_('BBCODE_HINT_ENTER_TEXT') : JText::_($code->button_prompt);
                    $open_tag = $code->button_open_tag;
                    $close_tag = $code->button_close_tag;
                    $icon = $code->button_image;
                    $css = $code->button_css;
                    $script .= "
	jcEditor.addButton('$k','$title','$text','$open_tag','$close_tag','$css','$icon');
";
                }
            }
        }

        $smiles = $this->getVar('comment-form-smiles');

        if (!empty($smiles)) {
            $script .= "
	jcEditor.initSmiles('" . $this->getVar("smilesurl") . "');
";
            foreach ($smiles as $code => $icon) {
                $code = trim(JCommentsText::jsEscape($code));
                $icon = trim(JCommentsText::jsEscape($icon));
                $script .= "
	jcEditor.addSmile('$code','$icon');
";
            }
        }
        if ($this->getVar('comments-form-showlength-counter', 0) == 1) {
            $script .= "
	jcEditor.addCounter(" . $this->getVar('comment-maxlength') . ", '" . JText::_('FORM_CHARSLEFT_PREFIX') . "', '" . JText::_('FORM_CHARSLEFT_SUFFIX') . "', 'counter');
";
        }
        $script .= "	jcomments.setForm(new JCommentsForm('comments-form', jcEditor));
}

";
        if ($this->getVar('comments-form-ajax', 0) == 1) {
            $script .= "
setTimeout(JCommentsInitializeForm, 100);
";
        } else {
            $script .= "
if (window.addEventListener) {window.addEventListener('load',JCommentsInitializeForm,false);}
else if (document.addEventListener){document.addEventListener('load',JCommentsInitializeForm,false);}
else if (window.attachEvent){window.attachEvent('onload',JCommentsInitializeForm);}
else {if (typeof window.onload=='function'){var oldload=window.onload;window.onload=function(){oldload();JCommentsInitializeForm();}} else window.onload=JCommentsInitializeForm;} 
";
        }
        $script .= "
//-->";
//</script>
        JFactory::getDocument()->addScriptDeclaration($script);
        ?>
        <?php echo $htmlAfterForm; ?>
        <?php
    }

    /*
     *
     * Displays link to show comments form
     *
     */
    function getCommentsFormLink()
    {
        $object_id = $this->getVar('comment-object_id');
        $object_group = $this->getVar('comment-object_group');
        ?>
        <div id="comments-form-link">
            <a id="addcomments" href="#addcomments" class="showform" data-object_id="<?php echo $object_id; ?>"
               data-object_group"<?php echo $object_group; ?>" href="#addcomments"
            ><?php echo JText::_('FORM_HEADER'); ?></a>
        </div>
        <?php
    }

    /*
     *
     * Displays service message
     *
     */
    function getMessage($text)
    {
        $htmlBeforeForm = $this->getVar('comments-html-before-form');
        $htmlAfterForm = $this->getVar('comments-html-after-form');
        ?>
        <a id="addcomments" href="#addcomments"></a>
        <?php
        echo $htmlBeforeForm;
        if ($text != '') {
            ?>
            <p class="message"><?php echo $text; ?></p>
            <?php
        }
        echo $htmlAfterForm;
    }

    function getFormFields($fields)
    {
        if (!empty($fields)) {
            $fields = is_array($fields) ? $fields : array($fields);

            foreach ($fields as $field) {

                $labelElement = '';
                $inputElement = '';

                if (is_array($field)) {
                    $labelElement = isset($field['label']) ? $field['label'] : '';
                    $inputElement = isset($field['input']) ? $field['input'] : '';
                } else {
                    $inputElement = $field;
                }

                if (!empty($inputElement)) {
                    ?>
                    <p>
	<span>
		<?php echo $inputElement; ?>
        <?php echo $labelElement; ?>
	</span>
                    </p>
                    <?php
                }
            }
        }
    }
}
?>