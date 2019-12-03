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
 * JComments comments table
 *
 */
class JCommentsTableComment extends JTable
{
	/** @var int Primary key */
	var $id = null;
	/** @var int */
	var $parent = null;
	/** @var int */
	var $thread_id = null;
	/** @var string */
	var $path = null;
	/** @var int */
	var $level = null;
	/** @var int */
	var $object_id = null;
	/** @var string */
	var $object_group = null;
	/** @var string */
	var $object_params = null;
	/** @var string */
	var $lang = null;
	/** @var int */
	var $userid = null;
	/** @var string */
	var $name = null;
	/** @var string */
	var $username = null;
	/** @var string */
	var $title = null;
	/** @var string */
	var $comment = null;
	/** @var string */
	var $email = null;
	/** @var string */
	var $homepage = null;
	/** @var datetime */
	var $date = null;
	/** @var string */
	var $ip = null;
	/** @var int */
	var $isgood = null;
	/** @var int */
	var $ispoor = null;
	/** @var boolean */
	var $published = null;
	/** @var boolean */
	var $deleted = null;
	/** @var boolean */
	var $subscribe = null;
	/** @var string */
	var $source = null;
	/** @var boolean */
	var $checked_out = 0;
	/** @var datetime */
	var $checked_out_time = 0;
	/** @var string */
	var $editor = '';

	/**
	 * @param JDatabase $_db A database connector object
	 * @return JCommentsTableComment
	 */
	public function __construct(&$_db)
	{
		parent::__construct('#__jcomments', 'id', $_db);
	}

	/**
	 * Magic method to get an object property's value by name.
	 *
	 * @param   string $name  Name of the property for which to return a value.
	 * @return  mixed  The requested value if it exists.
	 */
	public function __get($name)
	{
		switch ($name) {
			case 'datetime':
				return $this->date;
				break;
		}

		return null;
	}

	public function store($updateNulls = false)
	{
		$config = JCommentsFactory::getConfig();
		$app = JFactory::getApplication();

		if (JCommentsSystemPluginHelper::isAdmin($app)) {
			$language = JFactory::getLanguage();
			$language->load('com_jcomments', JPATH_SITE);

			if ($this->id == 0 && !empty($this->source)) {
				$this->comment = $this->clearComment($this->comment);
				$this->homepage = strip_tags($this->homepage);
				$this->title = strip_tags($this->title);

				if (!$this->userid) {
					$this->name = $this->clearComment($this->name);
					$this->username = $this->clearComment($this->username);
				}
			}
		}

		if ($this->parent > 0) {
			$parent = new JCommentsTableComment($this->_db);
			if ($parent->load($this->parent)) {
				if (empty($this->title) && $config->getInt('comment_title') == 1) {
					if (!empty($parent->title)) {
						if (strpos($parent->title, JText::_('COMMENT_TITLE_RE')) === false) {
							$this->title = JText::_('COMMENT_TITLE_RE') . ' ' . $parent->title;
						} else {
							$this->title = $parent->title;
						}
					}
				}

				$this->thread_id = $parent->thread_id ? $parent->thread_id : $parent->id;
				$this->level = $parent->level + 1;
				$this->path = $parent->path . ',' . $parent->id;
			}
		} else {
			if (empty($this->title) && $config->getInt('comment_title') == 1) {
				$title = JCommentsObjectHelper::getTitle($this->object_id, $this->object_group, $this->lang);
				if (!empty($title)) {
					$this->title = JText::_('COMMENT_TITLE_RE') . ' ' . $title;
				}
			}

			$this->path = '0';
		}

		if (isset($this->datetime)) {
			unset($this->datetime);
		}

		if (isset($this->author)) {
			unset($this->author);
		}

		return parent::store($updateNulls);
	}

