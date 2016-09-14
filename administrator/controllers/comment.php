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

class JCommentsControllerComment extends JCommentsControllerForm
{
	public function getModel($name = 'Comment', $prefix = 'JCommentsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function deleteReportAjax()
	{
		$id = $this->input->getInt('id');
		$model = $this->getModel();
		$table = $model->getTable('report');
		$result = -1;

		if ($table->load($id)) {
			$commentId = (int) $table->commentid;

			if ($model->deleteReport($id)) {
				$reports = $model->getReports($commentId);
				$result = count($reports);
			}
		}
		echo $result;

		JFactory::getApplication()->close();
	}
}