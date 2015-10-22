<?php

//namespace controller;

use piha\modules\core\classes\CController;

class HomeController extends CController {

	public $layout = 'main';
	public function actionIndex() {
		$this->render();
	}
}