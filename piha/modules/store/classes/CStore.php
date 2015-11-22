<?php

namespace piha\modules\store\classes;


class CStore {

	private static $stores = array();

	private static function getStore($name, $storeClass) {
		if (isset(self::$stores[$name])) {
			return self::$stores[$name];
		}
		self::$stores[$name] = new $storeClass($name);
		return self::$stores[$name];
	}

	public static function session($name = 'store') {
		return self::getStore($name, CSessionStore::className());
	}

}