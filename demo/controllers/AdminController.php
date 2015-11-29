<?php

use piha\modules\core\classes\CController;
use piha\modules\bootstrap3\widgets\CForm;


class AdminController extends CController {

	public $layout = 'main';
	public function beforeAction() {
		if (!\Piha::user()->is('admin')) {
			$this->redirect('auth/login');
		}
	}

	public function actionIndex() {

	}

}