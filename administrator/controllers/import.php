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

class JCommentsControllerImport extends JCommentsControllerLegacy
{
	function display($cachable = false, $urlparams = array())
	{
		$this->input->set('view', 'import');

		parent::display($cachable, $urlparams);
	}

	public function getModel($name = 'Import', $prefix = 'JCommentsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function modal()
	{
		$source = $this->input->get('source', '', 'string');
		$language = $this->input->get('language', '', 'string');

		if (empty($language)) {
			$languages = JLanguageHelper::getLanguages();
			$language = isset($languages[0]->lang_code) ? $languages[0]->lang_code : '';
		}

		$this->input->set('view', 'import');
		$this->input->set('layout', 'modal');

		$model = $this->getModel();
		$model->setState($model->getName() . '.source', $source);
		$model->setState($model->getName() . '.language', $language);

		$view = $this->getView('import', 'html');
		$view->setModel($model, true);
		$view->setLayout('modal');
		$view->modal();
	}

	public function ajax()
	{
		$source = $this->input->get('source');
		$language = $this->input->get('language');
		$start = $this->input->getInt('start', 0);
		$limit = $this->input->getInt('limit', 100);

		if (!empty($source)) {
			$model = $this->getModel();
			$return = $model->import($source, $language, $start, $limit);

			if ($return !== false) {
				$count = $model->getState($model->getName() . '.count');
				$total = $model->getState($model->getName() . '.total');
				$percent = ceil(($count / max($total, 1)) * 100);
				$percent = min($percent, 100);

				$start = min($start + $limit, $total);

				$data = array('count' => (int) $count,
							  'total' => (int) $total,
							  'percent' => $percent,
							  'start' => (int) $start,
							  'source' => $source,
							  'language' => $language,
				);

				if ($count == $total) {
					$data['message'] = JText::sprintf('A_IMPORT_DONE', $count);
				}

				echo json_encode($data);
			} else {
				$data['message'] = JText::_('A_IMPORT_FAILED');
				echo json_encode($data);
			}
		}

		JFactory::getApplication()->close();
	}
}