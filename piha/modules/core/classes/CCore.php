<?php

namespace piha\modules\core\classes;

class CCore {

    private $start_time = null;
    private static $app = null;
    public static function app() {
        if (!self::$app) {
            self::$app = new self();
            self::$app->start_time = time() + microtime();
        }
        return self::$app;
    }

    public function getTime() {
        return (time() + microtime()) - self::app()->start_time;
    }

    private $controller = null;

    /**
      * Валидирует переменную на тип и пустоту, иначе вызывает исключение
      * @param mixed $v переменная для проверки
      * @param array|string $types тип или типы для проверки, можно указывать названия классов
      * @param boolean $empty может ли быть переменная пустой (пустым массивом)
      * @return mixed
      */
    public static function Validate($v, $types = null, $empty = false) {
        if (is_string($v)) {
            $v = trim($v);
            if (is_numeric($v)) { // если числовая строка, то преобразуем
                if (intval($v) == $v) {
                    $v = intval($v);
                } else if (floatval($v) == $v) {
                    $v = floatval($v);
                }
            }
        }
        if (!$empty && !$v) {
            throw new CCoreException("Empty value not expected.");
        }
        if ($types) {
            $types = (array)$types;
            $type = strtolower(gettype($v));
            if ($type == 'integer') $type = 'int';
            if ($type == 'double') $type = 'float';
            if ($type != 'object' && !in_array($type, $types)) {
                throw new CCoreException("Type $type not expected. Expect " . implode(', ', $types) . '.');
            } else if ($type == 'object' && !in_array(get_class($v), $types)) {
                throw new CCoreException("Object ".get_class($v)." not expected. Expect " . implode(', ', $types) . '.');
            } else if (!in_array($type, $types)) {
                throw new CCoreException("Unknown type $type in ". implode(', ', $types));
            }
        }
        return $v;
    }

    public function start() {
        if (PIHA_CONSOLE == false) {
            $this->controller = new CRouter();
        }
    }
}
