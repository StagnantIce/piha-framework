<?php

use piha\modules\user\classes\AAdminController;

class ElementController extends AAdminController {

	public $layout = 'admin';

	public function modelClass() {
		return CElementModel::className();
	}
}