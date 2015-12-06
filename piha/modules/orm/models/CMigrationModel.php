<?php

namespace piha\modules\orm\models;
use piha\modules\orm\classes\CModel;


class CMigrationModel extends CModel {

	public $_name = '{{migration}}';

	public function getColumns() {
		return array(
			'ID'        => array('type' => 'pk'),
			'NAME'      => array('type' => 'string'),
			'TIMESTAMP' => array('type' => 'int')
		);
	}
}