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
 * Comment item template. Results of rendering used in tpl_list.php
 */
class jtt_tpl_comment extends JoomlaTuneTemplate
{
	function render()
	{
		$comment = $this->getVar('comment');

		if (isset($comment)) {
			if ($this->getVar('get_comment_vote', 0) == 1) {
				// return comment vote
			 	$this->getCommentVoteValue( $comment );
			} else if ($this->getVar('get_comment_body', 0) == 1) {
				// return only comment body (for example after quick edit)
				echo $comment->comment;
			} else {
				// return all comment item
?>
<div class="rbox">
<?php

				$comment_number = $this->getVar('comment-number', 1);
				$thisurl = $this->getVar('thisurl', '');

				$commentBoxIndentStyle = ($this->getVar('avatar') == 1) ? ' avatar-indent' : '';

				if ($this->getVar('avatar') == 1) {
?>
<div class="comment-avatar"><?php echo $comment->avatar; ?></div>
<?php
				}
?>
<div class="comment-box<?php echo $commentBoxIndentStyle; ?>">
<?php
				if ($this->getVar('comment-show-vote', 0) == 1) {
					$this->getCommentVote( $comment );
				}
?>
<a class="comment-anchor" href="<?php echo $thisurl; ?>#comment-<?php echo $comment->id; ?>" id="comment-<?php echo $comment->id; ?>">#<?php echo $comment_number; ?></a>
<?php
				if (($this->getVar('comment-show-title') > 0) && ($comment->title != '')) {
?>
<span class="comment-title"><?php echo $comment->title; ?></span> &mdash; 
<?php
				}
				if ($this->getVar('comment-show-homepage') == 1) {
?>
<a class="author-homepage" href="<?php echo $comment->homepage; ?>" rel="nofollow" title="<?php echo $comment->author; ?>"><?php echo $comment->author; ?></a>
<?php
				} else {
?>
<span class="comment-author"><?php echo $comment->author?></span>
<?php
				}
?>
<?php
				if (($this->getVar('comment-show-email') > 0) && ($comment->email != '')) {
?>
<a class="comment-email" href="mailto:<?php echo $comment->email; ?>"><?php echo $comment->email; ?></a>
<?php
				}
?>
<span class="comment-date"><?php echo JCommentsText::formatDate($comment->date, JText::_('DATETIME_FORMAT')); ?></span>
<div class="comment-body" id="comment-body-<?php echo $comment->id; ?>"><?php echo $comment->comment; ?></div>
<?php
				if (($this->getVar('button-reply') == 1)
				|| ($this->getVar('button-quote') == 1)
				|| ($this->getVar('button-report') == 1)) {
?>
<span class="comments-buttons">
<?php
					if ($this->getVar('button-reply') == 1) {
?>
<a href="#" onclick="jcomments.showReply(<?php echo $comment->id; ?>); return false;"><?php echo JText::_('BUTTON_REPLY'); ?></a>
<?php
						if ($this->getVar('button-quote') == 1) {
?>
 | <a href="#" onclick="jcomments.showReply(<?php echo $comment->id; ?>,1); return false;"><?php echo JText::_('BUTTON_REPLY_WITH_QUOTE'); ?></a> | 
<?php
						}
					}
					if ($this->getVar('button-quote') == 1) {
?>
<a href="#" onclick="jcomments.quoteComment(<?php echo $comment->id; ?>); return false;"><?php echo JText::_('BUTTON_QUOTE'); ?></a>
<?php
					}
					if ($this->getVar('button-report') == 1) {
						if ($this->getVar('button-quote') == 1 || $this->getVar('button-reply') == 1) {
?>
 | 
<?php
						}
?>
<a href="#" onclick="jcomments.reportComment(<?php echo $comment->id; ?>); return false;"><?php echo JText::_('BUTTON_REPORT'); ?></a>
<?php
					}
?>
</span>
<?php
				}
?>
</div><div class="clear"></div>
<?php
				// show frontend moderation panel
				$this->getCommentAdministratorPanel( $comment );
?>
</div>
<?php
			}
		}
	}

