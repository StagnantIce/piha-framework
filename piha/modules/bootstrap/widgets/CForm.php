<?php

namespace piha\modules\bootstrap\widgets;
use piha\modules\orm\classes\CModel;
use piha\modules\core\classes\CForm as CFormBase;
use piha\modules\core\classes\CHtml as CHtmlBase;


class CForm extends CFormBase {

	public function __construct($options) {
		$default = array(
			'action' => '',
			'method' => 'POST',
			'class' => 'form-horizontal',
			'html' => CHtmlBase::create()
		);
		return parent::__construct(array_replace($default, $options));
	}


	private function createControl($label, $control) {
		return $this->_html
			->div(array('class' => 'control-group'))
				->html($label)
				->div(array('class' => 'controls'))
					->html($control)
				->end();
			->render(true);
	}

	public function selectGroup($options = array()) {
		$label = $this->label(array('class' =>'control-label', 'for' => $options['name']);
		return $this->createControl($label, $this->select($options));
	}

	public function inputGroup($options = array()) {
		$label = $this->label(array('class' =>'control-label', 'for' => $options['name']);
		return $this->createControl($label, $this->text($options));
	}
}