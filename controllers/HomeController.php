<?php

class HomeController extends CController {

	public $layout = 'main';
	public function actionIndex() {
		$this->render();
	}
}