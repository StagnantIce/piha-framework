<?php

namespace piha\modules\orm\classes;
use piha\modules\core\classes\CHtml;
use piha\modules\core\classes\CForm;

class CFormModel extends CForm {

	protected $_model = null;
	protected $_form = null;

	public function __construct($options = array()) {
		$this->_model = CHtml::popOption($options, 'model') ?: null;
		parent::__construct($options);
	}

	public static function create($options = array()) {
		return new static($options);
	}

	public function before(&$options) {
		$model = CHtml::popOption($options, 'model') ?: $this->_model;
		if (isset($options['name']) && $model) {
			$className = get_class($model);

			if (!isset($options['value'])) {
				$key = $model->toVar(trim($options['name'], '[]'));
				$options['value'] = $model->$key;
			}

			if (mb_strpos($options['name'], '[') !== false) {
				$options['name'] = $className . '['.mb_substr($options['name'],0, mb_strpos($options['name'], '[')).']' . mb_substr($options['name'], mb_strpos($options['name'], '['));
			} else {
				$options['name'] = $className . '['.$options['name'] .']';
			}
		}
	}

	public function beforeLabel(&$options) {
		$model = CHtml::popOption($options, 'model') ?: $this->_model;
		$className = get_class($model);
		if ($className && isset($options['for']) && !$options['label']) {
			$options['label'] = $className::getLabel($options['for']);
		}
	}
}