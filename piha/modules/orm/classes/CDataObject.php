<?php

/**
* CDataObject
* универсальный объект-массив, используется для создания классов.
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @abstract
*/
namespace piha\modules\orm\classes;

use piha\CException;

abstract class CDataObject implements \IteratorAggregate, \ArrayAccess {
    /** @ignore */

    /* функции имеющие перед собой это слово могут быть вызваны как статические, без этого слова */
    const STATIC_PREFIX = 'Static';
    /** @var данные объекта в виде массива */
    private $_data = array();
    /** @var дефолтовые значения переменных */
    public $_defaults = array();
    /** @var события объекта, повешенные при помощи функции on */
    private static $_events = array();

    /**
     * Повесить событие на класс
     * @param $type - название события, лучше устанавливать в константах класса
     * @param $callback - результат вызова функции className с названием метода
     * @param $sort - порядок вызова
     * @return null
     */
    public static function on($type, Array $callback, $sort = 100) {
        self::$_events[static::className()][$type][intval($sort)][] = implode('::', $callback);
    }

    /**
     * Вызвать событие на класс
     * @param $type - название события класса, лучше устанавливать в константах класса
     * @param param1
     * @param param2
     * ...
     * @return Array results - результаты обработчиков событий
     */
    public static function trigger() {
        $args = func_get_args();
        if (count($args) == 0) {
            throw new CCoreExcption('No type param for trigger class '. static::className());
        }
        list($class, $type) = array(static::className(), $args[0]);
        if (isset(self::$_events[$class][$type])) {
            ksort(self::$_events[$class][$type]);
            foreach(self::$_events[$class][$type] as $sort => $callbacks) {
                foreach($callbacks as $callback) {
                    $result[$callback] = call_user_func_array($callback,  array_slice($args, 1));
                }
            }
        }
        return $result;
    }

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
            throw new CException('Not callable ' . $class . '::' . $method);
        }
        throw new CException('Not callable method');
    }

    // ONE_TIME to oneTime, _ONE_TIME to _oneTime
    public function toVar($v) {
        $s = '';
        $first = true;
        foreach(explode('_', strtolower($v)) as $vv) {
            if ($vv) {
                $s .= $first ? lcfirst($vv) : ucfirst($vv);
                $first = false;
            } else {
                $s .= '_';
            }
        }
        return $s;
    }

    // oneTime to ONE_TIME, myNAME to MY_NAME
    public function toKey($v) {
        return strtoupper(preg_replace('/([A-Z])([a-z]+)/', '_$1$2', lcfirst($v)));
    }

    /** @ignore */
    public function __call($method, $ps) {
        $type = strtolower(substr($method, 0, 3));

        // prepare class vars
        if ($type === 'set' || $type === 'get') {
            $p = $this->toVar($this->toKey(substr($method, 3)));
        }

        if ($type == 'set' && count($ps) == 1) {
            $this->$p = $ps[0];
            return $this;
        } elseif ($type == 'get' && count($ps) == 0) {
            return $this->$p;
        } else if (is_callable($method) && substr($method, 0, 6) === 'array_') {
            return call_user_func_array($method, array_merge(array($this->_data), $ps));
        }
        throw new CException(get_class($this).' do not have a method named '.$method);
    }

    /** @ignore */
    public function __construct(Array $data = null, Array $defaults = null) {
        if ($defaults) {
            $this->_defaults = $defaults;
            if ($data) {
                $data = array_replace($defaults, $data);
            } else {
                $data = $defaults;
            }
        } else {
            $this->_defaults = array_fill_keys(array_keys($data), null);
        }

        //var_dump($this->_defaults); die();
        $vars = array_keys(get_class_vars(get_class($this)));
        foreach($vars as $v) {
            $vv = $this->toKey($v);
            if (isset($this->_defaults[$vv])) {
                $this->$v = $this->_defaults[$vv];
            }
        }
        //print_r($this->_defaults); die();
        foreach($data as $k => $v) {
            if ($var = $this->toVar($k) and in_array($var, $vars)) {
                $this->$var = $v;
            } else if (!is_numeric($k)) {
                $this->_data[strtoupper($k)] = $v;
            } else if (!is_numeric($v)) {
                $this->_data[strtoupper($v)] = null;
            } else {
                throw new CException(__CLASS__.' error in __construct');
            }
        }
    }
    /** @ignore */
    public static function __callStatic($method, $ps) {
        /*
        if ($ps[0] instanceof self) {
            return call_user_func_array(array($ps[0], $method), array_slice($ps, 1));
        }*/
        if (substr($method, 0, strlen(self::STATIC_PREFIX)) != self::STATIC_PREFIX) {
            $callable = self::className(self::STATIC_PREFIX . $method);
            return call_user_func_array($callable, $ps);
        }
        throw new CException(__CLASS__.' do not have a static method named '.$method);
    }
    /**
      * Проверить, является ли объект массивом или наследником CDataObject
      * @return boolean ди или нет
      */
    public static function is_array($mixed) {
        return (is_array($mixed) || ($mixed instanceof self));
    }
    /** @ignore */
    public function __get($k) {
        $k = $this->toKey($k);
        if ($this->property_exists($k)) {
            return $this->_data[$k];
        }
        $this->BadPropertyCallException($k);
    }
    /** @ignore */
    public function __set($k, $v) {
        $k = $this->toKey($k);
        if ($this->property_exists($k)) {
            $this->_data[$k] = $v;
            return $this;
        }
        $this->BadPropertyCallException($k);
    }

    /**
      * Объект CDataObject можно печатать как обычную строку
      * @return string
      */
    public function __toString() {
        return get_class($this) . 'Object ' . substr(print_r($this->_data, true), 6);
    }

    /**
      * Вернуть объект CDataObject как массив
      * @param array $props имена параметров
      * @return array
      */
    public function toArray(Array $props=null) {
        if ($props) {
            foreach($props as &$v) $v = $this->toKey($v);
            return array_intersect_key($this->_data, array_flip($props));
        }
        return $this->_data;
    }
    /**
      * Загрузить в объект CDataObject массив
      */
    public function fromArray(Array $arr, Array $props=null) {
        foreach($this->_data as $key => $value) {
            if (isset($arr[$key])) {
                if (!$props || in_array($key, $props)) {
                    $this->_data[$key] = $arr[$key];
                }
            }
        }
        $vars = array_keys(get_class_vars(get_class($this)));
        foreach($vars as $v) {
            $vv = $this->toKey($v);
            if (isset($arr[$vv])) {
                if (!$props || in_array($vv, $props)) {
                    $this->$v = $arr[$vv];
                }
            }
        }
    }
    /** @ignore */
    public function BadPropertyCallException($name) {
        throw new CException(get_class($this) . ' do not have a property named "'. $name . '".');
    }
    /** @ignore */
    public function property_exists($name) {
        return (array_key_exists($name, $this->_data) ? true : $this->BadPropertyCallException($name));
    }

    //  IteratorAggregate interface
    /** @ignore */
    public function getIterator() {
        return new ArrayIterator($this->_data);
    }

    //  ArrayAccess interface
    /** @ignore */
    public function offsetExists ( $offset ) {
        return array_key_exists($offset, $this->_data);
    }
    /** @ignore */
    public function offsetGet ( $offset ) {
        if ($this->property_exists($offset)) {
            return $this->_data[$offset];
        }
        $this->BadPropertyCallException($offset);
    }
    /** @ignore */
    public function offsetSet ( $offset ,  $value ) {
        if ($this->property_exists($offset)) {
            $this->_data[$offset] = $value;
            return $this;
        }
        $this->BadPropertyCallException($offset);
    }
    /** @ignore */
    public function offsetUnset ( $offset ) {
        unset($this->_data[$offset]);
    }
}
