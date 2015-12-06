<?php

namespace piha;


abstract class AClass {

    /* методы имеющие перед собой это слово могут быть вызваны как статические извне, без этого слова */
    const STATIC_PREFIX = 'Static';

    public static function className($method=null) {
        if (!$method) {
            return get_called_class();
        } else if (is_string($method)) {
            $class = get_called_class();
            $classes = class_parents($class);
            $classes[] = $class;
            foreach($classes as $parent) {
                if (method_exists($parent, $method) || method_exists($parent, self::STATIC_PREFIX.$method)) {
                    return array($class, $method);
                }
            }
            throw new CException('Not callable ' . $class . '::' . $method . ' for classes ' . implode(', ', $classes));
        }
        throw new CException('Not callable method');
    }

    public function badPropertyCallException($name) {
        throw new CException(get_class($this) . ' do not have a property named "'. $name . '".');
    }

	public function __set($name, $value) {
		$this->badPropertyCallException($name);
	}

	public function __get($name) {
		$this->badPropertyCallException($name);
	}

    public function __call($method, $ps) {
        $type = strtolower(substr($method, 0, 3));
        // prepare class vars
        if ($type === 'set' || $type === 'get') {
            $p = lcfirst(substr($method, 3));
        }

        if ($p && $type == 'set' && count($ps) == 1) {
            $this->$p = $ps[0];
            return $this;
        } elseif ($p && $type == 'get' && count($ps) == 0) {
            return $this->$p;
        } else if (is_callable($method) && substr($method, 0, 6) === 'array_') {
            return call_user_func_array($method, array_merge(array($this->_data), $ps));
        }
        return self::__callStatic($method, $ps);
    }
    /** @ignore */
    public static function __callStatic($method, $ps) {
        if (substr($method, 0, strlen(self::STATIC_PREFIX)) != self::STATIC_PREFIX) {
            try {
                $callable = self::className(self::STATIC_PREFIX . $method);
                return call_user_func_array($callable, $ps);
            } catch(CException $e){

            }
        }
        throw new CException(get_called_class().' do not have a method named '.$method);
    }
}