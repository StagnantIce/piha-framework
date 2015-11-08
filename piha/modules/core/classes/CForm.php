<?php

namespace piha\modules\core\classes;
use piha\modules\orm\classes\CModel;
use piha\CException;

class CForm {

	protected $_html = null;


	public function __construct($options) {
		$this->_html = CHtml::popOption($options, 'html') ?: CHtml::create();
	}

	public static function create($options) {
		return new static($options);
	}

	public function before(&$options) {

	}

	public function beforeLabel(&$options) {

	}

	public function start($options) {
		return $this->_html->form($options, false)->render(true);
	}

	public function label($options) {
		$this->beforeLabel($options);
		$label = CHtml::popOption($options, 'label');
		return $this->_html->label($options)->html($label)->render(true);
	}

	public function text($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'text')))->render(true);
	}

	public function password($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'password')))->render(true);
	}

	public function submit($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'submit')))->render(true);
	}

	public function radio($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'radio')))->render(true);
	}

	public function checkbox($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'checkbox')))->render(true);
	}

	public function button($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'button')))->render(true);
	}

	public function color($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'color')))->render(true);
	}

	public function date($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'date')))->render(true);
	}

	public function datetime($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'datetime')))->render(true);
	}

	public function datetimeLocal($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'datetime-local')))->render(true);
	}

	public function email($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'email')))->render(true);
	}

	public function month($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'month')))->render(true);
	}

	public function number($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'number')))->render(true);
	}

	public function range($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'range')))->render(true);
	}

	public function search($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'search')))->render(true);
	}

	public function tel($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'tel')))->render(true);
	}

	public function time($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'time')))->render(true);
	}

	public function url($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'url')))->render(true);
	}

	public function week($options) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'week')))->render(true);
	}

	public function textarea($options) {
		$this->before($options);
		$value = CHtml::safe(CHtml::popOption($options,'value'));
		return $this->_html->textarea()->html($value)->render(true);
	}

	public function select($options) {
		$this->before($options);
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

	public function end() {
		return $this->_html->end('form')->render(true);
	}
}