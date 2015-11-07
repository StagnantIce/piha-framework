<?php

namespace piha\modules\core\classes;
use piha\modules\orm\classes\CModel;
use piha\CException;

class CForm {

	protected $_model = null;
	protected $_html = null;


	public function __construct($options) {
		$this->_html = CHtml::create();
		$this->_model = $this->_html->popOption($options, 'model') ?: null;
		$this->_html->form($options);
	}

	public static function create($options) {
		return new static($options);
	}

	public function before(&$options) {
		$model = $this->_html->popOption($options, 'model') ?: $this->_model;

		if (isset($options['name']) && $model) {
			$className = get_class($model);

			if (!isset($options['value'])) {
				$key = $model->toVar($options['name']);
				$options['value'] = $model->$key;
			}

			if (mb_strpos($options['name'], '[') !== false) {
				$options['name'] = $className . '['.mb_substr($options['name'],0, mb_strpos($options['name'], '[')).']' . mb_substr($options['name'], mb_strpos($options['name'], '['));
			} else {
				$options['name'] = $className . '['.$options['name'] .']';
			}
		}
		return $this->_html;
	}

	public function label($options) {
		$label = $this->_html->popOption($options, 'label');
		$model = $this->_html->popOption($options, 'model');
		$className = get_class($model ?: $this->_model);
		if ($className && isset($options['for']) && !$label) {
			$label = $className::getLabel($options['for']);
		}
		$this->_html->label($options)->html($label)->end();
		return $this;
	}

	public function text($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'text')))->end();
		return $this;
	}

	public function password($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'password')))->end();
		return $this;
	}

	public function submit($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'submit')))->end();
		return $this;
	}

	public function radio($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'radio')))->end();
		return $this;
	}

	public function checkbox($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'checkbox')))->end();
		return $this;
	}

	public function button($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'button')))->end();
		return $this;
	}

	public function color($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'color')))->end();
		return $this;
	}

	public function date($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'date')))->end();
		return $this;
	}

	public function datetime($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'datetime')))->end();
		return $this;
	}

	public function datetimeLocal($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'datetime-local')))->end();
		return $this;
	}

	public function email($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'email')))->end();
		return $this;
	}

	public function month($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'month')))->end();
		return $this;
	}

	public function number($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'number')))->end();
		return $this;
	}

	public function range($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'range')))->end();
		return $this;
	}

	public function search($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'search')))->end();
		return $this;
	}

	public function tel($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'tel')))->end();
		return $this;
	}

	public function time($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'time')))->end();
		return $this;
	}

	public function url($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'url')))->end();
		return $this;
	}

	public function week($options) {
		$this->before($options)->input(array_replace($options, array('type' => 'week')))->end();
		return $this;
	}

	public function textarea($options) {
		$value = $this->before($options)->safe($this->_html->popOption($options,'value'));
		$this->_html->textarea()->html($value)->end();
		return $this;
	}

	public function select($options) {
		$optionTags = $this->_html->before($options)->popOption($options, 'options');
		$selected = (array)$this->_html->popOption($options, 'value');
		$this->_html->select($options);
		foreach($optionTags as $value => $text) {
			$attr = array();
			if (in_array($value, $selected)) {
				$attr['selected'] = 'selected';
			}
			$attr['value'] = $value;
			$this->_html->option($attr)->html($this->_html->safe($text))->end();
		}
		$this->_html->end();
		return $this;
	}

	public function end() {
		$this->_html->render();
	}
}