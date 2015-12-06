<?php

use piha\modules\orm\classes\CModel;

class CElementModel extends CModel {

	public $_name = '{{element}}';
	public $_label = 'Элементы';

	public function getColumns() {
	    return array(
			'ID'        => array('type' => 'pk'),
			'NAME'      => array('type' => 'string'),
			'CODE'      => array('type' => 'string'),
			'DESCRIPTION'      => array('type' => 'text'),
			'STATUS'    => array('type' => 'tinyint'),
			'IS_ACTIVE' => array('type' => 'char', 'default' => 'Y'),
			'TAGS'      => array('type' => 'string'),
			'TYPE_ID'   => array('type' => 'int', 'object' => CElementTypeModel::className()),
		);
	}
}