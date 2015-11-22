<?php

namespace piha\modules\store\classes;
use piha\AClass;
use piha\modules\store\CStoreModule;

class CSessionStore extends AStore {

	private static $prefix = '';
	public function __construct($name) {
		self::$prefix = CStoreModule::config('prefix', '') . $name . '_';
	}

	public function set($name, $value) {
		$_SESSION[self::$prefix . $name] = $value;
	}

	public function del($name) {
		unset($_SESSION[self::$prefix . $name]);
	}

	public function get($name, $default=null) {
		if (isset($_SESSION[self::$prefix . $name])) {
			return $_SESSION[self::$prefix . $name];
		}
		return $default;
	}

	public function isExist($name) {
		return isset($_SESSION[self::$prefix . $name]);
	}

	public function pop($name, $default=null) {
		$pop = $this->get($name, $default=null);
		$this->del($name);
		return $pop;
	}
}