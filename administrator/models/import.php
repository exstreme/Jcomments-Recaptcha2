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

class JCommentsModelImport extends JCommentsModelLegacy
{
	protected $_adapters = array();
	protected static $initialised = false;

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function getItems()
	{
		$app = JFactory::getApplication();
		$prefix = $app->getCfg('dbprefix');
		$tables = JFactory::getDbo()->getTableList();
		$adapters = $this->getAdapters();

		$items = array();

		foreach ($adapters as $adapter) {
			$tableName = $adapter->getTableName();
			$tableName = str_replace('#__', $prefix, $tableName);

			if (in_array($tableName, $tables)) {
				$item = new StdClass;
				$item->code = $adapter->getCode();
				$item->name = $adapter->getName();
				$item->author = $adapter->getAuthor();
				$item->license = $adapter->getLicense();
				$item->licenseUrl = $adapter->getLicenseUrl();
				$item->siteUrl = $adapter->getSiteUrl();
				$item->comments = $adapter->getCount();

				$items[] = $item;
			}
		}

		return $items;
	}

	public function import($source, $language, $start = 0, $limit = 100)
	{
		$adapters = $this->getAdapters();

		if (isset($adapters[$source])) {
			if ($start == 0) {
				$this->deleteComments(strtolower($source));
			}

			$adapter = $adapters[$source];
			$adapter->execute($language, $start, $limit);

			$total = $adapter->getCount();
			$count = $this->getCommentsCount($source);

			if ($total == $count) {
				$this->updateParent($source);
			}

			$this->setState($this->getName() . '.total', $total);
			$this->setState($this->getName() . '.count', $count);

			return true;
		}

		return false;
	}

	function updateParent($source)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->update(array($db->quoteName('#__jcomments') . ' AS c1', $db->quoteName('#__jcomments') . ' AS c2'));
		$query->set('c1.parent = c2.id');
		$query->where('c1.source = c2.source');
		$query->where('c1.id <> c2.id');
		$query->where('c1.parent <> 0');
		$query->where('c1.parent = c2.source_id');
		$query->where('c1.source = ' . $db->quote($source));

		$db->setQuery($query);
		$db->execute();
	}

	function getCommentsCount($source)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__jcomments'));
		$query->where($db->quoteName('source') . '=' . $db->quote($source));
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	function deleteComments($source)
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->delete();
		$query->from($db->quoteName('#__jcomments'));
		$query->where($db->quoteName('source') . '=' . $db->quote($source));
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true);
		$query->delete();
		$query->from($db->quoteName('#__jcomments_subscriptions'));
		$query->where($db->quoteName('source') . '=' . $db->quote($source));
		$db->setQuery($query);
		$db->execute();
	}

	function getAdapters()
	{
		if (!self::$initialised) {
			require_once JPATH_COMPONENT . '/classes/import/adapter.php';

			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');

			$this->_adapters = array();
			$path = JPATH_COMPONENT . '/classes/import/adapters';

			$files = JFolder::files($path, '\.php');
			foreach ($files as $file) {
				require_once $path . '/' . $file;

				$name = JFile::stripExt($file);
				$className = 'JCommentsImport' . ucfirst($name);

				if (class_exists($className)) {
					$adapter = new $className;
					$this->_adapters[$adapter->getCode()] = $adapter;
				}
			}
		}

		return $this->_adapters;
	}
}