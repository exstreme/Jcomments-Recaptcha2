<?php
/**
 * JComments plugin for SOBI Pro objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_sobipro extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();
	        $app = JFactory::getApplication();
	        if (!$app->isAdmin()) {
			$db = JFactory::getDBO();
			$query = "SELECT o.id, o.name, o.owner, o.parent, fd.baseData"
				. " FROM #__sobipro_object as o"
				. " LEFT JOIN #__sobipro_field_data AS fd ON o.id = fd.sid"
				. " JOIN #__sobipro_field AS f ON fd.fid = f.fid AND f.nid = 'field_name'"
				. " WHERE o.id = " . $id
				. " AND o.oType = 'entry'"
				;

			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {
				$sobiCore = JPATH_SITE.'/components/com_sobipro/lib/base/fs/loader.php';
				if (is_file($sobiCore)) {
					if (!defined( 'SOBIPRO')) {
						$ver = new JVersion();
						$ver = str_replace( '.', null, $ver->RELEASE );
					        if ($ver > '15') { $ver = '16'; }
						define('SOBI_CMS', 'joomla'. $ver);
						define('SOBIPRO', true);
						define('SOBI_TASK', 'task');
						define('SOBI_DEFLANG', JFactory::getLanguage()->getDefault());
						define('SOBI_ACL', 'front');
						define('SOBI_ROOT', JPATH_ROOT);
						define('SOBI_MEDIA', implode(DS, array(JPATH_ROOT, 'media', 'sobipro')));
						define('SOBI_MEDIA_LIVE', JURI::root().'/media/sobipro');
						define('SOBI_PATH', JPATH_ROOT.'/components/com_sobipro');
						define('SOBI_LIVE_PATH', 'components/com_sobipro');
						require_once (JPATH_ROOT.'/components/com_sobipro/lib/base/fs/loader.php');
					}

				        SPLoader::loadClass( 'sobi' );
				        SPLoader::loadClass( 'base.request' );
				        SPLoader::loadClass( 'base.object' );
				        SPLoader::loadClass( 'base.factory' );
				        SPLoader::loadClass( 'base.mainframe' );
				        SPLoader::loadClass( 'base.const' );
				        SPLoader::loadClass( 'cms.base.mainframe' );
				        SPLoader::loadClass( 'cms.base.lang' );

					$info->title = empty($row->name) ? (isset($row->baseData) ? $row->baseData : '') : $row->name;
					$info->access = NULL;
					$info->userid = $row->owner;

					$info->link = Sobi::Url(array('title' => $row->name, 'pid' => $row->parent, 'sid' => $row->id));
				}
			}
		}

		return $info;
	}
}