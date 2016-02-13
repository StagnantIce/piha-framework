<?php

namespace admin;

use piha\modules\user\classes\AAdminController;

class ElementController extends AAdminController {

	public $layoutName = 'admin';

	public function modelClass() {
		return \CElementModel::className();
	}
}