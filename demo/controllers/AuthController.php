<?php

use piha\modules\core\classes\CController;


class AuthController extends CController {

	public $layout = 'main';

	public function actionLogin() {
		$this->render('login', array('login' => 'Noname'));
	}
}