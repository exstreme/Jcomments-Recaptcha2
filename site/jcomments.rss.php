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

class JoomlaTuneFeedItem
{
	var $title = "";
	var $link = "";
	var $description = "";
	var $author = "";
	var $category = "";
	var $pubDate = "";
	var $source = "";
}

class JoomlaTuneFeed
{
	var $title = "";
	var $link = "";
	var $syndicationURL = "";
	var $description = "";
	var $lastBuildDate = "";
	var $pubDate = "";
	var $copyright = "";
	var $items = array();

	function __construct()
	{
		$this->items = array();
	}

	function addItem(&$item)
	{
		$item->source = $this->link;
		$this->items[] = $item;
	}

	function render()
	{
		$tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

		$now = JFactory::getDate();
		$now->setTimeZone($tz);

		$this->link = str_replace('&', '&amp;', str_replace('&amp;', '&', $this->link));

		$feed = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$feed .= "<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
		$feed .= "	<channel>\n";
		$feed .= "		<title>" . $this->title . "</title>\n";
		$feed .= "		<description>" . $this->description . "</description>\n";
		$feed .= "		<link>" . $this->link . "</link>\n";
		$feed .= "		<lastBuildDate>" . htmlspecialchars($now->toRFC822(true), ENT_COMPAT, 'UTF-8') . "</lastBuildDate>\n";
		$feed .= "		<generator>JComments</generator>\n";

		if ($this->syndicationURL != '') {
			$feed .= "		<atom:link href=\"" . str_replace(' ', '%20', $this->syndicationURL) . "\" rel=\"self\" type=\"application/rss+xml\" />\n";
		}

		foreach ($this->items as $item) {
			$item->link = str_replace('&', '&amp;', str_replace('&amp;', '&', $item->link));

			$feed .= "		<item>\n";
			$feed .= "			<title>" . htmlspecialchars(strip_tags($item->title), ENT_COMPAT, 'UTF-8') . "</title>\n";
			$feed .= "			<link>" . $item->link . "</link>\n";
			$feed .= "			<description><![CDATA[" . $item->description . "]]></description>\n";

			if ($item->author != "") {
				$feed .= "			<dc:creator>" . htmlspecialchars($item->author, ENT_COMPAT, 'UTF-8') . "</dc:creator>\n";
			}

			if ($item->pubDate != "") {
				$pubDate = JFactory::getDate($item->pubDate);
				$pubDate->setTimeZone($tz);

				$feed .= "			<pubDate>" . htmlspecialchars($pubDate->toRFC822(true), ENT_COMPAT, 'UTF-8') . "</pubDate>\n";
			}

			$feed .= "			<guid>" . $item->link . "</guid>\n";
			$feed .= "		</item>\n";
		}
		$feed .= "	</channel>\n";
		$feed .= "</rss>\n";

		return $feed;
	}

	function display()
	{
		if (!headers_sent()) {
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 900) . ' GMT');
			header('Content-Type: application/xml');
		}
		echo $this->render();
	}
}

/**
 * Export comments to RSS
 */