	/*
	 *
	 * Displays comment's administration panel
	 *
	 */
	function getCommentAdministratorPanel( &$comment )
	{
		if ($this->getVar('comments-panel-visible', 0) == 1) {
?>
<p class="toolbar" id="comment-toolbar-<?php echo $comment->id; ?>">
<?php
			if ($this->getVar('button-edit') == 1) {
				$text = JText::_('BUTTON_EDIT');
?>
	<a class="toolbar-button-edit" href="#" onclick="jcomments.editComment(<?php echo $comment->id; ?>); return false;" title="<?php echo $text; ?>"></a>
<?php
			}

			if ($this->getVar('button-delete') == 1) {
				$text = JText::_('BUTTON_DELETE');
?>
	<a class="toolbar-button-delete" href="#" onclick="if (confirm('<?php echo JText::_('BUTTON_DELETE_CONIRM'); ?>')){jcomments.deleteComment(<?php echo $comment->id; ?>);}return false;" title="<?php echo $text; ?>"></a>
<?php
			}

			if ($this->getVar('button-publish') == 1) {
				$text = $comment->published ? JText::_('BUTTON_UNPUBLISH') : JText::_('BUTTON_PUBLISH');
				$class = $comment->published ? 'publish' : 'unpublish';
?>
	<a class="toolbar-button-<?php echo $class; ?>" href="#" onclick="jcomments.publishComment(<?php echo $comment->id; ?>);return false;" title="<?php echo $text; ?>"></a>
<?php
			}

			if ($this->getVar('button-ip') == 1) {
				$text = JText::_('BUTTON_IP') . ' ' . $comment->ip;
?>
	<a class="toolbar-button-ip" href="#" onclick="jcomments.go('http://www.ripe.net/perl/whois?searchtext=<?php echo $comment->ip; ?>');return false;" title="<?php echo $text; ?>"></a>
<?php
			}

			if ($this->getVar('button-ban') == 1) {
				$text = JText::_('BUTTON_BANIP');
?>
	<a class="toolbar-button-ban" href="#" onclick="jcomments.banIP(<?php echo $comment->id; ?>);return false;" title="<?php echo $text; ?>"></a>
<?php
			}
?>
</p>
<div class="clear"></div>
<?php
		}
	}

	function getCommentVote( &$comment )
	{
		$value = intval($comment->isgood) - intval($comment->ispoor);

		if ($value == 0 && $this->getVar('button-vote', 0) == 0) {
			return;
		}
?>
<span class="comments-vote">
	<span id="comment-vote-holder-<?php echo $comment->id; ?>">
<?php
		if ($this->getVar('button-vote', 0) == 1) {
?>
<a href="#" class="vote-good" title="<?php echo JText::_('BUTTON_VOTE_GOOD'); ?>" onclick="jcomments.voteComment(<?php echo $comment->id;?>, 1);return false;"></a><a href="#" class="vote-poor" title="<?php echo JText::_('BUTTON_VOTE_BAD'); ?>" onclick="jcomments.voteComment(<?php echo $comment->id;?>, -1);return false;"></a>
<?php
		}
		echo $this->getCommentVoteValue( $comment );
?>
	</span>
</span>
<?php
	}

	function getCommentVoteValue( &$comment )
	{
		$value = intval($comment->isgood - $comment->ispoor);

		if ($value == 0 && $this->getVar('button-vote', 0) == 0 && $this->getVar('get_comment_vote', 0) == 0) {
			// if current value is 0 and user has no rights to vote - hide 0
			return;
		}

		if ($value < 0) {
			$class = 'poor';
		} else if ($value > 0) {
			$class = 'good';
			$value = '+' . $value;
		} else {
			$class = 'none';
		}
?>
<span class="vote-<?php echo $class; ?>"><?php echo $value; ?></span>
<?php
	}
}