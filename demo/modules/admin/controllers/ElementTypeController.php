<?php

namespace admin;

use piha\modules\user\classes\AAdminController;

class ElementTypeController extends AAdminController {

	public $layoutName = 'admin';

	public function modelClass() {
		return \CElementTypeModel::className();
	}
}