<?php

namespace piha\modules\bootstrap\widgets;
use piha\modules\orm\classes\CModel;


class CForm extends CHtml {

	public function __construct(CModel $model) {
		$this->_model = $model;
	}

	public static function create(CModel $model) {
		return new static($model);
	}

	private $lastLabel = '';
	private $lastSelect = '';

	protected function start($name, $options, $close=false) {
		$className = get_class($this->_model);
		if ($name === 'label') {
			if(isset($options['for'])) {
				$this->_lastLabel = $this->_model->getLabel($options['for']);
			} else {
				$this->_lastLabel = '';
			}
		}
		if ($name === 'text' && $this->_lastLabel) {
			$options = $options ?: $this->_lastLabel;
			$this->_lastLabel = '';
		}

		if ($name === 'input' && !isset($options['value'])) {
			$key = $this->_model->toVar($options['name']);
			$options['value'] = $this->_model->$key;
		}

		if ($name === 'select') {
			$this->lastSelect = trim($options['name'], '[]');
		}

		if ($name == 'option' && $this->lastSelect) {
			$key = $this->_model->toVar($this->lastSelect);
			if (in_array($options['value'], (array)$this->_model->$key)) {
				$options['selected'] = 'selected';
			}
		}

		if (is_array($options) && isset($options['name'])) {
			if (mb_strpos($options['name'], '[') !== false) {
				$options['name'] = $className . '['.mb_substr($options['name'],0, mb_strpos($options['name'], '[')).']' . mb_substr($options['name'], mb_strpos($options['name'], '['));
			} else {
				$options['name'] = $className . '['.$options['name'] .']';
			}
		}

		return parent::start($name, $options, $close);
	}
}