	public function delete($oid = null)
	{
		$k = $this->_tbl_key;
		$id = $oid ? $oid : $this->$k;

		$result = parent::delete($oid);

		if ($result) {
			// process nested comments (threaded mode)
			$query = "SELECT id, parent"
				. "\n FROM #__jcomments"
				. "\n WHERE `object_group` = " . $this->_db->Quote($this->object_group)
				. "\n AND `object_id`= " . $this->object_id;
			$this->_db->setQuery($query);
			$rows = $this->_db->loadObjectList();

			require_once(JCOMMENTS_LIBRARIES . '/joomlatune/tree.php');

			$tree = new JoomlaTuneTree($rows);
			$descendants = $tree->descendants($id);

			unset($rows);

			if (count($descendants)) {
				$query = "DELETE FROM #__jcomments WHERE id IN (" . implode(',', $descendants) . ')';
				$this->_db->setQuery($query);
				$this->_db->execute();

				$descendants[] = $id;
				$query = "DELETE FROM #__jcomments_votes WHERE commentid IN (" . implode(',', $descendants) . ')';
				$this->_db->setQuery($query);
				$this->_db->execute();

				$query = "DELETE FROM #__jcomments_reports WHERE commentid IN (" . implode(',', $descendants) . ')';
				$this->_db->setQuery($query);
				$this->_db->execute();
			} else {
				// delete comment's vote info
				$query = "DELETE FROM #__jcomments_votes WHERE commentid = " . $id;
				$this->_db->setQuery($query);
				$this->_db->execute();

				// delete comment's reports info
				$query = "DELETE FROM #__jcomments_reports WHERE commentid = " . $id;
				$this->_db->setQuery($query);
				$this->_db->execute();
			}
			unset($descendants);
		}

		return $result;
	}

	public function markAsDeleted()
	{
		$this->title = null;
		$this->deleted = 1;
		$this->store();
	}

	protected function clearComment($value)
	{
		// change \n to <br />
		$matches = array();
		preg_match_all('#(\[code\=?([a-z0-9]*?)\].*\[\/code\])#isUu', trim($value), $matches);

		$map = array();
		$key = '';

		foreach ($matches[1] as $code) {
			$key = '{' . md5($code . $key) . '}';
			$map[$key] = $code;
			$value = preg_replace('#' . preg_quote($code, '#') . '#isUu', $key, $value);
		}

		$value = JCommentsText::nl2br($value);

		foreach ($map as $key => $code) {
			$value = preg_replace('#' . preg_quote($key, '#') . '#isUu', $code, $value);
		}

		// strip bbcodes
		$patterns = array(
			'/\[font=(.*?)\](.*?)\[\/font\]/i'
		, '/\[size=(.*?)\](.*?)\[\/size\]/i'
		, '/\[color=(.*?)\](.*?)\[\/color\]/i'
		, '/\[b\](null|)\[\/b\]/i'
		, '/\[i\](null|)\[\/i\]/i'
		, '/\[u\](null|)\[\/u\]/i'
		, '/\[s\](null|)\[\/s\]/i'
		, '/\[url=null\]null\[\/url\]/i'
		, '/\[img\](null|)\[\/img\]/i'
		, '/\[url=(.*?)\](.*?)\[\/url\]/i'
		, '/\[email](.*?)\[\/email\]/i'
			// JA Comment syntax
		, '/\[quote=\"?([^\:\]]+)(\:[0-9]+)?\"?\]/ism'
		, '/\[link=\"?([^\]]+)\"?\]/ism'
		, '/\[\/link\]/ism'
		, '/\[youtube ([^\s]+) youtube\]/ism'
		);

		$replacements = array(
			'\\2'
		, '\\2'
		, '\\2'
		, ''
		, ''
		, ''
		, ''
		, ''
		, ''
		, '\\2 ([url]\\1[/url])'
		, '\\1'
		, '[quote name="\\1"]'
		, '[url=\\1]'
		, '[/url]'
		, '[youtube]\\1[/youtube]'
		);
		$value = preg_replace($patterns, $replacements, $value);

		return $value;
	}

	protected function clearName($value)
	{
		return preg_replace('/[\'"\>\<\(\)\[\]]?+/iu', '', $value);
	}
}