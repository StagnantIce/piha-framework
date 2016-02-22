<?php

use piha\modules\orm\classes\CModel;

class CCategoryModel extends CModel {

	public $_name = '{{category}}';
	public $_label = 'Категории';
	public $childs;

	public function getColumns() {
	    return array(
			'ID'             => array('type' => 'pk'),
			'NAME'           => array('type' => 'string'),
			'CODE'           => array('type' => 'string'),
			'DESCRIPTION'    => array('type' => 'text'),
			'STATUS'         => array('type' => 'tinyint'),
			'SORT'           => array('type' => 'tinyint'),
			'IS_ACTIVE'      => array('type' => 'char', 'default' => 'Y'),
			'PARENT_ID'      => array('type' => 'int', 'default' => 0)
		);
	}

	public static function GetByParent($parent = 0) {
		return self::q()
			->where(array('PARENT_ID' => $parent))
			->order(array('SORT' => 'ASC'))
			->objects();
	}

	public static function GetTree($parent = 0) {
		$cats = self::GetByParent($parent);
		foreach($cats as $c) {
			$c->childs = self::GetTree($c->id);
		}
		return $cats;
	}
}