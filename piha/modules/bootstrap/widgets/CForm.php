<?php

namespace piha\modules\bootstrap\widgets;
use piha\modules\orm\classes\CModel;
use piha\modules\core\classes\CForm as CFormBase;
use piha\modules\core\classes\CHtml as CHtmlBase;


class CForm extends CFormBase {

	public function __construct(CModel $model, CHtmlBase $html = null) {
		$this->_model = $model;
		$this->_html = $html ?: CHtml::create();
	}

	public static function create(CModel $model, CHtmlBase $html = null) {
		return new static($model, $html);
	}

	private function createLabel(&$options) {
		$this
			->div(array('class' => 'control-group'))
				->label(array('class' =>'control-label', 'for' => $options['name']))
					->text($options['label'])
				->end()
				->div(array('class' => 'controls'));
		unset($options['label']);
		return $this;
	}

	public function selectGroup(Array $arr, $options = array()) {
		return $this
			->group()
				->createLabel($options)
				->select($options)
					->each(CHtml::plainArray($arr, 'value', 'text'))
						->option('array("value" => $data->value)')
							->text('$data->text')
						->end()
					->endEach()
			->endGroup();
	}

	public function inputGroup($options = array()) {
		return $this
			->group()
				->createLabel($options)
				->input($options, true)
			->endGroup($stack);
	}

	public function form($options = array()) {
		$default = array(
			'action' => '',
			'method' => 'POST',
			'class' => 'form-horizontal'
		);
		return parent::form(array_replace($default, $options));
	}
}