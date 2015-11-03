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

	protected function start($name, $options=null, $close=false) {
		if ($name === 'text' && is_string($options)) {
			$this->html .= $options;
			return $this;
		}

		if ($name === 'end'  && !$options) {
			$this->html .= '</'.array_pop($this->stack).'>';
			return $this;
		}

		$options = $options ?: array();

		if (!is_array($options)) {
			throw new CException("CHtml function expect Array.");
		}

		$text = '';
		$attrs = array();
		foreach($options as $attr => $value) {
			$attrs[] = $attr . '="'.$value.'"';
		}
		$this->html .= '<'.$name. ($attrs ? ' '. implode(' ', $attrs) : '') . ($close ? '/':'') .'>';
		if (!$close) {
			$this->stack[] = $name;
		}
		return $this;
	}

	public function render($return=false) {
		while($this->eachIndex >= 0) {
			$this->endEach();
		}
		$this->endStack();
		$html = $this->html;
		$this->html = '';
		if (!$return) {
			echo $html;
		}
		return $return ? $html : $this;
	}

	public function popStack() {
		$stack = $this->stack;
		$this->stack = array();
		return $stack;
	}

	public function getParent() {
		return end($this->stack);
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
		if ($this->eachIndex >= 0) {
			$this->eachMethods[$this->eachIndex][] = array($method, $ps);
			return $this;
		}
		array_unshift($ps, $method);
		return call_user_func_array(array($this, 'start'), $ps);
	}

	public function each(Array $items) {
		if ($this->eachIndex >= 0) {
			$this->eachMethods[$this->eachIndex][] = null;
		}
		$this->eachIndex++;
		$this->eachItems[$this->eachIndex] = $items;
		return $this;
	}

	public function endEach($index = null, Array $params = null) {
		if ($index == null) {
			$this->eachIndex--;
			if ($this->eachIndex >= 0) {
				return $this;
			}
		}
		$index = $index ?: 0;
		$params = $params ?: array();
		foreach($this->eachItems[$index] as $eachItem) {
			$prop = $params;
			$prop[] = $eachItem;
			foreach($this->eachMethods[$index] as $eachMethod) {
				if ($eachMethod === null) {
					$this->endEach($index + 1, $prop);
				} else {
					list($method, $ps) = $eachMethod;
					$attrs = array();
					$close = false;
					if (count($ps) > 0) {
						$attrs = self::ExtractValue($ps[0], $prop);
						if (isset($ps[1])) {
							$close = $ps[1];
						}
					}
					$this->start($method, $attrs, $close);
				}
			}
		}

		if ($index == null && $this->eachIndex == -1) {
			$this->eachMethods = array();
			$this->eachItems = array();
		}
		return $this;
	}

	public static function extractValue($mixed, Array $params = null) {
		if (is_callable($mixed)) {
			return call_user_func_array($mixed, $params);
		} else if (is_string($mixed)) {
			$data = new \stdclass();
			$data->params = $params;
			foreach($params as $eachParams) {
				foreach($eachParams as $key => $value) {
					if (!is_numeric($key) && $key !== 'params') {
						$data->$key = $value;
					}
				}
			}
			return eval('return ' . rtrim($mixed, ';') . ';');
		} else if (is_array($mixed)) {
			return $mixed;
		}
		throw new CException("Error extract value.");
	}

	public function plainArray(Array $arr, $keyName='id', $valName='value') {
		foreach($arr as $key => &$val) {
			if (is_string($val)) {
				$val = array($valName => $val);
			}
			if (!is_array($val) || isset($val[$keyName])) {
				throw new CException("Expect array or string array values or key values exists");
			}
			$val[$keyName] = $key;
		}
		return array_values($arr);
	}
}
