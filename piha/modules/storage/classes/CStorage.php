<?php

namespace piha\modules\storage\classes;


class CStorage {

	private static $storages = array();

	public function getStorage($storagesClass, $name = 'storage') {
		if (isset(self::$storages[$name])) {
			return self::$storages[$name];
		}
		self::$storages[$name] = new $storagesClass($name);
		return self::$storages[$name];
	}
}