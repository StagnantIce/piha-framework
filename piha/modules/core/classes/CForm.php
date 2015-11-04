<?php

namespace piha\modules\core\classes;
use piha\modules\orm\classes\CModel;
use piha\CException;

class CForm {

	protected $_model = null;
	protected $_html = null;
	private $_lastLabel = '';
	private $_lastSelect = '';

	public function __construct(CModel $model, CHtml $html = null) {
		$this->_model = $model;
		$this->_html = $html ?: CHtml::create();
	}

	public static function create(CModel $model, CHtml $html = null) {
		return new static($model, $html);
	}

	public function __call($method, $ps) {
		$options = $ps ? $ps[0]: array();

		if ($method === 'label') {
			if(isset($options['for'])) {
				$this->_lastLabel = $this->_model->getLabel($options['for']);
			} else {
				$this->_lastLabel = '';
			}
		}
		if ($method === 'text' && $this->_lastLabel) {
			$options = $options ?: $this->_lastLabel;
			$this->_lastLabel = '';
		}

		if ($method === 'input' && !isset($options['value'])) {
			$key = $this->_model->toVar($options['name']);
			$options['value'] = $this->_model->$key;
		}

		if ($method === 'select') {
			$this->_lastSelect = trim($options['name'], '[]');
		}

		if ($method == 'option' && $this->_lastSelect) {
			$key = $this->_model->toVar($this->_lastSelect);
			if (in_array($options['value'], (array)$this->_model->$key)) {
				$options['selected'] = 'selected';
			}
		}

		if (is_array($options) && isset($options['name'])) {
			$className = get_class($this->_model);
			if (mb_strpos($options['name'], '[') !== false) {
				$options['name'] = $className . '['.mb_substr($options['name'],0, mb_strpos($options['name'], '[')).']' . mb_substr($options['name'], mb_strpos($options['name'], '['));
			} else {
				$options['name'] = $className . '['.$options['name'] .']';
			}
		}
		$this->_html->$method($options);
		return $this;
	}
}