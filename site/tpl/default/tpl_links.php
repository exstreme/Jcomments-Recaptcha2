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
 * Template for links (Readmore and Add comment) attached to content items on frontpage and blogs
 */
class jtt_tpl_links extends JoomlaTuneTemplate
{
	function render() 
	{
		$readmoreLink = $this->getReadmoreLink();
		$commentsLink = $this->getCommentsLink();

		$hitsCount = '';
		
		if ($this->getVar('show_hits', 0) == 1) {
			$content = $this->getVar('content-item');


			if (!isset($content->hits)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT hits FROM #__content WHERE id = ' . (int) $content->id);
				$cnt = (int) $db->loadResult();
			} else {
				$cnt = (int) $content->hits;
			}

			$hitsCount = JText::sprintf('ARTICLE_HITS', $cnt);
		}

		if ($readmoreLink != '' || $commentsLink != '') {
?>
<div class="jcomments-links"><?php echo $readmoreLink; ?> <?php echo $commentsLink; ?> <?php echo $hitsCount; ?></div>
<?php
		}
	}

	/*
	 *
	 * Display Readmore link
	 *
	 */
	function getReadmoreLink() 
	{
		if ($this->getVar('readmore_link_hidden', 0) == 1) {
			return '';
		}

		$link  = $this->getVar('link-readmore');
		$text  = $this->getVar('link-readmore-text');
		$title = $this->getVar('link-readmore-title');
		$css   = $this->getVar('link-readmore-class');

		return '<a class="'.$css.'" href="'.$link.'" title="'.htmlspecialchars($title).'">'.$text.'</a>';
	}

	/*
	 *
	 * Display Comments or Add comments link
	 *
	 */
	function getCommentsLink()
	{
		if ($this->getVar('comments_link_hidden') == 1) {
			return '';
		}

		$style = $this->getVar('comments_link_style');
		$count = $this->getVar('comments-count');
		$link  = $this->getVar('link-comment');
		$text  = $this->getVar('link-comment-text');
		$css   = $this->getVar('link-comments-class');

		switch($style) {
			case -1:
				return $count > 0 ? '<span class="'.$css.'">'.$text.'</span>' : '';
				break;
			default:
				return '<a class="'.$css.'" href="'.$link.'" title="'.htmlspecialchars($text).'">'.$text.'</a>';
				break;
		}
	}
}