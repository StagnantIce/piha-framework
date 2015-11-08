<?php

namespace piha\modules\core\classes;
use piha\modules\orm\classes\CModel;
use piha\CException;

class CForm {

	protected $_html = null;


	public function __construct($options) {
		$this->_html = CHtml::popOption('html') ?: CHtml::create();
		return $this->_html->form($options, false)->render(true);
	}

	public static function create($options) {
		return new static($options);
	}

	public function label($options) {
		$label = CHtml::popOption($options, 'label');
		return $this->_html->label($options)->html($label)->render(true);
	}


	public function text($options) {
		return $this->_html->input(array_replace($options, array('type' => 'text')))->render(true);
	}

	public function password($options) {
		return $this->_html->input(array_replace($options, array('type' => 'password')))->render(true);
	}

	public function submit($options) {
		return $this->_html->input(array_replace($options, array('type' => 'submit')))->render(true);
	}

	public function radio($options) {
		return $this->_html->input(array_replace($options, array('type' => 'radio')))->render(true);
	}

	public function checkbox($options) {
		return $this->_html->input(array_replace($options, array('type' => 'checkbox')))->render(true);
	}

	public function button($options) {
		return $this->_html->input(array_replace($options, array('type' => 'button')))->render(true);
	}

	public function color($options) {
		return $this->_html->input(array_replace($options, array('type' => 'color')))->render(true);
	}

	public function date($options) {
		return $this->_html->input(array_replace($options, array('type' => 'date')))->render(true);
	}

	public function datetime($options) {
		return $this->_html->input(array_replace($options, array('type' => 'datetime')))->render(true);
	}

	public function datetimeLocal($options) {
		return $this->_html->input(array_replace($options, array('type' => 'datetime-local')))->render(true);
	}

	public function email($options) {
		return $this->_html->input(array_replace($options, array('type' => 'email')))->render(true);
	}

	public function month($options) {
		return $this->_html->input(array_replace($options, array('type' => 'month')))->render(true);
	}

	public function number($options) {
		return $this->_html->input(array_replace($options, array('type' => 'number')))->render(true);
	}

	public function range($options) {
		return $this->_html->input(array_replace($options, array('type' => 'range')))->render(true);
	}

	public function search($options) {
		return $this->_html->input(array_replace($options, array('type' => 'search')))->render(true);
	}

	public function tel($options) {
		return $this->_html->input(array_replace($options, array('type' => 'tel')))->render(true);
	}

	public function time($options) {
		return $this->_html->input(array_replace($options, array('type' => 'time')))->render(true);
	}

	public function url($options) {
		return $this->_html->input(array_replace($options, array('type' => 'url')))->render(true);
	}

	public function week($options) {
		return $this->_html->input(array_replace($options, array('type' => 'week')))->render(true);
	}

	public function textarea($options) {
		$value = CHtml::safe(CHtml::popOption($options,'value'));
		return $this->_html->textarea()->html($value)->render(true);
	}

	public function select($options) {
		$optionTags = CHtml::popOption($options, 'options');
		$selected = (array)CHtml::popOption($options, 'value');
		$this->_html->select($options);
		foreach($optionTags as $value => $text) {
			$attr = array();
			if (in_array($value, $selected)) {
				$attr['selected'] = 'selected';
			}
			$attr['value'] = $value;
			$this->_html->option($attr)->html(CHtml::safe($text))->end();
		}
		return $this->_html->render(true);
	}

	public function endForm() {
		$this->_html->end('form');
	}
}