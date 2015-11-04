<?php

namespace piha\modules\core\classes;
use piha\CException;

class CHtml {

	/* @var string $html - string for output */
	private $html = '';

	/* @var array $group - group for all open tags */
	private $group = array();

	/* @var array $groups - save group for group */
	private $groups = array();

	/* @var array $eachItems - each arrays */
	private $eachItems = array();

	/* @var array $eachMethods - each methods */
	private $eachMethods = array();

	/* @var array $eachIndex - current each iterate index */
	private $eachIndex = -1;

	/* @var array $noCloseTags - no pair tags */
	protected static $noCloseTags = array(
		'area',
		'base',
		'br',
		'col',
		'frame',
		'hr',
		'img',
		'input',
		'link',
		'meta',
		'param'
	);

	/**
	  * Static constructor
	  * @return CHtml
	  */
	public static function create() {
		return new static();
	}

	/**
	  * Add tag
	  * @param string $name tag name
	  * @param array|string $options tag attributes
	  * @return CHtml
	  */
	protected function tag($name, $options=null) {
		$name = strtolower($name);
		$close = in_array($name, self::$noCloseTags);

		if (in_array($name, array('text', 'html')) && is_string($options)) {
			$this->html .= $options;
			return $this;
		}

		if ($name === 'end'  && !$options) {
			$this->html .= '</'.array_pop($this->group).'>';
			return $this;
		}

		$options = $options ?: array();
		$attrs = array();
		foreach($options as $attr => $value) {
			$attrs[] = $attr . '="'.$value.'"';
		}
		$this->html .= '<'.$name. ($attrs ? ' '. implode(' ', $attrs) : '') . ($close ? '/':'') .'>';
		if (!$close) {
			$this->group[] = $name;
		}
		return $this;
	}

	/**
	  * Render current tags
	  * @param boolean $return return or print
	  * @return CHtml|string
	  */
	public function render($return=false) {
		while($this->eachIndex != -1) {
			$this->endEach();
		}
		while($this->group) {
			$this->end();
		}
		$html = $this->html;
		$this->html = '';
		if (!$return) {
			echo $html;
		}
		return $return ? $html : $this;
	}

	/**
	  * Group html methods
	  * @return array
	  */
	public function group() {
		$this->groups[] = $this->group;
		$this->group = array();
		return $this;
	}

	/**
	  * Close group and its tags
	  * @return array
	  */
	public function endGroup() {
		while($this->group) {
			$this->end();
		}
		$this->group = array_pop($this->groups);
		return $this;
	}

	/**
	  * Create tag by not define method, or save method in iterate array
	  * @return CHtml
	  */
	public function __call($method, $ps) {
		if ($this->eachItems) {
			$this->eachMethods[$this->eachIndex][] = array($method, $ps);
			return $this;
		}
		array_unshift($ps, $method);
		return call_user_func_array(array($this, 'tag'), $ps);
	}

	/**
	  * Create iterate array
	  * @param array $items for iterate
	  * @return CHtml
	  */
	public function each(Array $items) {
		if ($this->eachIndex !== -1) {
			$this->eachMethods[$this->eachIndex][] = null;
		}
		$this->eachIndex++;
		$this->eachItems[] = $items;
		return $this;
	}

	/**
	  * Recursion for iterate each
	  * @param array $params params for each iteration
	  * @return CHtml
	  */
	private function eachIterate(Array $params = null) {
		$items = array_shift($this->eachItems);
		$methods = array_shift($this->eachMethods);

		if ($items && $methods) {
			$params = $params ?: array();
			foreach($items as $eachItem) {
				$prop = $params;
				$prop[] = $eachItem;
				foreach($methods as $method) {
					if ($method === null) {
						$this->eachIterate($prop);
					} else {
						list($tag, $ps) = $method;
						$this->tag($tag, $ps ? self::ExtractValue($ps[0], $prop) : array());
					}
				}
			}
		}
	}

	/**
	  * Close each and run iterator when last each close
	  * @return CHtml
	  */
	public function endEach() {
		$this->eachIndex--;
		if ($this->eachIndex == -1) {
			$this->eachIterate();
			$this->eachMethods = array();
			$this->eachItems = array();
		}
		return $this;
	}

	/**
	  * Extract value from attributes
	  * @return CHtml
	  */
	public static function extractValue($mixed = null, Array $params = null) {
		if (is_callable($mixed)) {
			return call_user_func_array($mixed, $params);
		} else if (is_string($mixed) && strpos($mixed, '$data') !== false) {
			$data = new \stdclass();
			if ($params) {
				$data->params = $params;
				foreach($params as $param) {
					foreach($param as $key => $value) {
						if (!is_numeric($key) && $key !== 'params') {
							$data->$key = $value;
						}
					}
				}
			}
			return eval('return ' . rtrim($mixed, ';') . ';');
		}
		return $mixed;
	}

	/**
	  * Plain array
	  * @param array $arr array for plain
	  * @param string $keyName name for keys
	  * @param string $valName name for values if need
	  * @return Array
	  */
	public static function plainArray(Array $arr, $keyName='id', $valName='value') {
		foreach($arr as $key => &$val) {
			if (!is_array($val)) {
				$val = array($valName => $val);
			}
			if (isset($val[$keyName])) {
				throw new CException("Array key for plain is exist.");
			}
			$val[$keyName] = $key;
		}
		return array_values($arr);
	}
}
