<?php

namespace piha;


abstract class AClass {

    public function badPropertyCallException($name) {
        throw new CException(get_class($this) . ' do not have a property named "'. $name . '".');
    }

	public function __set($name, $value) {
		$this->badPropertyCallException($name);
	}

	public function __get($name) {
		$this->badPropertyCallException($name);
	}
}