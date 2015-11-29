<?php

namespace piha\modules\permission\models;
use piha\modules\orm\classes\CModel;


class CPermissionModel extends CModel {

	public $_name = '{{permission}}';

	public function getColumns() {
		return array(
			'ID'          => array('type' => 'int'),
			'NAME'        => array('type' => 'string'),
			'PARENT_ID'   => array('type' => 'int', 'default' => 'NULL'),
			'DESCRIPTION' => array('type' => 'string'),
			'FILTER'      => array('type' => 'string'),
			'TYPE'        => array('type' => 'int')
		);
	}
}