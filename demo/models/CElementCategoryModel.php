<?php

use piha\modules\orm\classes\CModel;

class CElementCategoryModel extends CModel {

	public $_name = '{{element_category}}';
	public $_label = 'Элементы категорий';

	public function getColumns() {
	    return array(
			'ID'        => array('type' => 'pk'),
			'ELEMENT_ID'=> array('type' => 'int', 'object' => CElementModel::className()),
			'CATEGORY_ID'=> array('type' => 'int', 'object' => CCategoryModel::className()),
		);
	}
}