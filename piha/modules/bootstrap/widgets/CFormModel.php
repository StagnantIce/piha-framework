<?php

namespace piha\modules\bootstrap\widgets;
use piha\modules\core\orm\CFormModel as CFormModelBase;


class CFormModel extends CFormModelBase {

	public function __construct($options) {
		$default = array(
			'action' => '',
			'method' => 'POST',
			'class' => 'form-horizontal',
			'form' => CForm::create($options)
		);
		return parent::__construct(array_replace($default, $options));
	}
}