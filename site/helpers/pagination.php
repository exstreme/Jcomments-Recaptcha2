<?php
/**
 * JComments - Joomla Comment System
 * 
 * @version 3.0
 * @package JComments
 * @subpackage Helpers
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

/**
 * JComments Pagination Helper
 */
class JCommentsPagination {

	var $commentsCount = 0;
	var $commentsOrder = null;
	var $commentsPerPage = null;
	var $commentsPageLimit = null;
	var $totalPages = 1;
	var $limitStart = 0;
	var $currentPage = 0;

	public function __construct($object_id, $object_group)
	{
		$config = JCommentsFactory::getConfig();

		$this->commentsPerPage = $config->getInt('comments_per_page');
		$this->commentsPageLimit = $config->getInt('comments_page_limit');
		$this->commentsOrder = $config->get('comments_list_order');

		if ($this->commentsPerPage > 0) {
			$this->setCommentsCount(JComments::getCommentsCount($object_id, $object_group));
		}
	}

	public function setCommentsCount($commentsCount)
	{
		$this->commentsCount = $commentsCount;
		$this->_calculateTotalPages();
	}

	public function setCurrentPage($currentPage)
	{
		if ($this->commentsPerPage > 0 && $this->commentsCount > 0) {
			if ($currentPage <= 0) {
				$this->currentPage = $this->commentsOrder == 'DESC' ? 1 : $this->totalPages;
			} else if ($currentPage > $this->totalPages) {
				$this->currentPage = $this->totalPages;
			} else {
				$this->currentPage = $currentPage;
			}
			$this->limitStart = (($this->currentPage-1) * $this->commentsPerPage);
		} else {
			$this->currentPage = 0;
			$this->limitStart = 0;
		}
	}

	public function getTotalPages()
	{
		return $this->totalPages;
	}

	public function getCommentsPerPage()
	{
		return $this->commentsPerPage;
	}

	public function getCommentsCount()
	{
		return $this->commentsCount;
	}

	public function getCurrentPage()
	{
		return $this->currentPage;
	}

	public function getLimitStart()
	{
		return $this->limitStart;
	}

	public function getCommentPage($object_id, $object_group, $comment_id)
	{
		$result = 0;

		if ($this->commentsPerPage > 0) {
			$compare = $this->commentsOrder == 'DESC' ? '>=' : '<=';
			$prev = JComments::getCommentsCount($object_id, $object_group, "\n id " . $compare . " " . $comment_id);
			$result = max(ceil($prev / $this->commentsPerPage), 1);
		}
		return $result;
	}

	protected function _calculateTotalPages()
	{
		if ($this->commentsPerPage > 0) {
			$this->totalPages = ceil($this->commentsCount / $this->commentsPerPage);
			if (($this->commentsPageLimit > 0) && ($this->totalPages > $this->commentsPageLimit)) {
				$this->totalPages = $this->commentsPageLimit;
				$this->commentsPerPage = ceil($this->commentsCount / $this->totalPages);

				if (ceil($this->commentsCount / $this->commentsPerPage) < $this->totalPages) {
					$this->totalPages = ceil($this->commentsCount / $this->commentsPerPage);
  				}
			}
		}
	}
}