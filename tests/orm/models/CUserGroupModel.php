<?php

use piha\modules\orm\classes\CModel;

class CUserGroupModel extends CModel {

	public $_name = '{{user_group}}';

	public $_columns = array(
		'ID'       => array('type' => 'pk'),
		'USER_ID'  => array('type' => 'int',    'object' => 'CUserModel'),
		'GROUP_ID' => array('type' => 'int', 'object' => 'CGroupModel')
	);
}