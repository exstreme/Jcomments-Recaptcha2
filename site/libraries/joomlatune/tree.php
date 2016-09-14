<?php
/**
 * Simple tree class
 *
 * @version 1.0
 * @package JoomlaTune.Framework
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

// Check for double include
if (!defined('JOOMLATUNE_TREE')) {
	
	define('JOOMLATUNE_TREE', 1);
	
	class JoomlaTuneTree
	{
		/* @var array children list */
		var $children = array();
		
		/**
		 * Class constructor
		 *
		 * @param	array	$items	array of all objects (objects must contain id and parent fields)
		 * @return	JoomlaTuneTree
		 */
		function __construct( $items )
		{
			$this->children = array();
			
			foreach ($items as $v) {
				$pt = $v->parent;
				$list = isset($this->children[$pt]) ? $this->children[$pt] : array();
				array_push($list, $v);
				$this->children[$pt] = $list;
			}
		}
		
		function _buildTree( $id, $list = array(), $maxlevel = 9999, $level = 0, $number = '' )
		{
			if (isset($this->children[$id]) && $level <= $maxlevel) {
				if ($number != '') {
					$number .= '.';
				}
				
				$i = 1;
				
				foreach ($this->children[$id] as $v) {
					$id = $v->id;
					$list[$id] = $v;
					$list[$id]->level = $level;
					$list[$id]->number = $number . $i;
					$list[$id]->children = isset($this->children[$id]) ? count($this->children[$id]) : 0;
					$list = $this->_buildTree($id, $list, $maxlevel, $level + 1, $list[$id]->number);
					$i++;
				}
			}
			return $list;
		}
		
		function _getDescendants( $id, $list = array(), $maxlevel = 9999, $level = 0 )
		{
			if (isset($this->children[$id]) && $level <= $maxlevel) {
				foreach ($this->children[$id] as $v) {
					$id = $v->id;
					$list[] = $v->id;
					$list = $this->_getDescendants($id, $list, $maxlevel, $level + 1);
				}
			}
			return $list;
		}
		
		/**
		 * Return objects tree
		 *
		 * @param int	$id node id (by default node id is 0 - root node)
		 * @return array
		 */
		function get( $id = 0 )
		{
			return $this->_buildTree($id);
		}
		
		/**
		 * Return children items for given node or empty array for empty children list
		 *
		 * @param int	$id node id (by default node id is 0 - root node)
		 * @return array
		 */
		function children( $id = 0 )
		{
			return isset($this->children[$id]) ? $this->children[$id] : array();
		}
		
		/**
		 * Return array with descendants id for given node
		 *
		 * @param int $id node id (by default node id is 0 - root node)
		 * @return array
		 */
		function descendants( $id = 0 )
		{
			return $this->_getDescendants($id);
		}
	}
} // end of double include check