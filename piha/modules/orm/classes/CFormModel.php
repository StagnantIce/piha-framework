<?php

namespace piha\modules\orm\classes;
use piha\modules\core\classes\CHtml;
use piha\modules\core\classes\CForm;
use piha\modules\orm\classes\CModel;

class CFormModel extends CForm {

	protected $_model = null;
	protected $_models = array();
	protected $_prefix = '';
	protected $_form = null;

	public function __construct($options = array()) {
		$this->_model = CHtml::popOption($options, 'model') ?: null;
		$this->_models = CHtml::popOption($options, 'models') ?: array();
		$this->_values = $this->_model ? $this->_model->toArray() : array();
		if ($this->_model) {
			$this->_name = $this->getNameByModel($this->_model);
		}
		parent::__construct($options);
		$this->loadModels();
	}

	public function loadModels() {
		foreach($this->_models as $key => $mixed) {

			$this->_models[$key] = $mixed;

			if (is_array($mixed)) {
				$mixed = $mixed[0];
			}
			if ($mixed instanceof CModel) {
				$className = get_class($mixed);
			} else {
				$className = $mixed;
			}

			$name = $this->getNameByClass($className);

			if ($data = $this->getRequest($name)) {
				if (isset($data[0])) {
					$this->_models[$key] = array();
					foreach($data as $index => $dataModel) {
						$model = new $className;
						$model->fromArray($dataModel);
						$this->_models[$key][] = $model;
					}
				} else {
					$this->_models[$key] = new $className;
					$this->_models[$key]->fromArray($data);
				}
			}
		}
	}

	public static function getFieldGroup(&$options) {
		if (isset($options['name'])) {
			$name = $options['name'];
			if (strncmp($name,'[',1) === 0) {
				$options['name'] = substr($name, strpos($name, ']') + 1);
				return substr($name, 0, strpos($name, ']') + 1);
			}
		}
		return '';
	}

	public static function create($options = array()) {
		return new static($options);
	}

	public function getFieldNamePrefix() {
		if ($this->_prefix) {
			return $this->_prefix;
		}
		return parent::getFieldNamePrefix();
	}

	public function before(&$options) {
		$this->_prefix = '';
		$model = $this->_model;
		if ($fieldModel = CHtml::popOption($options, 'model')) {
			$this->_prefix = $this->getNameByModel($fieldModel);
			$model = $fieldModel;
		}

		if (isset($options['name']) && $model) {
			$group = self::getFieldGroup($options);
			$this->_prefix .= $group;

			if (!isset($options['value'])) {
				$key = $model->toVar(self::getFieldName($options));
				$value = $model->$key;
				if ($value instanceof CModel) {
					$value = $value->id;
				}
				if (is_array($value)) {
					foreach($value as &$v) {
						if ($v instanceof CModel) {
							$v = $v->id;
						}
					}
				}
				$this->_values[$options['name']] = $value;
			}
		}
		parent::before($options);
	}

	public function getNameByClass($className) {
		$className = explode('\\', $className);
		return end($className);
	}

	public function getNameByModel(CModel $model) {
		if ($class = get_class($model)) {
			return $this->getNameByClass($class);
		}
		throw new CException("CModel class '{$class}' not found.");
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
			$this->_model->merge($this->_values);
		}
	}

	public function getModel() {
		return $this->_model;
	}

	public function getModels($name) {
		return $this->_models[$name];
	}
}