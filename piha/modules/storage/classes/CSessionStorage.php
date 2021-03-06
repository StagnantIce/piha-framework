<?php

namespace piha\modules\storage\classes;
use piha\AClass;
use piha\modules\storage\CStorageModule;

class CSessionStorage extends AStorage {

	private static $prefix = '';
	public function __construct($name) {
		self::$prefix = CStorageModule::config('prefix', '') . $name . '_';
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