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
			'class' => 'form-horizontal'
		);
		return parent::__construct(array_replace($default, $options));
	}

	private function createLabel(&$options) {
		$this->_html->group()->div(array('class' => 'control-group'));
		$this->label(array('class' =>'control-label', 'for' => $options['name'], 'label' => isset($options['label']) ? $options['label'] : ''));
		$this->_html->div(array('class' => 'controls'));
		return $this;
	}

	public function selectGroup(Array $arr, $options = array()) {
		$this
				->createLabel($options)
				->select(array_replace($options, array('options' => $arr)));
		$this->_html->endGroup();
		return $this;
	}

	public function inputGroup($options = array()) {
		$this
				->createLabel($options)
				->text($options);
		$this->_html->endGroup();
		return $this;
	}
}