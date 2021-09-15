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
use Joomla\Utilities\ArrayHelper;
class JCommentsModelCustomBBCode extends JCommentsModelForm
{
	public function getTable($type = 'CustomBBCode', $prefix = 'JCommentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_jcomments.custombbcode', 'custombbcode', array('control' => 'jform',
																					'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		if (!$this->canEditState((object)$data)) {
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_jcomments.edit.custombbcode.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data)
	{
		$table = $this->getTable();
		$pkName = $table->getKeyName();
		$pk = (!empty($data[$pkName])) ? $data[$pkName] : (int)$this->getState($this->getName() . '.id');

		try {
			$old_simple_pattern = '';
			$old_simple_replacement_html = '';
			$old_simple_replacement_text = '';

			if ($pk > 0) {
				$table->load($pk);

				$old_simple_pattern = $table->simple_pattern;
				$old_simple_replacement_html = $table->simple_replacement_html;
				$old_simple_replacement_text = $table->simple_replacement_text;
			}

			if (!$table->bind($data)) {
				$this->setError($table->getError());

				return false;
			}

			$acl = is_array($data['button_acl']) ? $data['button_acl'] : array();
			if (version_compare(JVERSION, '4.0', 'lt')){
				JArrayHelper::toInteger($acl);
			}else {
				ArrayHelper::toInteger($acl);
			}
			$table->button_acl = implode(',', $acl);

			$table->name = trim(strip_tags($table->name));
			$table->button_open_tag = trim(strip_tags($table->button_open_tag));
			$table->button_close_tag = trim(strip_tags($table->button_close_tag));
			$table->button_title = trim(strip_tags($table->button_title));
			$table->button_prompt = trim(strip_tags($table->button_prompt));
			$table->button_image = trim(strip_tags($table->button_image));
			$table->button_css = trim(strip_tags($table->button_css));

			if (get_magic_quotes_gpc() == 1) {
				$table->pattern = stripslashes($table->pattern);
				$table->replacement_html = stripslashes($table->replacement_html);
				$table->replacement_text = stripslashes($table->replacement_text);
				$table->simple_pattern = stripslashes($table->simple_pattern);
				$table->simple_replacement_html = stripslashes($table->simple_replacement_html);
				$table->simple_replacement_text = stripslashes($table->simple_replacement_text);
			}

			if ($table->simple_replacement_text == '') {
				$table->simple_replacement_text = strip_tags($table->simple_replacement_html);
			}

			if ($table->simple_pattern != '' && $table->simple_replacement_html != '') {
				$tokens = array();
				$tokens['TEXT'] = array('([\w0-9-\+\=\!\?\(\)\[\]\{\}\/\&\%\*\#\.,_ ]+)' => '$1');
				$tokens['SIMPLETEXT'] = array('([\A-Za-z0-9-\+\.,_ ]+)' => '$1');
				$tokens['IDENTIFIER'] = array('([\w0-9-_]+)' => '$1');
				$tokens['NUMBER'] = array('([0-9]+)' => '$1');
				$tokens['ALPHA'] = array('([A-Za-z]+)' => '$1');

				$pattern = preg_quote($table->simple_pattern, '#');
				$replacement_html = $table->simple_replacement_html;
				$replacement_text = $table->simple_replacement_text;

				$m = array();
				$pad = 0;

				if (preg_match_all('/\{(' . implode('|', array_keys($tokens)) . ')[0-9]*\}/im', $table->simple_pattern, $m)) {
					foreach ($m[0] as $n => $token) {
						$token_type = $m[1][$n];

						reset($tokens[strtoupper($token_type)]);
						list($match, $replace) = each($tokens[strtoupper($token_type)]);

						$repad = array();
						if (preg_match_all('/(?<!\\\\)\$([0-9]+)/', $replace, $repad)) {
							$repad = $pad + sizeof(array_unique($repad[0]));
							$replace = preg_replace('/(?<!\\\\)\$([0-9]+)/e', "'\${' . (\$1 + \$pad) . '}'", $replace);
							$pad = $repad;
						}

						$pattern = str_replace(preg_quote($token, '#'), $match, $pattern);
						$replacement_html = str_replace($token, $replace, $replacement_html);
						$replacement_text = str_replace($token, $replace, $replacement_text);
					}
				}

				// if simple pattern not changed but pattern changed - clear simple
				if ($old_simple_pattern != $table->simple_pattern || $table->pattern == '') {
					$table->pattern = $pattern;
				}

				// if simple replacement not changed but pattern changed - clear simple
				if ($old_simple_replacement_html != $table->simple_replacement_html || $table->replacement_html == '') {
					$table->replacement_html = $replacement_html;
				}

				// if simple replacement not changed but pattern changed - clear simple
				if ($old_simple_replacement_text != $table->simple_replacement_text || $table->replacement_text == '') {
					$table->replacement_text = $replacement_text;
				}
			}

			if (!$table->check()) {
				$this->setError($table->getError());

				return false;
			}

			if (!$table->store()) {
				$this->setError($table->getError());

				return false;
			}

			$this->cleanCache('com_jcomments');

		} catch (Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		if (isset($table->$pkName)) {
			$this->setState($this->getName() . '.id', $table->$pkName);
		}

		return true;
	}
}