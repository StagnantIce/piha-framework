<?php

namespace piha\modules\core\classes;


class CHtml {

	private $html = '';
	private $stack = array();
	private $each = array();

	public static function create() {
		return new static();
	}

	protected function start($name, $options, $close=false) {
		$text = '';
		$attrs = array();
		foreach($options as $attr => $value) {
			if ($attr === 'text') {
				$text = $value;
				continue;
			}
			$attrs[] = $attr . '="'.$value.'"';
		}
		$this->html .= '<'.$name. ' '. implode(' ', $attrs) . ($close && $text ==='' ? '/':'') .'>' . $text;
		if (!$close) {
			$this->stack[] = $name;
		} else if ($text !== '') {
			$this->html .= '</'.$name.'>';
		}
		return $this;
	}

	public function popStack() {
		$stack = $this->stack;
		$this->stack = array();
		return $stack;
	}

	public function endStack($stack = array()) {
		while($this->stack) {
			$this->end();
		}
		$this->stack = $stack;
		return $this;
	}

	public function end() {
		$this->html .= '</'.array_pop($this->stack).'>';
		return $this;
	}

	public function text($text) {
		$this->html .= $text;
		return $this;
	}

	public function render() {
		$this->endStack();
		echo $this->html;
		$this->html = '';
		return $this;
	}

	public function __call($method, $ps) {
		if ($this->each) {
			foreach($this->each as $forItem) {
				call_user_func_array(array($this, 'start'), array($method, $forItem));
			}
			$this->each = array();
			return $this;
		}
		array_unshift($ps, $method);
		return call_user_func_array(array($this, 'start'), $ps);
	}

	public function each(Array $arr) {
		$this->each = $arr;
		return $this;
	}

	public function arrayToAttributes(Array $arr, $keyAttr, $valAttr) {
		$result = array();
		foreach($arr as $key => $val) {
			$result[] = array($keyAttr => $key, $valAttr => $val);
		}
		return $result;
	}
}
