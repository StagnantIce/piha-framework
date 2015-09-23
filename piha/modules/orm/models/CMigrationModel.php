<?php

class CMigrationModel extends CModel {

	public $_name = '{{migration}}';

	public $_columns = array(
		'ID'        => array('type' => 'int'),
		'NAME'      => array('type' => 'string'),
		'TIMESTAMP' => array('type' => 'timestamp')
	);
}