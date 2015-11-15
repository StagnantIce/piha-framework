<?php

use piha\modules\orm\classes\CModel;

class CGroupModel extends CModel {

	public $_name = '{{group}}';
	public $_label = 'Группы';

	public function getColumns() {
	    return array(
			'ID' => array('type' => 'pk'),
			'NAME' => array('type' => 'string'),
			'CODE' => array('type' => 'string')
		);
	}
}