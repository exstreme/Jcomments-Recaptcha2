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

abstract class JCommentsImportAdapter
{
	protected $code;
	protected $extension;
	protected $name;
	protected $tableName;
	protected $author;
	protected $license;
	protected $licenseUrl;
	protected $siteUrl;

	abstract public function execute($language, $start = 0, $limit = 100);

	public function getCount()
	{
		$result = -1;

		if (!empty($this->tableName)) {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$query->from($db->quoteName($this->tableName));
			$db->setQuery($query);

			$result = $db->loadResult();
		}

		return $result;
	}

	public function getCode()
	{
		$result = $this->code;

		if (empty($result)) {
			$result = empty($this->extension) ? strtolower(preg_replace('/[^A-Z0-9_]/i', '', $this->name)) : $this->extension;
		}

		return $result;
	}

	public function getExtension()
	{
		return $this->extension;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getTableName()
	{
		return $this->tableName;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function getLicense()
	{
		return $this->license;
	}

	public function getLicenseUrl()
	{
		return $this->licenseUrl;
	}

	public function getSiteUrl()
	{
		return $this->siteUrl;
	}
}