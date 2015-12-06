<?php

namespace piha\modules\bootstrap3\widgets;
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

	public function start($options = array()) {
		$error = '';
		if ($error = $this->getError()) {
			$error = $this->_html->p(array('class' => 'text-danger'))->html(implode('', $error))->render(true);
		}
		return $error.parent::start($options);
	}

	private function createControl($options, $control) {
		$label = $this->label(array('for' => $options['name'], 'label' => CHtmlBase::popOption($options, 'label')));
		$classes = array('form-group');
		if ($this->isError()) {
			$classes[] = 'has-error';
		}
		$this->_html
			->div(array('class' => $classes))
				->html($label)
				->html($control);
		if ($error = $this->getError($options['name'])) {
			$this->_html->span(array('class' => 'help-block'))->html(implode('', $error))->end('span');
		}
		return $this->_html->end('div')->render(true);
	}

	public function selectGroup($options = array()) {
		return $this->createControl($label, $this->select($options));
	}

	public function textGroup($options = array()) {
		$defaults = array(
			'class' => 'form-control'
		);
		return $this->createControl($options, $this->text(array_replace($defaults, $options)));
	}

	public function emailGroup($options = array()) {
		$defaults = array(
			'class' => 'form-control'
		);
		return $this->createControl($options, $this->email(array_replace($defaults, $options)));
	}

	public function passwordGroup($options = array()) {
		$defaults = array(
			'class' => 'form-control'
		);
		return $this->createControl($options, $this->password(array_replace($defaults, $options)));
	}

	public function fieldGroup($name, $options = array()) {
		$defaults = array(
			'class' => 'form-control'
		);
		$options['name'] = $name;
		return $this->createControl($options, $this->getField($name, array_replace($defaults, $options)));
	}

	public function submit($options = array()) {
		$defaults = array(
			'class' => array(CHtml::BUTTON, CHtml::BUTTON_PRIMARY)
		);
		return parent::submit(array_replace($defaults, $options));
	}

}