<?php

namespace piha\modules\bootstrap3\widgets;

use piha\CException;
use piha\modules\core\classes\CHtml as CBaseHtml;


class CHtml extends CBaseHtml {

	const BUTTON = 'btn btn-default';
	const BUTTON_INFO = 'btn-info';
	const BUTTON_PRIMARY = 'btn-primary';
	const BUTTON_SUCCESS = 'btn-success';
	const BUTTON_WARNING = 'btn-warning';
	const BUTTON_DANGER = 'btn-danger';
	const BUTTON_INVERSE = 'btn-inverse';

	const TABLE = 'table';
	const TABLE_BORDERED = 'table-bordered';
	const TABLE_STRIPED = 'table-striped';

	const ICON = 'glyphicon';
	const ICON_PENCIL = 'glyphicon-pencil';
	const ICON_REMOVE = 'glyphicon-remove';
	const ICON_WHITE = 'glyphicon-inverse';

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

	public function icon($class) {
		return parent::i(array('class' => array_merge(array(self::ICON), (array)$class)));
	}

	public function button($url, $class) {
		return parent::a(array('href' => $url, 'class' => array_merge(array(CHtml::BUTTON), (array)$class)));
	}
}
