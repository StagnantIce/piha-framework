<?php

use piha\modules\core\classes\CController;

class CategoryController extends CController {

	public $layoutName = 'main';

	public function actionView() {
		$this->render('view');
	}
}