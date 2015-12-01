<?php

namespace piha\modules\permission\classes;
use piha\CException;

class CPermission {

	public function assign($user_id, $name) {
		if ($pid = CPermissionModel::GetID(array('NAME' => $name))) {
			return CPermissionUserModel::Insert(array('USER_ID' => (int)$user_id, 'PERMISSION_ID' => $pid));
		}
		throw new CException("Permission with name '{$name}' not found");
	}

	public function addRole($name) {
		return CPermissionModel::Insert(array('NAME' => $name, 'TYPE' => CPermissionModel::TYPE_ROLE));
	}

	public function addPermission($name) {
		return CPermissionModel::Insert(array('NAME' => $name, 'TYPE' => CPermissionModel::TYPE_PERMISSION));
	}

	public function hasPermission($user_id, $name) {
		if ($pid = CPermissionModel::GetID(array('NAME' => $name))) {
			return CPermissionUserModel::Exists(array('USER_ID' => (int)$user_id, 'PERMISSION_ID' => $pid));
		}
		throw new CException("Permission with name '{$name}' not found");
	}
}