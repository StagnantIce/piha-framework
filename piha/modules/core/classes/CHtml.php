<?php

namespace piha\modules\core\classes;
use piha\CException;

class CHtml {

	/* @var string $html - string for output */
	private $html = '';

	/* @var array $stack - stack tags */
	private $stack = array();

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
	protected function tag($name, $options=null, $autoClose=true) {
		$name = strtolower($name);
		$close = in_array($name, self::$noCloseTags);

		if ($name === 'html' && is_string($options)) {
			$this->html .= $options;
			return $this;
		}

		$options = $options ?: array();
		$attrs = array();
		foreach($options as $attr => $value) {
			$attrs[] = $attr . '="'.$this->safe(is_array($value) ? implode(' ', $value) : $value ).'"';
		}
		$this->html .= '<'.$name. ($attrs ? ' '. implode(' ', $attrs) : '') . ($close ? '/':'') .'>';
		if (!$close && $autoClose) {
			$this->stack[] = $name;
		}
		return $this;
	}

	/**
	  * Close tag with name, or last tag
	  * @param string $name tag name
	  * @return CHtml
	  */
	public function end($name = '') {
		if ($this->stack) {
			$name2 = array_pop($this->stack);
		}
		if (!$name) {
			$name = $name2;
		}
		$this->html .= '</'.$name.'>';
		return $this;
	}

	/**
	  * Render current tags
	  * @param boolean $return return or print
	  * @return CHtml|string
	  */
	public function render($return=false) {
		while($this->stack) {
			$this->end();
		}
		$html = $this->html;
		$this->html = '';
		if ($return) {
			return $html;
		}
		echo $html;
	}

	/**
	  * Create tag by not define method, or save method in iterate array
	  * @return CHtml
	  */
	public function __call($method, $ps) {
		array_unshift($ps, $method);
		return call_user_func_array(array($this, 'tag'), $ps);
	}

	/**
	  * Извлечь параметр из опций и вернуть его
      * @param array $options параметры
      * @param string $name параметр для извлечения
	  */
	public static function popOption(&$options, $name) {
		if (isset($options[$name])) {
			$option = $options[$name];
			unset($options[$name]);
			return $option;
		}
		return '';
	}

	/**
	  * Return safe html string
	  * @param string|array $value
	  * @return string
	  */
	public static function safe($value) {
		if (is_array($value)) {
			foreach($value as &$item) {
				$item = self::safe($item);
			}
			return $value;
		} else if (is_scalar($value)) {
			return htmlspecialchars('' .$value, ENT_QUOTES);
		}
		throw new CException("Error safe value with type " . gettype($value));
	}
}
