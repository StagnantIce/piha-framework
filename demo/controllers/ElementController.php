<?php

use piha\modules\core\classes\CController;

class ElementController extends CController {

	public $layoutName = 'main';
	public $layoutClass = 'CMainLayout';

	public function actionView($id) {
		$categories = CCategoryModel::GetTree();
		$this->render('view', array('element' => CElementModel::StaticGet($id)));
	}

}