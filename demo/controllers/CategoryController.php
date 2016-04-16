<?php

use piha\modules\core\classes\CController;

class CategoryController extends CController {

	public $layoutName = 'main';

	public function actionView($id) {
		$categories = CCategoryModel::GetTree();
		$elementCategories = CCategoryModel::GetTree($id);
		$this->render('view', array('elementCategories' => $elementCategories, 'categories' => $categories, 'category' => CCategoryModel::StaticGet($id)));
	}
}