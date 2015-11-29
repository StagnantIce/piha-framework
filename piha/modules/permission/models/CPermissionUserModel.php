<?php

namespace piha\modules\permission\models;
use piha\modules\orm\classes\CModel;


class CPermissionUserModel extends CModel {

	public $_name = '{{permission_user}}';

	public function getColumns() {
		return array(
			'ID'              => array('type' => 'int'),
			'PERMISSION_ID'   => array('type' => 'int', 'object' => CPermissionModel::className()),
			'USER_ID'         => array('type' => 'string', 'object' => CPermissionModule::Config('modelClass')),
		);
	}
}