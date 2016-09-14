<?php
/**
 * JComments plugin for joomleague (http://www.joomleague.net) objects support
 *
 * @version 2.3
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_joomleague extends JCommentsPlugin
{
	function getObjectInfo($id, $language = null)
	{
		$info = new JCommentsObjectInfo();

		$routerHelper = JPATH_SITE.'/components/com_joomleague/helpers/route.php';
		if (is_file($routerHelper)) {
			require_once($routerHelper);

			$db = JFactory::getDBO();
			$query = "SELECT m.id as matchid,
								t1.short_name t1name,
								t2.short_name t2name,
								m.projectteam1_id,
								m.projectteam2_id,
								m.team1_result,
								m.team2_result,
								m.modified_by,
								r.project_id,
								p.name as projectname"
				. " FROM #__joomleague_match as m"
				. " LEFT JOIN #__joomleague_round as r ON r.id=m.round_id"
				. " INNER JOIN #__joomleague_project_team AS pt1 ON m.projectteam1_id=pt1.id"
				. " INNER JOIN #__joomleague_project_team AS pt2 ON m.projectteam2_id=pt2.id"
				. " INNER JOIN #__joomleague_team AS t1 ON pt1.team_id=t1.id"
				. " INNER JOIN #__joomleague_team AS t2 ON pt2.team_id=t2.id"
				. " INNER JOIN #__joomleague_project AS p ON pt1.project_id=p.id"
				. " WHERE m.id = " . $id;
			$db->setQuery($query);
			$row = $db->loadObject();

			if (!empty($row)) {

				if (!is_null($row->team1_result) && (!is_null($row->team1_result))) {
					$info->title = $row->projectname." ".$row->t1name." vs. ".$row->t2name." ".$row->team1_result.":".$row->team2_result;
				} else {
					$info->title = $row->projectname." ".$row->t1name." vs. ".$row->t2name;
				}

				$info->userid = $row->modified_by;
				$info->link = JRoute::_(JoomleagueHelperRoute::getMatchReportRoute($row->project_id, $row->matchid));
			}
		}

		return $info;
	}
}