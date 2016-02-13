<?php

namespace admin;

use piha\modules\user\classes\AAdminController;

class UserController extends AAdminController {

	public $layoutName = 'admin';

	public function modelClass() {
		return \CUserModel::className();
	}
}