<?php

namespace piha\modules\user\models;

use piha\modules\orm\classes\CModel;

class CUserGroupModel extends CModel {

	public $_name = '{{user_group}}';

	public function getColumns() {
		return array(
			'ID'       => array('type' => 'pk'),
			'USER_ID'  => array('type' => 'int', 'object' => CUserModel::className()),
			'GROUP_ID' => array('type' => 'int', 'object' => CGroupModel::className())
		);
	}

	public function getRelations() {
		return array(
		    self::TYPE_ONE => array(
		    	'group' => array('GROUP_ID', CGroupModel::className()),
		    	'user' => array('USER_ID', CUserModel::className())
			)
		);
	}
}