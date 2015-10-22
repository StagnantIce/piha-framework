<?php

/**
* AExtendClass
* класс для организации множественного наследования в PHP
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/
namespace piha\modules\core\classes;


abstract class AExtendClass {

    /** @var array Кешируемые объекты */
    private $_objects = array();

    /**
      * Функция для определения списка классов для наследования
      * @return array список классов для наследования
      */
    abstract public static function extend();

    protected function getObject($className) {
        if (!isset($this->_objects[ $className ])) {
            $this->_objects[ $className ] = new $className;
        }
        return $this->_objects[ $className ];
    }

    public static function getClass($method) {
        foreach(static::extend() as $class) {
            if(method_exists($class, $method)) {
                return $class;
            }
        }
        return false;
    }

    protected function getObjectByProperty($property) {
        foreach (static::extend() as $class) {
            if (in_array($property, array_keys(get_class_vars($class)))) {
               return $this->getObject($class);
            }
        }
        return false;
    }

    public function __call($name, $ps) {
        $method = $name;
        $className = self::getClass($name);
        if (!$className) {
            $ps = array($name, $ps);
            $name = '__call';
            $className = self::getClass($name);
        }
        if ($className) {
            $object = $this->getObject($className);
            $res =  call_user_func_array(array(&$object, $name), $ps);
            return $res;
        }
        throw new CCoreException("Method $method not found in objects ". get_called_class() . ',' . implode(', ', static::extend()));
    }

    public static function __callStatic($name, $ps) {
        $method = $name;
        $className = self::getClass($name);
        if (!$className) {
            $ps = array($name, $ps);
            $name = '__callStatic';
            $className = self::getClass($name);
        }
        if ($className) {
            return call_user_func_array(array($className, $name), $ps);
        }
        throw new CCoreException("Static method $method not found in objects ".  get_called_class() . ',' . implode(', ', static::extend()));
    }


    public function __get($key) {
        if ($object = $this->getObjectByProperty($key)) {
            return $object->$key;
        }
        if ($className = self::getClass('__get')) {
            $object = $this->getObject($className);
            return $object->__get($key);
        }
        throw new CCoreException(get_called_class() . ',' . implode(', ', static::extend()) . ' do not have a property named "'. $key . '".');
    }

    public function __set($key, $value) {
        if ($object = $this->getObjectByProperty($key)) {
            $object->$key = $value;
            return $object;
        }
        if ($className = self::getClass('__set')) {
            $object = $this->getObject($className);
            return $object->__set($key, $value);
        }
        throw new CCoreException(get_called_class() . ',' . implode(', ', static::extend()) . ' do not have a property named "'. $key . '".');
    }
}