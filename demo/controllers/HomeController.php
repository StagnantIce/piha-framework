<?php

use piha\modules\core\classes\CController;

class HomeController extends CController {

	public $layoutName = 'main';
	
	public function actionIndex() {
		$categories = CCategoryModel::GetTree();
		$this->render('index', array(
			'categories' => $categories
		));
	}
}