class JCommentsRSS
{
	public static function showObjectComments()
	{
		$config = JCommentsFactory::getConfig();

		if ($config->get('enable_rss') == '1') {

			$app = JFactory::getApplication('site');
			$object_id = $app->input->getInt('object_id', 0);
			$object_group = JCommentsSecurity::clearObjectGroup($app->input->get('object_group', 'com_content'));
			$limit = $app->input->getInt('limit', $config->getInt('feed_limit', 100));

			// if no group or id specified - return 404
			if ($object_id == 0 || $object_group == '') {
				self::showNotFound();

				return;
			}

			$lm = $limit != $config->getInt('feed_limit') ? ('&amp;limit=' . $limit) : '';

			if (JCommentsMultilingual::isEnabled()) {
				$language = JCommentsMultilingual::getLanguage();
				$lp = '&amp;lang=' . $language;
			} else {
				$language = null;
				$lp = '';
			}

			$liveSite = trim(str_replace(JURI::root(true), '', str_replace('/administrator', '', JURI::root())), '/');
			$syndicationURL = $liveSite . JRoute::_('index.php?option=com_jcomments&amp;task=rss&amp;object_id=' . $object_id . '&amp;object_group=' . $object_group . $lm . $lp . '&amp;format=raw');

			$object_title = JCommentsObjectHelper::getTitle($object_id, $object_group, $language);
			$object_link = JCommentsObjectHelper::getLink($object_id, $object_group, $language);
			$object_link = str_replace('amp;', '', JCommentsFactory::getAbsLink($object_link));

			$rss = new JoomlaTuneFeed();
			$rss->title = $object_title;
			$rss->link = $object_link;
			$rss->syndicationURL = $syndicationURL;
			$rss->description = JText::sprintf('OBJECT_FEED_DESCRIPTION', $rss->title);

			$options = array();
			$options['object_id'] = $object_id;
			$options['object_group'] = $object_group;
			$options['lang'] = $language;
			$options['published'] = 1;
			$options['filter'] = 'c.deleted = 0';
			$options['orderBy'] = 'c.date DESC';
			$options['limit'] = $limit;
			$options['limitStart'] = 0;
			$options['objectinfo'] = true;

			$rows = JCommentsModel::getCommentsList($options);

			$word_maxlength = $config->getInt('word_maxlength');

			foreach ($rows as $row) {
				$comment = JCommentsText::cleanText($row->comment);
				$title = $row->title;
				$author = JComments::getCommentAuthorName($row);

				if ($comment != '') {
					// apply censor filter
					$title = JCommentsText::censor($title);
					$comment = JCommentsText::censor($comment);

					// fix long words problem
					if ($word_maxlength > 0) {
						$comment = JCommentsText::fixLongWords($comment, $word_maxlength, ' ');
						if ($title != '') {
							$title = JCommentsText::fixLongWords($title, $word_maxlength, ' ');
						}
					}

					$item = new JoomlaTuneFeedItem();
					$item->title = ($title != '') ? $title : JText::sprintf('OBJECT_FEED_ITEM_TITLE', $author);
					$item->link = $object_link . '#comment-' . $row->id;
					$item->description = $comment;
					$item->source = $object_link;
					$item->pubDate = $row->date;
					$item->author = $author;
					$rss->addItem($item);
				}
			}

			$rss->display();

			unset($rows, $rss);
			exit();
		}
	}

	public static function showAllComments()
	{
		$config = JCommentsFactory::getConfig();

		if ($config->get('enable_rss') == '1') {

			$app = JFactory::getApplication('site');
			$acl = JCommentsFactory::getACL();
			$object_group = JCommentsSecurity::clearObjectGroup($app->input->get('object_group', 'com_content'));
			$limit = $app->input->getInt('limit', $config->getInt('feed_limit', 100));

			$og = $object_group ? ('&amp;object_group=' . $object_group) : '';
			$lm = $limit != $config->getInt('feed_limit') ? ('&amp;limit=' . $limit) : '';

			if (JCommentsMultilingual::isEnabled()) {
				$language = JCommentsMultilingual::getLanguage();
				$lp = '&amp;lang=' . $language;
			} else {
				$language = null;
				$lp = '';
			}

			$liveSite = trim(str_replace(JURI::root(true), '', str_replace('/administrator', '', JURI::root())), '/');
			$syndicationURL = $liveSite . JRoute::_('index.php?option=com_jcomments&amp;task=rss_full' . $og . $lm . $lp . '&amp;tmpl=raw');

			$rss = new JoomlaTuneFeed();
			$rss->title = JText::sprintf('SITE_FEED_TITLE', $app->getCfg('sitename'));
			$rss->link = str_replace('/administrator', '', JURI::root());
			$rss->syndicationURL = $syndicationURL;
			$rss->description = JText::sprintf('SITE_FEED_DESCRIPTION', $app->getCfg('sitename'));

			if ($object_group != '') {
				$groups = explode(',', $object_group);
			} else {
				$groups = array();
			}

			$options = array();
			$options['object_group'] = $groups;
			$options['lang'] = $language;
			$options['published'] = 1;
			$options['filter'] = 'c.deleted = 0';
			$options['orderBy'] = 'c.date DESC';
			$options['votes'] = false;
			$options['limit'] = $limit;
			$options['limitStart'] = 0;
			$options['objectinfo'] = true;
			$options['access'] = $acl->getUserAccess();

			$rows = JCommentsModel::getCommentsList($options);

			$word_maxlength = $config->getInt('word_maxlength');

			foreach ($rows as $row) {
				$comment = JCommentsText::cleanText($row->comment);

				if ($comment != '') {
					// getting object's information (title and link)					
					$object_title = $row->object_title;
					$object_link = JCommentsFactory::getAbsLink(str_replace('amp;', '', $row->object_link));

					// apply censor filter
					$object_title = JCommentsText::censor($object_title);
					$comment = JCommentsText::censor($comment);

					// fix long words problem
					if ($word_maxlength > 0) {
						$comment = JCommentsText::fixLongWords($comment, $word_maxlength, ' ');
						if ($object_title != '') {
							$object_title = JCommentsText::fixLongWords($object_title, $word_maxlength, ' ');
						}
					}

					$author = JComments::getCommentAuthorName($row);

					$item = new JoomlaTuneFeedItem();
					$item->title = $object_title;
					$item->link = $object_link . '#comment-' . $row->id;
					$item->description = JText::sprintf('SITE_FEED_ITEM_DESCRIPTION', $author, $comment);
					$item->source = $object_link;
					$item->pubDate = $row->date;
					$item->author = $author;
					$rss->addItem($item);
				}
			}

			$rss->display();

			unset($rows, $rss);
			exit();
		}
	}

