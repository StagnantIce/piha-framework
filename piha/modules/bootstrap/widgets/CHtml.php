<?php

namespace piha\modules\bootstrap\widgets;

use piha\CException;
use piha\modules\core\classes\CHtml as CBaseHtml;
use piha\modules\core\classes\CTool;


class CHtml extends CBaseHtml {

	const BUTTON_NONE = '';
	const BUTTON_INFO = 'btn-info';
	const BUTTON_PRIMARY = 'btn-primary';
	const BUTTON_SUCCESS = 'btn-success';
	const BUTTON_WARNING = 'btn-warning';
	const BUTTON_DANGER = 'btn-danger';
	const BUTTON_INVERSE = 'btn-inverse';

	public function button($options) {
		$default = array(
			'href' => 'javascript:void(0)',
			'class' => 'btn'
		);
		if (isset($options['class'])) {
			$default['class'] .= ' '.$options['class'];
			unset($options['class']);
		}
		return parent::a(array_replace($default, $options));
	}

	private function group($options) {
		$stack = $this->popStack();
		if (isset($options['label'])) {
			$this
			->div(array('class' => 'control-group'))
				->label(array('class' =>'control-label', 'for' => $options['name']))
					->text($options['label'])
				->end()
				->div(array('class' => 'controls'));
			unset($options['label']);
		}
		return $options;
	}

	public function selectGroup($options) {
		$stack = $this->popStack();
		$options = $this->group($options);
		parent::select($options);
		$options = CTool::fromArray($options, 'options', array());
		$htmlOptions = array();
		foreach($options as $key => $text) {
			$htmlOptions[] = array('value' => $key, 'text' => $text);
		}
		$this
			->each($htmlOptions)
				->option();
		return $this->endStack($stack);
	}

	public function inputGroup($options) {
		$stack = $this->popStack();
		$options = $this->group($options);
		parent::input($options, true);
		return $this->endStack($stack);
	}

	public function form($options) {
		$default = array(
			'action' => '',
			'method' => 'POST',
			'class' => 'form-horizontal'
		);
		return parent::form(array_replace($default, $options));
	}
}
