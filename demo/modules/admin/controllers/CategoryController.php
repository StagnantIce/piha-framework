<?php

use piha\modules\user\classes\AAdminController;

class CategoryController extends AAdminController {

	public $layout = 'admin';

	public function modelClass() {
		return CCategoryModel::className();
	}
}