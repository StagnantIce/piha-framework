<?php

/**
* CDataObject
* универсальный объект-массив, используется для создания классов.
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @abstract
*/
namespace piha\modules\orm\classes;

use piha\AClass;
use piha\CException;

class CDataObject extends AClass implements \IteratorAggregate, \ArrayAccess {
    /** @ignore */

    /* функции имеющие перед собой это слово могут быть вызваны как статические, без этого слова */
    const STATIC_PREFIX = 'Static';
    /** @var данные объекта в виде массива */
    private $_data = array();
    /** @var события объекта, повешенные при помощи функции on */
    private static $_events = array();

    /**
     * Повесить событие на класс
     * @param $type - название события, лучше устанавливать в константах класса
     * @param $callback - результат вызова функции className с названием метода
     * @param $sort - порядок вызова
     * @return null
     */
    public static function on($type, $callback, $sort = 100) {
        if (is_array($callback) && count($callback) === 2) {
            $callback = implode('::', $callback);
        }
        if (!is_string($callback)) {
            throw new CException("Error callback parameter");
        }
        self::$_events[static::className()][$type][intval($sort)][] = $callback;
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

    /** @ignore */
    public function __call($method, $ps) {
        $type = strtolower(substr($method, 0, 3));
        // prepare class vars
        if ($type === 'set' || $type === 'get') {
            $p = lcfirst(substr($method, 3));
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

    /**
      * @param array $data - данные для инициализации
      */
    public function __construct(Array $data = null) {
        foreach($data as $key => $value) {
            if (!preg_match('/^[_a-z]+[A-Za-z_]*\d*$/', $key)) {
                throw new CException("Error name parameter {$key}.");
            }
        }

        $vars = array_keys(get_class_vars(get_class($this)));
        foreach($vars as $v) {
            if (array_key_exists($v, $data)) {
                $this->$v = $data[$v];
            }
            unset($data[$v]);
        }
        $this->_data = $data;
    }
    /** @ignore */
    public static function __callStatic($method, $ps) {
        if (substr($method, 0, strlen(self::STATIC_PREFIX)) != self::STATIC_PREFIX) {
            $callable = self::className(self::STATIC_PREFIX . $method);
            return call_user_func_array($callable, $ps);
        }
        throw new CException(__CLASS__.' do not have a static method named '.$method);
    }

    /** @ignore */
    public function __get($k) {
        if ($this->property_exists($k)) {
            return $this->_data[$k];
        }
        return parent::__get($k, $v);
    }
    /** @ignore */
    public function __set($k, $v) {
        if ($this->property_exists($k)) {
            $this->_data[$k] = $v;
            return $this;
        }
        return parent::__set($k, $v);
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
        $props = $props ?: array_keys($this->_data);
        $result = array_intersect_key($this->_data, array_flip($props));
        $vars = array_keys(get_class_vars(get_class($this)));

        foreach($vars as $v) {
            if (!$props || in_array($v, $props)) {
                $result[$v] = $this->$v;
            }
        }
        return $result;
    }
    /**
      * Загрузить в объект CDataObject массив
      */
    public function fromArray(Array $data, Array $props=null) {
        $this->_data = array_replace($data, array_intersect_key($data, $props ? array_flip($props) : $data));
        $vars = array_keys(get_class_vars(get_class($this)));
        foreach($vars as $v) {
            if (array_key_exists($v, $data) && (!$props || (in_array($v, $props)))) {
                $this->$v = $data[$v];
            }
        }
    }

    public function fromObject(CDataObject $obj) {
        if(get_class($this)===get_class($obj)) {
            $this->_data = $obj->_data;
        } else {
            throw new CException("Error copy object");
        }
    }

    /** @ignore */
    public function property_exists($name) {
        return (array_key_exists($name, $this->_data) ? true : $this->BadPropertyCallException($name));
    }

    //  IteratorAggregate interface
    /** @ignore */
    public function getIterator() {
        return new \ArrayIterator($this->_data);
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
        $this->badPropertyCallException($offset);
    }
    /** @ignore */
    public function offsetSet ( $offset ,  $value ) {
        if ($this->property_exists($offset)) {
            $this->_data[$offset] = $value;
            return $this;
        }
        $this->badPropertyCallException($offset);
    }
    /** @ignore */
    public function offsetUnset ( $offset ) {
        unset($this->_data[$offset]);
    }
}
