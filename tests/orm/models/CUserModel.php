<?php

use piha\modules\orm\classes\CModel;

class CUserModel extends CModel {

	public $_name = '{{user}}';

	public $_columns = array(
		'ID'   => array('type' => 'pk'),
		'NAME' => array('type' => 'string')
	);
}