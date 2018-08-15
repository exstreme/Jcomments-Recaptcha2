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
 * Threaded comments list template
 *
 */
class jtt_tpl_tree extends JoomlaTuneTemplate
{
	function render() 
	{
		$comments = $this->getVar('comments-items');

		if (isset($comments)) {
			$this->getHeader();
?>
<div class="comments-list" id="comments-list-0">
<?php
			$i = 0;
			
			$count = count($comments);
			$currentLevel = 0;
		
			foreach($comments as $id => $comment) {
				if ($currentLevel < $comment->level) {
?>
	</div>
	<div class="comments-list" id="comments-list-<?php echo $comment->parent; ?>">
<?php				
				} else {
					$j = 0;
	
					if ($currentLevel >= $comment->level) {
						$j = $currentLevel - $comment->level;
					} else if ($comment->level > 0 && $i == $count - 1) {
						$j = $comment->level;
					}

					while($j > 0) {
?>
	</div>
<?php
						$j--;
					}
				}
?>
		<div class="<?php echo ($i%2 ? 'odd' : 'even'); ?>" id="comment-item-<?php echo $id; ?>">
<?php
				echo $comment->html;

				if ($comment->children == 0) {
?>
		</div>
<?php
				}
				
				if ($comment->level > 0 && $i == $count - 1) {
					$j = $comment->level;
				}

				while($j > 0) {
?>
	</div>
<?php					$j--;
				}

				$i++;
				$currentLevel = $comment->level;
			}
?>
</div>
<div id="comments-list-footer"><?php echo $this->getFooter();?></div>
<?php
		} else {
			// display single comment item (works when new comment is added)
			$comment = $this->getVar('comment-item');

			if (isset($comment)) {
				$i = $this->getVar('comment-modulo');
				$id = $this->getVar('comment-id');
?>
	<div class="<?php echo ($i%2 ? 'odd' : 'even'); ?>" id="comment-item-<?php echo $id; ?>"><?php echo $comment; ?></div>
<?php
			} else {
?>
<div class="comments-list" id="comments-list-0"></div>
<?php
			}
		}

	}

	/*
	*
	* Display comments header and small buttons: rss and refresh
	*
	*/
	function getHeader()
	{
		$object_id = $this->getVar('comment-object_id');
		$object_group = $this->getVar('comment-object_group');

		$btnRSS = '';
		$btnRefresh = '';
		
		if ($this->getVar('comments-refresh', 1) == 1) {
			$btnRefresh = '<a class="refresh" href="#" title="'.JText::_('BUTTON_REFRESH').'" onclick="jcomments.showPage('.$object_id.',\''. $object_group . '\',0);return false;">&nbsp;</a>';
		}

		if ($this->getVar('comments-rss') == 1) {
			$link = $this->getVar('rssurl');
			if (!empty($link)) {
				$btnRSS = '<a class="rss" href="'.$link.'" title="'.JText::_('BUTTON_RSS').'" target="_blank">&nbsp;</a>';
			}
		}
?>
<h4><?php echo JText::_('COMMENTS_LIST_HEADER'); ?> <?php echo $btnRSS; ?><?php echo $btnRefresh; ?></h4>
<?php
	}

	/*
	*
	* Display RSS feed and/or Refresh buttons after comments list
	*
	*/
	function getFooter()
	{
		$footer = '';

		$object_id = $this->getVar('comment-object_id');
		$object_group = $this->getVar('comment-object_group');

		$lines = array();

		if ($this->getVar('comments-refresh', 1) == 1) {
			$lines[] = '<a class="refresh" href="#" title="'.JText::_('BUTTON_REFRESH').'" onclick="jcomments.showPage('.$object_id.',\''. $object_group . '\',0);return false;">'.JText::_('BUTTON_REFRESH').'</a>';
		}

		if ($this->getVar('comments-rss', 1) == 1) {
			$link = $this->getVar('rssurl');
			if (!empty($link)) {
				$lines[] = '<a class="rss" href="'.$link.'" title="'.JText::_('BUTTON_RSS').'" target="_blank">'.JText::_('BUTTON_RSS').'</a>';
			}
		}

		if ($this->getVar('comments-can-subscribe', 0) == 1) {
			$isSubscribed = $this->getVar('comments-user-subscribed', 0);

			$text = $isSubscribed ? JText::_('BUTTON_UNSUBSCRIBE') : JText::_('BUTTON_SUBSCRIBE');
			$func = $isSubscribed ? 'unsubscribe' : 'subscribe';

			$lines[] = '<a id="comments-subscription" class="subscribe" href="#" title="' . $text . '" onclick="jcomments.' . $func . '('.$object_id.',\''. $object_group . '\');return false;">'. $text .'</a>';
		}

		if (count($lines)) {
			$footer = implode('<br />', $lines);
		}

		return $footer;
	}
}