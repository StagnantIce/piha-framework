<?php

use piha\modules\core\classes\CController;

class ElementController extends CController {

	public $layoutName = 'main';

	public function actionView($id) {
		$categories = CCategoryModel::GetTree();
		$this->render('view', array('categories' => $categories, 'element' => CElementModel::StaticGet($id)));
	}

}