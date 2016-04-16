<?php

namespace piha\modules\core\classes;
use piha\modules\orm\classes\CModel;
use piha\CException;

class CForm {

	const TYPE_TEXT = 0;
	const TYPE_DATETIME = 1;
	const TYPE_INT = 2;
	const TYPE_FLOAT = 3;
	const TYPE_EMAIL = 4;
	const TYPE_URL = 5;

	protected $_html = null;
	protected $_values = array();
	protected $_errors = array();
	protected $_fields = array();
	protected $_name = 'Form';
	private $_method = '';
	private $_isSubmit = false;
	private $_isError = false;

	protected $_errorMessages = array();
	public $_defaultErrorMessages = array(
		'type' => 'Значение имеет неверный формат',
		'min'  => 'Значение слишком маленькое',
		'max'  => 'Значение слишком большое',
		'require' => 'Значение не может быть пустым'
	);

	public function __construct($options) {
		$this->_html = CHtml::popOption($options, 'html') ?: CHtml::create();
		$this->_values = CHtml::popOption($options, 'values') ?: array();
		$this->_name = CHtml::popOption($options, 'name') ?: $this->_name;
		$this->_method = CHtml::popOption($options, 'method') ?: 'POST';
		if (!in_array($this->_method, array('GET', 'POST'))) {
			throw new CException("Unknown form method");
		}
	}

	public function addError($error, $name = '') {
		$this->_isError = true;
		$this->_errors[$name][] = $error;
	}

	public function addErrorMessage($name, $typeError, $text) {
		$this->_errorMessages[$name][$typeError] = $text;
	}

	public function getError($name = '') {
		return isset($this->_errors[$name]) ? $this->_errors[$name] : '';
	}

	public function isError() {
		return $this->_isError;
	}

	public function setValues($values) {
		$this->_values = $values;
	}

	public function getRequest($name = null) {
		if (!$name) {
			$name = $this->_name;
		}
		if ($this->_method == 'POST') {
			return \Piha::request()->post($name);
		}
		if ($this->_method == 'GET') {
		    return \Piha::request()->get($name);
		}
		throw new CException("Form method not define");
	}

	public static function post($options) {
		$obj = new static(array_replace($options, array('method' => 'POST')));
		if ($postData = $obj->getRequest()) {
			$obj->setValues($postData);
			$obj->_isSubmit = true;
		}
		return $obj;
	}

	public static function get($options) {
		$obj = new static(array_replace($options, array('method' => 'GET')));
		if ($getData = $obj->getRequest()) {
			$obj->setValues($getData);
			$obj->_isSubmit = true;
		}
		return $obj;
	}

	public function getValue($name) {
		return isset($this->_values[$name]) ? $this->_values[$name] : null;
	}

	public function fieldEmail($name, $options = array()) {
		return $this->addField(self::TYPE_EMAIL, $name, array_replace(array('widget' => 'email'), $options));
	}

	public function fieldText($name, $options = array()) {
		return $this->addField(self::TYPE_TEXT, $name, array_replace(array('widget' => 'text'), $options));
	}

	public function fieldPassword($name, $options = array()) {
		return $this->addField(self::TYPE_TEXT, $name, array_replace(array('widget' => 'password'), $options));
	}

	public function fieldInt($name, $options = array()) {
		return $this->addField(self::TYPE_INT, $name, array_replace(array('widget' => 'number'), $options));
	}

	public function fieldFloat($name, $options = array()) {
		return $this->addField(self::TYPE_FLOAT, $name, array_replace(array('widget' => 'number'), $options));
	}

	public function fieldDateTime($name, $options = array()) {
		return $this->addField(self::TYPE_DATETIME, $name, array_replace(array('widget' => 'datetime'), $options));
	}

	public function fieldUrl($name, $options = array()) {
		return $this->addField(self::TYPE_URL, $name, array_replace(array('widget' => 'url'), $options));
	}

	private function addField($type, $name, $options) {
		$this->_fields[$name]['type'] = $type;
		foreach($options as $key => $option) {
			$this->_fields[$name]['require'] = false;
			if ($option == 'require') {
				$this->_fields[$name]['require'] = true;
				continue;
			}
			if (!in_array($key, array('min', 'max', 'widget', 'error'))) {
				throw new CException("Field option {$option} not defined.");
			}
			$this->_fields[$name][$key] = $option;
		}
		return $this;
	}

	public function getField($name, $options) {
		if (!isset($this->_fields[$name])) {
			throw new CException("Field form {$name} not found.");
		}
		if (!isset($this->_fields[$name]['widget'])) {
			throw new CException("Widget for {$name} field form not found.");
		}
		$widget = $this->_fields[$name]['widget'];
		if (method_exists($this, $widget)) {
			return $this->$widget(array_replace(array('name' => $name), $options));
		}
		throw new CException("Widget {$widget} not found.");
	}

