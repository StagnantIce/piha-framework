<?php

namespace piha\modules\core\classes;
use piha\modules\orm\classes\CModel;
use piha\CException;

class CForm {

	protected $_html = null;
	protected $_values = array();
	protected $_name = 'Form';
	private $_method = '';
	private $_submit = false;


	public function __construct($options) {
		$this->_html = CHtml::popOption($options, 'html') ?: CHtml::create();
		$this->_values = CHtml::popOption($options, 'values') ?: array();
		$this->_name = CHtml::popOption($options, 'name') ?: $this->_name;
		$this->_method = CHtml::popOption($options, 'method') ?: 'POST';
		if (!in_array($this->_method, array('GET', 'POST'))) {
			throw new CException("Unknown form method");
		}
	}

	public static function post($options) {
		$obj = new static(array_replace($options, array('method' => 'POST')));
		if ($postData = \Piha::request()->post($obj->_name)) {
			$obj->_values = $postData;
			$obj->_submit = true;
		}
		return $obj;
	}

	public static function get($options) {
		$obj = new static(array_replace($options, array('method' => 'GET')));
		if ($getData = \Piha::request()->get($obj->_name)) {
			$obj->_values = $getData;
			$obj->_submit = true;
		}
		return $obj;
	}

	public function getValue($name) {
		return isset($this->_values[$name]) ? $this->_values[$name] : null;
	}

	public function isValid() {
		return $this->_submit && $this->_values;
	}

	public function isSubmit() {
		return $this->_submit;
	}

	public static function getFieldName($options) {
		if (isset($options['name'])) {
			$name = $options['name'];
			$pos = strpos($name, '[');
			return $pos !== false ? substr($name,0,$pos) : $name;
		}
		return '';
	}

	public function before(&$options) {
		if (isset($options['name'])) {
			if (isset($this->_values[$options['name']])) {
				$options['value'] = $this->_values[$options['name']];
			}

			if (strpos($options['name'], '[') !== false) {
				$options['name'] = $this->_name . '[' . self::getFieldName($options) . ']' . substr($options['name'], strpos($options['name'], '['));
			} else {
				$options['name'] = $this->_name . '['.$options['name'] .']';
			}
		}
	}

	public function beforeLabel(&$options) {

	}

	public function start($options = array()) {
		$default = array(
			'action' => '',
			'name' => $this->_name,
			'method' => $this->_method
		);
		return $this->_html->form(array_replace($default, $options), false)->render(true);
	}

	public function label($options = array()) {
		$this->beforeLabel($options);
		$label = CHtml::popOption($options, 'label');
		return $this->_html->label($options)->html($label)->render(true);
	}

	public function text($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'text')))->render(true);
	}

	public function password($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'password')))->render(true);
	}

	public function submit($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'submit')))->render(true);
	}

	public function radio($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'radio')))->render(true);
	}

	public function checkbox($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'checkbox')))->render(true);
	}

	public function button($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'button')))->render(true);
	}

	public function color($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'color')))->render(true);
	}

	public function date($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'date')))->render(true);
	}

	public function datetime($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'datetime')))->render(true);
	}

	public function datetimeLocal($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'datetime-local')))->render(true);
	}

	public function email($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'email')))->render(true);
	}

	public function month($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'month')))->render(true);
	}

	public function number($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'number')))->render(true);
	}

	public function range($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'range')))->render(true);
	}

	public function search($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'search')))->render(true);
	}

	public function tel($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'tel')))->render(true);
	}

	public function time($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'time')))->render(true);
	}

	public function url($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'url')))->render(true);
	}

	public function week($options = array()) {
		$this->before($options);
		return $this->_html->input(array_replace($options, array('type' => 'week')))->render(true);
	}

	public function textarea($options = array()) {
		$this->before($options);
		$value = CHtml::safe(CHtml::popOption($options,'value'));
		return $this->_html->textarea($options)->html($value)->render(true);
	}

	public function select($options = array()) {
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