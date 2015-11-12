<?php

namespace piha\modules\bootstrap\widgets;

use piha\CException;
use piha\modules\core\classes\CHtml as CBaseHtml;
use piha\modules\core\classes\CTool;


class CHtml extends CBaseHtml {

	const BUTTON = 'btn';
	const BUTTON_INFO = 'btn-info';
	const BUTTON_PRIMARY = 'btn-primary';
	const BUTTON_SUCCESS = 'btn-success';
	const BUTTON_WARNING = 'btn-warning';
	const BUTTON_DANGER = 'btn-danger';
	const BUTTON_INVERSE = 'btn-inverse';

	const TABLE = 'table';
	const TABLE_BORDERED = 'table-bordered';
	const TABLE_STRIPED = 'table-striped';

	public function a($options = array()) {
		$default = array(
			'class' => self::BUTTON
		);
		return parent::a(array_replace($default, $options));
	}

	public function table($options = array()) {
		$default = array(
			'class' => self::TABLE_BORDERED_STRIPED
		);
		return parent::table(array_replace($default, $options));
	}
}
