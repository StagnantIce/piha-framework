<?php

use piha\modules\core\classes\CController;

class CategoryController extends CController {

	public $layoutName = 'main';
	public $layoutClass = 'CMainLayout';

	public function actionView($id) {
		$elementCategories = CCategoryModel::GetTree($id);
		$this->render(
			'view',
			array(
				'elementCategories' => $elementCategories,
				'category' => CCategoryModel::StaticGet($id)
			)
		);
	}
}