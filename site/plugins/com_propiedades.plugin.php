<?php
/**
 * JComments plugin for Property (http://www.com-property.com/)
 *
 * @version 2.0
 * @package JComments
 * @author Sergey M. Litvinov (smart@joomlatune.ru)
 * @copyright (C) 2006-2013 by Sergey M. Litvinov (http://www.joomlatune.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class jc_com_propiedades extends JCommentsPlugin
{
	function getObjectTitle($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT titulo_producto, id_producto FROM #__propiedades_productos WHERE id_producto = ' . $id );
		return $db->loadResult();
	}

	function getObjectLink($id)
	{
		$_Itemid = self::getItemid('com_propiedades');

		$db = JFactory::getDbo();
		$query =  ' SELECT CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(":", p.id_producto, p.alias) ELSE p.id_producto END as slug,'
			. ' CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(":", t.id_categoria, t.alias) ELSE t.id_categoria END as catslug,'
			. ' FROM #__propiedades_productos AS p '				
			. ' LEFT JOIN #__propiedades_categorias AS t ON t.id_categoria = p.id_categoria_producto '
			. ' WHERE p.id_producto = ' . $id
			;
		$db->setQuery($query);

		$row = new StdClass;

		if (JCOMMENTS_JVERSION == '1.5') {
			$row = $db->loadObject();
		} else {
			$db->loadObject($row);
		}

		$link = 'index.php?option=com_propiedades&view=propiedad&catid='.$row->catslug.'&id='.$row->slug;
		$link .= ($_Itemid > 0) ? ('&Itemid=' . $_Itemid) : '';
		$link = JRoute::_( $link );

		return $link;
	}

	function getObjectOwner($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery( 'SELECT publicador_id FROM #__propiedades_productos WHERE id_producto = ' . $id );
		$userid = $db->loadResult();
		
		return $userid;
	}
}