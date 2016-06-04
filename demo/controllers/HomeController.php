<?php

use piha\modules\core\classes\CController;

class HomeController extends CController {

	public $layoutName = 'main';
	public $layoutClass = 'CMainLayout';
	
	public function actionIndex() {
		$this->render();
	}
}