	public static function showUserComments()
	{
		$config = JCommentsFactory::getConfig();

		if ($config->get('enable_rss') == '1') {

			$app = JFactory::getApplication('site');
			$acl = JCommentsFactory::getACL();
			$userid = $app->input->getInt('userid', 0);
			$limit = $app->input->getInt('limit', $config->getInt('feed_limit', 100));

			$user = JFactory::getUser($userid);
			if (!isset($user->id)) {
				self::showNotFound();

				return;
			}

			$lm = $limit != $config->getInt('feed_limit') ? ('&amp;limit=' . $limit) : '';

			if (JCommentsMultilingual::isEnabled()) {
				$language = JCommentsMultilingual::getLanguage();
				$lp = '&amp;lang=' . $language;
			} else {
				$language = null;
				$lp = '';
			}

			$liveSite = trim(str_replace(JURI::root(true), '', str_replace('/administrator', '', JURI::root())), '/');
			$syndicationURL = $liveSite . JRoute::_('index.php?option=com_jcomments&amp;task=rss_user&amp;userid=' . $userid . $lm . $lp . '&amp;tmpl=raw');

			$user->userid = $user->id;
			$username = JComments::getCommentAuthorName($user);

			$rss = new JoomlaTuneFeed();
			$rss->title = JText::sprintf('USER_FEED_TITLE', $username);
			$rss->link = str_replace('/administrator', '', JURI::root());
			$rss->syndicationURL = $syndicationURL;
			$rss->description = JText::sprintf('USER_FEED_DESCRIPTION', $username);

			$options = array();
			$options['lang'] = $language;
			$options['userid'] = $userid;
			$options['published'] = 1;
			$options['filter'] = 'c.deleted = 0';
			$options['orderBy'] = 'c.date DESC';
			$options['votes'] = false;
			$options['limit'] = $limit;
			$options['limitStart'] = 0;
			$options['objectinfo'] = true;
			$options['access'] = $acl->getUserAccess();

			$rows = JCommentsModel::getCommentsList($options);

			$word_maxlength = $config->getInt('word_maxlength');

			foreach ($rows as $row) {
				$comment = JCommentsText::cleanText($row->comment);

				if ($comment != '') {
					// getting object's information (title and link)
					$object_title = $row->object_title;
					$object_link = JCommentsFactory::getAbsLink(str_replace('amp;', '', $row->object_link));

					// apply censor filter
					$object_title = JCommentsText::censor($object_title);
					$comment = JCommentsText::censor($comment);

					// fix long words problem
					if ($word_maxlength > 0) {
						$comment = JCommentsText::fixLongWords($comment, $word_maxlength, ' ');
						if ($object_title != '') {
							$object_title = JCommentsText::fixLongWords($object_title, $word_maxlength, ' ');
						}
					}

					$author = JComments::getCommentAuthorName($row);

					$item = new JoomlaTuneFeedItem();
					$item->title = $object_title;
					$item->link = $object_link . '#comment-' . $row->id;
					$item->description = JText::sprintf('USER_FEED_ITEM_DESCRIPTION', $author, $comment);
					$item->source = $object_link;
					$item->pubDate = $row->date;
					$item->author = $author;
					$rss->addItem($item);
				}
			}

			$rss->display();

			unset($rows, $rss);
			exit();
		}
	}

	protected static function showNotFound()
	{
		header('HTTP/1.0 404 Not Found');
		JError::raiseError(404, 'JGLOBAL_RESOURCE_NOT_FOUND');
		exit(404);
	}
}