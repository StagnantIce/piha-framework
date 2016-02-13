<?php

namespace admin;

use piha\modules\user\classes\AAdminController;

class CategoryController extends AAdminController {

	public $layoutName = 'admin';

	public function modelClass() {
		return \CCategoryModel::className();
	}
}