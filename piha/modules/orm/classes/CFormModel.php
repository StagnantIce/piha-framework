<?php

namespace piha\modules\orm\classes;
use piha\modules\core\classes\CHtml;
use piha\modules\core\classes\CForm;

class CFormModel extends CForm {

	protected $_model = null;
	protected $_form = null;

	public function __construct($options = array()) {
		$this->_model = CHtml::popOption($options, 'model') ?: null;
		$this->_values = $this->_model ? $this->_model->toArray() : array();
		if ($this->_model && $class = get_class($this->_model)) {
			$class = explode('\\', $class);
			$class = end($class);
			$this->_name = $class;
		}
		parent::__construct($options);
	}

	public static function create($options = array()) {
		return new static($options);
	}

	public function before(&$options) {
		$model = CHtml::popOption($options, 'model') ?: $this->_model;
		if (isset($options['name']) && $model) {

			if (!isset($options['value'])) {
				$key = $model->toVar(self::getFieldName($options));
				$this->_values[$options['name']] = $model->$key;
			}
		}
		parent::before($options);
	}

	public function beforeLabel(&$options) {
		$model = CHtml::popOption($options, 'model') ?: $this->_model;
		if ($model) {
			$className = get_class($model);
			if ($className && isset($options['for']) && !$options['label']) {
				$options['label'] = $className::getLabel($options['for']);
			}
		}
		parent::beforeLabel($options);
	}

	public function setValues($values) {
		parent::setValues($values);
		if ($this->_model) {
			$this->_model->fromArray($this->_values);
		}
	}

	public function getModel() {
		return $this->_model;
	}
}