<?php

namespace piha\modules\permission\classes;
use piha\CException;

class CPermission {

	public function assign($id, $name) {
		if ($pid = CPermissionModel::GetID(array('NAME' => $name))) {
			return CPermissionUserModel::Insert(array('USER_ID' => (int)$id, 'PERMISSION_ID' => $pid));
		}
		throw new CException("Permission with name '{$name}' not found");
	}

	public function addRole($name) {

	}
}