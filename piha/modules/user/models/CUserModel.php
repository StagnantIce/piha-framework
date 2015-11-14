<?php

namespace piha\modules\user\models;

use piha\modules\orm\classes\CModel;

class CUserModel extends CModel {

	public $_name = '{{user}}';

	public function getColumns() {
		return array(
			'ID'        => array('type' => 'pk'),
			'LOGIN'     => array('type' => 'string'),
			'PASSWORD'  => array('type' => 'string'),
			'EMAIL'     => array('type' => 'string'),
			'PHONE'     => array('type' => 'string'),
		);
	}

	public function getRelations() {
		return array(
		    self::TYPE_MANY => array(
		    	'groups' => array('ID', CUserGroupModel::className(), CGroupModel::className())
			)
		);
	}
}