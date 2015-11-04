<?php

namespace piha\modules\bootstrap\widgets;

use piha\CException;
use piha\modules\core\classes\CHtml as CBaseHtml;
use piha\modules\core\classes\CTool;


class CHtml extends CBaseHtml {

	const BUTTON_NONE = 'btn';
	const BUTTON_INFO = 'btn btn-info';
	const BUTTON_PRIMARY = 'btn btn-primary';
	const BUTTON_SUCCESS = 'btn btn-success';
	const BUTTON_WARNING = 'btn btn-warning';
	const BUTTON_DANGER = 'btn btn-danger';
	const BUTTON_INVERSE = 'btn btn-inverse';

	const TABLE_BORDERED = 'table table-bordered';
	const TABLE_STRIPED = 'table table-striped';
	const TABLE_BORDERED_STRIPED = 'table table-bordered table-striped';

	public function a($options = array()) {
		$default = array(
			'href' => 'javascript:void(0)'
		);
		return parent::a(array_replace($default, $options));
	}

	public function input($options = array()) {
		$default = array(
			'type' => 'text'
		);
		return parent::input(array_replace($default, $options));
	}

	public function table($options = array()) {
		$default = array(
			'class' => self::TABLE_BORDERED_STRIPED
		);
		return parent::table(array_replace($default, $options));
	}
}
