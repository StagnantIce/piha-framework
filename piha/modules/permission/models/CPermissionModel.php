<?php

namespace piha\modules\permission\models;
use piha\modules\orm\classes\CModel;


class CPermissionModel extends CModel {

	public $_name = '{{permission}}';

	const TYPE_ROLE = 1;
	const TYPE_PERMISSION = 2;

	public function getColumns() {
		return array(
			'ID'          => array('type' => 'pk'),
			'NAME'        => array('type' => 'string'),
			'PARENT_ID'   => array('type' => 'int', 'default' => null),
			'DESCRIPTION' => array('type' => 'string'),
			'FILTER'      => array('type' => 'string'),
			'TYPE'        => array('type' => 'int')
		);
	}

	public function addChild($name) {
		if (!$this->id) {
			throw new CException("Add child to empty permission");
		}
		if ($p = CPermissionModel::Get(array('NAME' => $name))) {
			$p->parentId = $this->id;
			$p->save();
		}
		throw new CException("Permission with name '{$name}' not found");
	}
}