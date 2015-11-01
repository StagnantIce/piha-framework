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

	protected function start($name, $options, $close=false) {
		$className = get_class($this->_model);
		if (isset($options['name'])) {
			if (strpos($options['name'], '[') !== false) {
				$options['name'] = $className . '['.substr($options['name'],0, strpos($options['name'], '[')).']' . substr($options['name'], strpos($options['name'], '['));
			} else {
				$options['name'] = $className . '['.$options['name'] .']';
			}
		}
		if ($name === 'label' && !isset($options['text']) && isset($options['for'])) {
			$options['text'] = $this->_model->getLabel($options['for']);
		}
		return parent::start($name, $options, $close);
	}
}