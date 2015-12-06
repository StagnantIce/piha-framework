<?php

use piha\modules\user\classes\AAdminController;

class UserController extends AAdminController {

	public $layout = 'admin';

	public function modelClass() {
		return CUserModel::className();
	}
}