	public function isValid() {
		foreach($this->_fields as $name => $field) {
			$errors = array();
			if (!isset($this->_values[$name])) {
				throw new CException("Field {$name} is not sent.");
			}
			$val = trim($this->_values[$name]);
			if ($val === '') {
				if ($field['require']) {
					$errors[] = 'require';
				} else {
					continue;
				}
			} else {
				switch($field['type']) {
					case self::TYPE_TEXT:
						if (isset($field['min'])) {
							if (mb_strlen($val) < (int)$field['min']) {
								$errors[] = 'min';
							}
						}
						if (isset($field['max'])) {
							if (mb_strlen($val) > (int)$field['max']) {
								$errors[] = 'max';
							}
						}
					break;
					case self::TYPE_INT:
						if (!is_numeric($val) || $val != (int)$val) {
							$errors[] = 'type';
						} else {
							$val = (int)$val;
							if (isset($field['min'])) {
								if ($val < (int)$field['min']) {
									$errors[] = 'min';
								}
							}
							if (isset($field['max'])) {
								if ($val > (int)$field['max']) {
									$errors[] = 'max';
								}
							}
						}
					break;
					case self::TYPE_FLOAT:
						if (!is_numeric($val) || $val != (float)$val) {
							$errors[] = 'type';
						} else {
							$val = (float)$val;
							if (isset($field['min'])) {
								if ($val < (float)$field['min']) {
									$errors[] = 'min';
								}
							}
							if (isset($field['max'])) {
								if ($val > (float)$field['max']) {
									$errors[] = 'max';
								}
							}
						}
					break;
					case self::TYPE_DATETIME:
						if(strtotime($val) === false) {
							$errors[] = 'type';
						} else {
							$val = date('Y-m-d H:i:s', $val);
							if (isset($field['min'])) {
								if (strtotime($val) < strtotime($field['min'])) {
									$errors[] = 'min';
								}
							}
							if (isset($field['max'])) {
								if (strtotime($val) > strtotime($field['max'])) {
									$errors[] = 'max';
								}
							}
						}
					break;
					case self::TYPE_EMAIL:
						if(filter_var($val, FILTER_VALIDATE_EMAIL) === false) {
							$errors[] = 'type';
						}
					break;
					case self::TYPE_URL:
						if(filter_var($val, FILTER_VALIDATE_URL) === false) {
							$errors[] = 'type';
						}
					break;
					default:
						$errors[] = 'type';
					break;
				}
			}
			if (count($errors) > 0) {
				$result = array();
				foreach($errors as $error) {
					if (isset($this->_errorMessages[$name][$error])) {
						$result[] = $this->_errorMessages[$name][$error];
					} else {
						$result[] = $this->_defaultErrorMessages[$error];
					}
				}
				foreach(array_unique($result) as $text) {
					$this->addError($text, $name);
				}
			}
		}
		return !$this->_isError;
	}

	public function isSubmit() {
		return $this->_isSubmit;
	}

	public static function getFieldName(&$options) {
		if (isset($options['name'])) {
			$name = $options['name'];
			$pos = strpos($name, '[');
			return $pos !== false ? substr($name,0,$pos) : $name;
		}
		return '';
	}

	public function getFieldNamePrefix() {
		return $this->_name;
	}

	public function before(&$options) {
		if (isset($options['name'])) {
			if (isset($this->_values[$options['name']])) {
				$options['value'] = $this->_values[$options['name']];
			}
			if (strpos($options['name'], '[') !== false) {
				$options['name'] = $this->getFieldNamePrefix() . '[' . self::getFieldName($options) . ']' . substr($options['name'], strpos($options['name'], '['));
			} else {
				$options['name'] = $this->getFieldNamePrefix()  . '['.$options['name'] .']';
			}
		}
	}

	public function beforeLabel(&$options) {
		$options['for'] = $this->_name . '['.$options['for'] .']';
	}

	public function start($options = array()) {
		$options = $options ?: array();
		$default = array(
			'action' => '',
			'name' => $this->_name,
			'method' => $this->_method
		);
		return $this->_html->form(array_replace($default, $options), false)->render(true);
	}

	public function label($options = array()) {
		$options = $options ?: array();
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
		if (isset($options['value']) && $options['value'] == 1) {
			$options['checked'] = 'checked';
		}
		return $this->_html->input(array_replace($options, array('type' => 'checkbox', 'value' => 1)))->render(true);
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
			if ($text !== null) {
				$this->_html->option($attr)->html(CHtml::safe($text))->end();
			}
		}
		return $this->_html->render(true);
	}

	public function end() {
		return $this->_html->end('form')->render(true);
	}
}