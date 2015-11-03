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

	protected function start($name, $options, $close=false) {
		$className = get_class($this->_model);
		if (is_array($options) && isset($options['name'])) {
			if (mb_strpos($options['name'], '[') !== false) {
				$options['name'] = $className . '['.mb_substr($options['name'],0, mb_strpos($options['name'], '[')).']' . mb_substr($options['name'], mb_strpos($options['name'], '['));
			} else {
				$options['name'] = $className . '['.$options['name'] .']';
			}
		}
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
		return parent::start($name, $options, $close);
	}
}