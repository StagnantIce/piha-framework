<?php

namespace piha\modules\permission\classes;
use piha\CException;
use piha\modules\permission\models\CPermissionModel;
use piha\modules\permission\models\CPermissionUserModel;


class CPermission {

	public static function assign($user_id, $name) {
		if ($pid = CPermissionModel::GetID(array('NAME' => $name))) {
			return CPermissionUserModel::GetOrCreate(array('USER_ID' => (int)$user_id, 'PERMISSION_ID' => $pid));
		}
		throw new CException("Permission with name '{$name}' not found");
	}

	public static function addRole($name) {
		return CPermissionModel::GetOrCreate(array('NAME' => $name, 'TYPE' => CPermissionModel::TYPE_ROLE));
	}

	public static function addPermission($name) {
		return CPermissionModel::GetOrCreate(array('NAME' => $name, 'TYPE' => CPermissionModel::TYPE_PERMISSION));
	}

	public static function hasPermission($user_id, $name) {
		if ($pid = CPermissionModel::GetID(array('NAME' => $name))) {
			return CPermissionUserModel::Exists(array('USER_ID' => (int)$user_id, 'PERMISSION_ID' => $pid));
		}
		throw new CException("Permission with name '{$name}' not found");
	}
}