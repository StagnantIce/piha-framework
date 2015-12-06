<?php

use piha\modules\core\classes\CController;

class HomeController extends CController {

	public $layout = 'main';
	public function actionIndex() {
		$categories = CCategoryModel::GetTree();
		$this->render('index', array(
			'categories' => $categories
		));
	}
}