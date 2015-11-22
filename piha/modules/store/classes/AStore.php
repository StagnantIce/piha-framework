<?php

namespace piha\modules\store\classes;
use piha\AClass;

abstract class AStore extends AClass {

	abstract public function set($name, $value);
	abstract public function get($name);
	abstract public function del($name);
	abstract public function isExist($name);
	abstract public function pop($name, $default = null);

}