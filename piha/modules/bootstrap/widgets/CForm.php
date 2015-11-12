<?php

namespace piha\modules\bootstrap\widgets;
use piha\modules\orm\classes\CFormModel;
use piha\modules\core\classes\CHtml as CHtmlBase;


class CForm extends CFormModel {

	const HORIZONTAL = 'form-horizontal';
	const INLINE = 'form-inline';

	public function __construct($options) {
		$default = array(
			'html' => CHtmlBase::create()
		);
		return parent::__construct(array_replace($default, $options));
	}

	public function start($options) {
		$default = array(
			'class' => self::HORIZONTAL
		);
		return parent::start(array_replace($default, $options));
	}


	private function createControl($label, $control) {
		return $this->_html
			->div(array('class' => 'control-group'))
				->html($label)
				->div(array('class' => 'controls'))
					->html($control)
				->end('div')
			->end('div')
		->render(true);
	}

	public function selectGroup($options = array()) {
		$label = $this->label(array('class' =>'control-label', 'for' => $options['name'], 'label' => CHtmlBase::popOption($options, 'label')));
		return $this->createControl($label, $this->select($options));
	}

	public function inputGroup($options = array()) {
		$label = $this->label(array('class' =>'control-label', 'for' => $options['name'], 'label' => CHtmlBase::popOption($options, 'label')));
		return $this->createControl($label, $this->text($options));
	}
}