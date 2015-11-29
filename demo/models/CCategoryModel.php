<?php

use piha\modules\orm\classes\CModel;

class CCategoryModel extends CModel {

	public $_name = '{{category}}';
	public $_label = 'Категории';

	public function getColumns() {
	    return array(
			'ID'             => array('type' => 'pk'),
			'NAME'           => array('type' => 'string'),
			'CODE'           => array('type' => 'string'),
			'DESCRIPTION'    => array('type' => 'text'),
			'STATUS'         => array('type' => 'tinyint'),
			'IS_ACTIVE'      => array('type' => 'varchar', 'default' => 'Y'),
			'PARENT_ID'      => array('type' => 'int')
		);
	}
}