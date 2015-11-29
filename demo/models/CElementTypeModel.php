<?php

use piha\modules\orm\classes\CModel;

class CElementTypeModel extends CModel {

	public $_name = '{{element_type}}';
	public $_label = 'Типы элементов';

	public function getColumns() {
	    return array(
			'ID'        => array('type' => 'pk'),
			'NAME'      => array('type' => 'string'),
			'CODE'      => array('type' => 'string')
		);
	}
}