<?php

use piha\modules\core\classes\CController;

class CategoryController extends CController {

	public $layoutName = 'main';

	public function actionView($id) {
		$categories = CCategoryModel::GetTree($id);
		$this->render('view', array('categories' => $categories, 'category' => CCategoryModel::StaticGet($id)));
	}
}