<?php

namespace piha\modules\core\classes;
use piha\CException;

class CHtml {

	private $html = '';
	private $stack = array();
	private $eachIndex = -1;
	private $eachItems = array();
	private $eachMethods = array();

	public static function create() {
		return new static();
	}

	protected function start($name, $options, $close=false) {
		if ($name === 'end') {
			$this->html .= '</'.array_pop($this->stack).'>';
			return $this;
		}
		if ($name === 'text') {
			$this->html .= $text;
			return $this;
		}

		$text = '';
		$attrs = array();
		foreach($options as $attr => $value) {
			if ($attr === 'text') {
				$text = $value;
				continue;
			}
			$attrs[] = $attr . '="'.$value.'"';
		}
		$this->html .= '<'.$name. ($attrs ? ' '. implode(' ', $attrs) : '') . ($close && $text ==='' ? '/':'') .'>' . $text;
		if (!$close) {
			$this->stack[] = $name;
		} else if ($text !== '') {
			$this->html .= '</'.$name.'>';
		}
		return $this;
	}

	public function render() {
		while($this->eachIndex >= 0) {
			$this->endEach();
		}
		$this->endStack();
		echo $this->html;
		$this->html = '';
		return $this;
	}

	public function popStack() {
		$stack = $this->stack;
		$this->stack = array();
		return $stack;
	}

	public function endStack($stack = array()) {
		if ($this->eachIndex >= 0) {
			throw new CException("Stack in each not allowed");
		}
		while($this->stack) {
			$this->end();
		}
		$this->stack = $stack;
		return $this;
	}

	public function __call($method, $ps) {
		if ($this->eachItems[$this->eachIndex]) {
			$this->eachMethods[$this->eachIndex] = array($method, $ps);
			return $this;
		}
		array_unshift($ps, $method);
		return call_user_func_array(array($this, 'start'), $ps);
	}

	public function each(Array $arr) {
		$this->eachIndex++;
		$this->eachItems[$this->eachIndex] = $arr;
		return $this;
	}

	public function endEach() {
		foreach($this->eachItems[$this->eachIndex] as $eachItem) {
			foreach($this->eachMethods[$this->eachIndex] as $eachMethod) {
				list($method, $ps) = $eachMethod;
				$attrs = array();
				$close = false;
				if (count($ps) > 0) {
					if (count($ps) > 0 && is_callable($ps[0])) {
						$attrs = $ps[0]($eachItem);
					} else if (is_array($ps[0])) {
						$attrs = array_replace($ps, $eachItem);
					}
					if (isset($ps[1])) {
						$close = $ps[1];
					}
				}
				$this->start($method, $attrs, $close);
			}
		}
		$this->eachMethods[$this->eachIndex] = array();
		$this->eachItems[$this->eachIndex] = array();
		$this->eachIndex--;
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
