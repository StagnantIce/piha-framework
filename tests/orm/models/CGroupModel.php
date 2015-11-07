<?php

use piha\modules\orm\classes\CModel;

class CGroupModel extends CModel {

	public $_name = '{{group}}';

	public $_columns = array(
		'ID' => array('type' => 'pk'),
		'NAME' => array('type' => 'string'),
		'CODE' => array('type' => 'string')
	);
}