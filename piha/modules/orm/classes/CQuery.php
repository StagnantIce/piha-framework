<?php

/**
* CQuery
* класс для написания запросов к БД.
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @todo сделать подзапросы, рефакторинг
*/
namespace piha\modules\orm\classes;

use piha\modules\orm\COrmModule;
use piha\CException;
use piha\modules\core\classes\AExtendClass;
use piha\modules\core\classes\CTool;

class CQuery extends AExtendClass {
    public static $last = '';

    private $_q = '';
    private $_name = '';
    private $_execute = false;
    private $_columns = array();
    private $_relations = array();

    private $_select = '';
    private $_delete = '';
    private $_from = '';
    private $_join = '';
    private $_where = '';
    private $_order = '';
    private $_group = '';
    private $_limit = '';
    private $_having = '';

    private $_object = '';

    private static $secret = '';

    public static function extend() {
        return array(COrmModule::GetInstance()->config('className', 'CMysqlConnection'));
    }
    /**
      * Конструктор
      * @param string $mixed имя таблицы или запрос к БД
      * @param array $columns
      * @param array $relations
      * @return CQuery
      */
    public function __construct($mixed = '', $columns = array(), $relations = array()) {
        $mixed = trim($mixed);
        if (strpos($mixed, ' ') > 0 || in_array(strtoupper($mixed), array('ROLLBACK', 'COMMIT'))) {
            $this->_q = $mixed;
        }
        $this->_name = $mixed;
        $this->_relations = $relations;
        $this->_columns = $columns;
    }

    public static function GetTableName($className) {
        $object = CModel::m($className);
        if ($object) {
            return COrmModule::quoteTableName($object->_name);
        }
        throw new CException("CQuery link error. Class $className not exists");
    }

    public static function fromModel($className) {
        $object = $className::m();
        $q = new CQuery($object->_name, $object->getColumns(), $object->getRelations());
        unset($object);
        $q->_object = $className;
        return $q;
    }

    /**
      * Считает количество записей согласно условию
      * @param mixed $where - условие
      * @param mixed $cond - условие для объединения
      * @return int Количество записей
      */
    public function count($fields = '*') {
        return (int)$this->select(array('COUNT('.$fields.')' => 'CNT'))->one('CNT') ?: 0;
    }

    public function getModel() {
        return $this->_object;
    }

    /**
      * Конструктор для вызова из цепочки
      * @param string $tableName имя таблицы или запрос к БД
      * @param array $columns
      * @param array $relations
      * @return CQuery
      */
    public static function create($tableName = '', $columns = array(), $relations = array()) {
        return new CQuery($tableName, $columns, $relations);
    }
    /**
      * Установить имя таблицы для select запросов
      * @param string $name
      * @return CQuery
      */
    public function setTableName($name = '') {
        $this->_name = $name;
        return $this;
    }

    /**
      * Установить список столбцов таблицы
      * @param array $columns
      * @return CQuery
      */
    public function setColumns($columns = array()) {
        $this->_columns = $columns;
        return $this;
    }

    /**
      * Установить запрос в БД
      * @param string $q
      * @return CQuery
      */
    public function setQuery($q = '') {
        $this->_q = $q;
        return $this;
    }

    /**
     * Выполнение запроса
     * @param bool $size
     * @param bool $numPage
     * @return CQuery
     */
    public function execute($size = false, $numPage = false) {
        $this->_execute = true;
        if ($size) {
            $this->queryNav($this->getQuery(), $size, $numPage);
        } else {
            $this->query($this->getQuery());
        }
        return $this;
    }
    /**
      * Возвращает строку запроса и обнуляет запрос
      * @param string|array $fields поля для метода select, если его не было
      * @return string - строка запроса
      */
    public function getQuery($fields = false) {
        if ($this->_select == "" && $this->_delete == "") {
            $this->select($fields); // as default
        }

        if ($this->_from == "") {
            $this->from(); // as default
        }

        if ($this->_q) {
            $q = $this->_q;
        } else {
            $q = ($this->_select ? 'SELECT SQL_CALC_FOUND_ROWS ' . $this->_select: $this->_delete) . $this->_from . $this->_join . ($this->_where ? ' WHERE ' . $this->_where: '') .$this->_group . ($this->_having ? ' HAVING ' . $this->_having: '') .$this->_order . $this->_limit;
        }
        CQuery::$last = $q;
        $this->_q = "";
        $this->_from = "";
        $this->_join = "";
        $this->_group = "";
        $this->_order = "";
        $this->_select = "";
        $this->_delete = "";
        $this->_where = "";
        $this->_limit = "";

        return $q;
    }

    /**
      * Возвращает количество записей последнего SELECT запроса
      * @return int количество записей
      */
    public static function getTotal() {
        return self::create()->setQuery("SELECT FOUND_ROWS() AS total")->one('total');
    }

    /**
      * Выполняет запрос и возвращает объект
      * @return array
      */
    public function object($mixed = false, $cond = 'AND') {
        if ($mixed) {
            if ($this->_where) {
                throw new CException("Use objects() method with where().");
            }
            $this->where($mixed, $cond);
        }
        if (!$object = $this->_object) {
            throw new CException("Execute object() method without object.");
        }

        if ($data = $this->one()) {
            return new $object($data);
        }
    }

    /**
      * Выполняет запрос и возвращает объекты
      * @return array
      */
    public function objects($mixed = false, $cond = 'AND') {
        if ($mixed) {
            if ($this->_where) {
                throw new CException("Use objects() method with where().");
            }
            $this->where($mixed, $cond);
        }
        if (!$object = $this->_object) {
            throw new CException("Execute objects() method without object.");
        }
        $data = $this->all();
        foreach($data as $d) {
            $result[] = new $object($d);
        }
        return $result;
    }

    /**
      * Выполняет запрос и возвращает одну строку как массив
      * @param string|array $fields поля для извлечения
      * @param string|array $groups группировка для извлечения ассоциативного массива
      * @return array
      */
    public function one($fields = false, $groups = false, $flat = null) {
        if (!$this->_execute) {
            $this->execute();
            $this->_execute = false;
        }
        return $this->parse(true, $fields, $groups, $flat);
    }

    /**
      * Выполняет запрос и возвращает все строки как массив
      * @param string|array $fields поля для извлечения
      * @param string|array $groups группировка для извлечения ассоциативного массива
      * @return array
      */
    public function all($fields = false, $groups = false, $flat = null) {
        if (!$this->_execute) {
            $this->execute();
            $this->_execute = false;
        }
        return $this->parse(false, $fields, $groups, $flat);
    }

    /**
      * Добавляет данные
      * @param array $fields
      * @deprecated нужно избавится от битрикс и этой функции
      * @return int - ID вставленной записи
      */
    public function insert(Array $fields = null) {
        return self::tableInsert($this->getName(), $this->prepare($fields));
    }

    /** @ignore */
    private function prepare(Array $fields = null) {
        if (!$fields) return array();

        $new_fields = array();
        $types = self::tableFields($this->getName());

        foreach ($fields as $param => &$value) {
            // not field
            if (!isset($types[$param])){
                continue;
            } else if (is_null($value) || $value === 'NULL') {
                $value = 'NULL';
            } else if (self::isField($value)) {
                if (substr_count($value, '"') % 2 != 0 || substr_count($value, "'") % 2 != 0) {
                    $value = self::escape($value);
                }
            } else {
                if (is_bool($value)) {
                    $value = intval($value);
                }
                // string, int, float, date
                switch($types[$param]) {
                    case 'string':
                    case 'varchar':
                    case 'blob':
                    case 'char':
                    case 'text':
                        if (is_numeric($value)) {
                            $value = (string)$value;
                        }
                        if (is_string($value)) {
                            $value = '"' . self::escape($value) . '"';
                        } else {
                            $value = serialize($value);
                            throw new CException("Error prepare $param field with not string value $value.");
                        }
                    break;
                    case 'real':
                    case 'float':
                    case 'decimal(10,0)':
                    case 'decimal(19,4)':
                        if (is_numeric($value)){
                            $value = DoubleVal($value);
                        } else if (is_string($value) && !trim($value)){
                            $value = 0;
                        } else {
                            $value = serialize($value);
                            throw new CException("Error prepare $param field with not float value $value.");
                        }
                    break;
                    case 'int':
                    case 'tinyint':
                        if (is_numeric($value)){
                            $value = intval($value);
                        } else if (is_string($value) && !trim($value)){
                            $value = 0;
                        } else {
                            $value = serialize($value);
                            throw new CException("Error prepare $param field with not int value $value.");
                        }
                    break;
                    case 'datetime':
                    case 'timestamp':
                    case 'time':
                    case 'date':
                        if (is_numeric($value)) {
                            $value = intval($value);
                        } else if (is_string($value) && strtotime($value) !== false) {
                            $value = '"' . self::escape($value) . '"';
                        } else {
                            $value = serialize($value);
                            throw new CException("Error prepare $param field with not date/time value $value.");
                        }
                    break;
                    default:
                        throw new CException("Prepare error. Unexpected field type ". $types[$param] .'.');
                    break;
                }
            }
            $param = COrmModule::GetInstance()->config('database/use_quotes', true) ? '`'.$param.'`' : $param;
            $new_fields[$param] = $value;
        }
        return $new_fields;
    }

    /**
     * Обновляет данные
     * @param array $fields
     * @param bool $where
     * @deprecated нужно избавится от битрикс и этой функции
     * @return bool|int
     */
    public function update(Array $fields = null, $where = false) {
        $this->where($where);
        $where = $this->_where;
        return self::tableUpdate($this->getName(), $this->prepare($fields), $where ? ' WHERE ' . $where : '');
    }
    /**
      * Удаляет данные согласную фильтру
      * @deprecated нужно избавится
      * @return int - количество затронутых строк
      */
    public function remove($where = '') {
        return $this->delete()->where($where)->execute()->affectedRows();
    }
    /** @ignore */
    public static function parseRow(Array $r = null, $fields = false) {
        if (!$r || !$fields) {
            return $r;
        }
        if (is_string($fields) && array_key_exists($fields, $r)) {
            return $r[$fields];
        }
        elseif (is_array($fields)) {
            $arr = array();
            foreach ($fields as $needField) {
                if (array_key_exists($needField, $r)) {
                    $arr[$needField] = $r[$needField];
                }
            }
            return $arr;
        }
        return false;
    }
    /** @ignore */
    private function parse($one = false, $fields = false, $groups = false, $flat = null) {
        $data = array();
        if ($fields !== false) {
            $fields = is_string($fields) ? strtoupper($fields) : array_map('strtoupper', $fields);
        }
        if ($groups !== false) {
            $groups = array_map('strtoupper', (array)$groups);
        }
        while($r = $this->Fetch()) {
            $r = array_change_key_case($r, CASE_UPPER);

            if ($groups) {
                $d = &$data;
                foreach($groups as $group) {
                    if (!array_key_exists($group, $r)) throw new CException('Key for group by "'.$group. '" not found in ' . implode(',', array_keys($r)));
                    if (!isset($r[$group])) throw new CException('Value for group by "'.$group. '" is empty');
                    if (!isset($d[$r[$group]])) $d[$r[$group]] = array();
                    $d = &$d[$r[$group]];
                }
                if (is_string($d)) {
                    $d = array($d);
                }
                if (count($d) === 0) {
                    $d = self::parseRow($r, $fields);
                    if ($flat===false) {
                        $d = array($d);
                    }
                } else {
                    if ($flat===true) {
                        throw new CException("Unexpected array value with flat parameter.");
                    }
                    if (!is_numeric(key($d))) {
                        $d = array($d);
                    }
                    $d[] = self::parseRow($r, $fields);
                }
                if ($one) {
                    return $data;
                }
            } else {
                if ($one) {
                    return self::parseRow($r, $fields);
                }
                $data[] = self::parseRow($r, $fields);
            }
        }
        return $one ? false : $data;
    }
    /**
      * Добавляет к запросу конструкцию DELETE
      * @return CQuery
      */
    public function delete() {
    	$this->_delete = "DELETE ";
        return $this;
    }

    /**
     * Функция преобразует переменную в диапазон
     * @param int|array|string $v
     * @return string as Range
     */
    public static function range($v = false) {
        if (!$v) $v = CQuery::formula("NULL");
        if (!is_array($v)) $v = array($v);
        $r = array();
        foreach($v as $ar) {
            if (is_string($ar)) {
                if (self::isField($ar) === false) {
                    $r[] = "'".self::escape($ar)."'";
                } else {
                     $r[] = self::escape($ar);
                }
            } elseif (is_numeric($ar)) {
                $r[] = intval($ar);
            } else {
                throw new CException("Range Invalid ". serialize($v));
            }
        }
        return '('. implode(', ', $r) . ')';
    }
    /** @ignore */
    public function getColumns() {
        if ($this->_columns) {
            $columns =  array_keys($this->_columns);
            $arr = array();
            foreach($columns as $column) {
                $arr[] = $this->getName() . ".`$column`";
            }
            return implode(', ', $arr);
        }
        return '*';
    }
    /**
      * Добавляет к запросу конструкцию SELECT, если вытаскиваются только поля
      * исходной таблицы, вызов не требуется
      * @param array|string $fields список полей
      * @todo необходим рефакторинг
      * @return CQuery
      */
    public function select($fields = false) {
        if (!$fields) {
            $this->_select = $this->getColumns();
        } else if (is_string($fields)) {
            $this->_select = self::escape($fields);
        } else if (is_array($fields)) {
            $f = array();
            foreach($fields as $key => $value) {
                if ($value == '*') {
                    $f[] = $this->getColumns();
                    continue;
                }
                if (!is_numeric($key)) {
                    $field = $key;
                } else {
                    $field = $value;
                }
                if (isset($this->_columns[$field])) {
                    $field = $this->getName() . ".`$field`";
                }
                if (!is_numeric($key)) {
                    $field .= ' AS '. $value;
                }

                $f[] = $field;
            }
            $this->_select =  implode(', ', $f);
        }

        return $this;
    }
    /**
      * Добавляет к запросу конструкцию FROM, если таблица одна, вызов не требуется
      * @param array $tables список таблиц/запросов и алиасов
      * @todo необходим рефакторинг
      * @return CQuery
      */
    public function from(Array $mixed = null) {
        if (!$mixed) {
            $this->_from = " FROM " . $this->getName() . ' ';
        } else {
            foreach($mixed as $key => $value) {
                if (is_numeric($key)) {
                    $key = 't'.$key;
                }
                if ($value instanceof self) {
                    $f[] = '('. str_replace('SQL_CALC_FOUND_ROWS ', '', $value->getQuery()) . ') as '.$key;
                } else {
                    $f[] = COrmModule::quoteTableName($value) . " AS $key";
                }
            }
            $this->_from = " FROM ". implode(', ', $f);
        }
        return $this;
    }

    /**
      * Вытащить связь двух моделей
      * @return
      */
    private function getJoinCondition($tableName, Array $condition, $type = 'AND') {
        $result = array();
        $tableName = COrmModule::quoteTableName($tableName);
        foreach($condition as $k => $v) {
            $result[str_replace('#', $tableName, $k)] = str_replace('#', $tableName, $v);
        }
        return $this->condition($result, $type, true);
    }

    private function joinRelation($fieldName, $joinType) {
        if (!$this->_object || !$this->_relations) {
            return false;
        }
        foreach($this->_relations as $type => $relations) {
            foreach($relations as $relationName => $relation) {
                if ($relationName === $fieldName) {

                    $field = array_shift($relation);

                    $prevClass = null;
                    if (!$relation || count($relation) > 2) {
                        throw new CException("Inocorrect arguments number in relation {$fieldName} for model {$this->_object}");
                    }
                    $className = array_shift($relation);

                    $cond = array();
                    $class = $this->_object;

                    $pk = $class::m()->_pk;
                    if (!$relation) {
                        $cond['__table'] = $fieldName;
                    } else {
                        $cond['__table'] = $fieldName . '__between';
                    }
                    if ($field === $pk) {
                        $joinField = $className::GetObjectField($this->_object); // ID => MODEL_ID
                    } else if (!$relation) {
                        $joinField = $pk; // MODEL_ID -> ID
                    } else if ($object = $class::GetObject($field)) {
                        if ($object === $className) { // MODEL_ID -> ID => MODEL_OTHER_ID
                            $joinField = $pk;
                        } else {
                            $joinField = $className::GetObjectField($object); // MODEL_ID -> MODEL_ID
                        }
                    }
                    if (!$joinField) {
                        throw new CException("Error join {$className} to {$this->_object}. No object for {$this->_object} class in {$className} class.");
                    }
                    $cond['#.'.$joinField] = '*.'.$field;

                    $this->join(array($className => $cond), '', $joinType);
                    if ($relation) {
                        $nextClass = array_shift($relation);
                        $joinField = $className::GetObjectField($nextClass);
                        if (!$joinField) {
                            throw new CException("Error join {$nextClass} to {$className}. No object for {$nextClass} class in {$className} class.");
                        }
                        $this->join(array($nextClass => array('#.'.$nextClass::m()->_pk => $fieldName . '__between' .'.'.$joinField, '__table' => $fieldName)), '', $joinType);
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
      * Добавляет к запросу конструкцию JOIN
      * @param array|string $table имя таблицы, связующего поля или список таблиц/полей, а также условий для связки
      * @param array $rewrite_cond - условие для переопределения привязки, если таблица одна
      * Список условий $rewrite_cond может иметь следующие форматы
      * <pre>
      * array('ID>=' => 5)
      * array('ID' => array(1,2,3,4,5), 'NAME%' => 'Alexander')
      * array( array('OR' => array('!ID' => array(1,2,3,4,5), 'NAME%' => 'Alexander'))
      * </pre>
      * Список условий $table может иметь следующие форматы
      * <pre>
      * field_name
      * array('field_name' => 'table_name')
      * array('class_model_name' => array('*.field_name' => '#.join_field_name')
      * array('join_table_name' => array('*.field_name' => '#.join_field_name')
      * </pre>
      * * - указывает на текущую таблицу, # - указывает на таблицу которую присоединяют
      * @return CQuery
      */
    public function join($table = false, $rewrite_cond = "", $type = "") {
        if (!$table) return;
        $q = "";
        if (!is_array($table)) $table = array($table);
        foreach($table as $alias => $field) {
            $tableName = $cond = '';

            $alias = is_numeric($alias) ? $field : $alias;

            if (is_string($field) && is_string($alias)) {
                if (isset($this->_columns[$alias])) {
                    list($alias, $field) = array($field, $alias);
                }

                if ($this->joinRelation($field, $type)) {
                    continue;
                } else if (isset($this->_columns[$field]) && $rel = $this->_columns[$field]) {
                    if (isset($rel['object'])) {
                        $tableName = self::GetTableName($rel['object']);
                        $cond = "`{$alias}`.ID = ".$this->getName().".`{$field}`";
                    } else {
                        throw new CException("JOIN Error. Expected string or array for '$field' field.");
                    }
                } else {
                    $tableName = $field;
                }
            } else if (is_array($field)) {
                $className = isset($field['__object']) ? $field['__object'] : $alias;
                $tableName = class_exists($className) ? self::GetTableName($className) : $alias;
                $asName = isset($field['__table']) ? $field['__table'] : $tableName;
                unset($field['__table'], $field['__object']);
                $cond = $this->getJoinCondition($asName, $field);
                $alias = $asName;
                unset($asName);
            } else {
                throw new CException("JOIN Error. Not support format.");
            }

            $cond = $rewrite_cond ? $this->getJoinCondition($alias, $rewrite_cond) : $cond;
            $alias = '`' . trim($alias, '`') . '`';
            $q .= " $type JOIN {$tableName} AS {$alias} " . ($cond ?  ' ON '. $cond : '');
        }
        $this->_join .= $q;
        return $this;
    }
    /**
      * Добавляет к запросу конструкцию RIGHT JOIN аналогично JOIN
      * @param array|string $table имя таблицы, связующего поля или список таблиц/полей, а также условий для связки
      * @param array $cond - условие для переопределения привязки, если таблица одна
      * @return CQuery
      */
    public function right($table, $cond = '') {
        return $this->join($table, $cond, 'RIGHT');
    }
    /**
      * Добавляет к запросу конструкцию LEFT JOIN аналогично JOIN
      * @param array|string $table имя таблицы, связующего поля или список таблиц/полей, а также условий для связки
      * @param array $cond - условие для переопределения привязки, если таблица одна
      * @return CQuery
      */
    public function left($table, $cond = '') {
        return $this->join($table, $cond, 'LEFT');
    }
    /**
      * Добавляет к запросу конструкцию INNER JOIN аналогично JOIN
      * @param array|string $table имя таблицы, связующего поля или список таблиц/полей, а также условий для связки
      * @param array $cond - условие для переопределения привязки, если таблица одна
      * @return CQuery
      */
    public function inner($table, $cond = '') {
        return $this->join($table, $cond, 'INNER');
    }
    /** @ignore */
    public static function field($c) {
        if (strlen($c) > 4) {
            return trim(preg_replace('/[<>=\%]+/', '', substr($c, 0, 2))) . substr($c, 2, -2).  trim(preg_replace('/[<>=\%]+/', '',  substr($c, -2)));
        }
        return trim(preg_replace('/[<>=\%]+/', '', $c));
    }
    /** @ignore */
    public static function cond($c) {
        if (strlen($c) > 4) {
            $c = substr($c, 0, 2) . substr($c, -2);
        }
        return trim(preg_replace('/[^<>=\%]+/', '', $c));
    }

    public function getName() {
        return COrmModule::quoteTableName($this->_name);
    }

    public function findAndReplaceTableName($value) {
        return preg_replace('/\*\.[`]?([A-Za-z_0-9]+)/', $this->getName() . ".`$1`", $value);
    }

    /** @ignore */
    public function condition(Array $arr = null, $cond = 'AND', $is_join = false) {
        if (!$arr) return;

        $res = array();
        foreach($arr as $key => $value) {
            if (is_numeric($key)) {
                if (is_array($value)) {
                    foreach($value as $k => $v) {
                        $res[] = $this->condition($v, $k);
                    }
                } else {
                    $res[] = $value;
                }
                continue;
            }

            if (in_array($key, array('OR', 'AND', 'XOR'))) {
                if (!is_array($value)) {
                    throw new CException("Error condition. Expected array for key $key.");
                }
                if (is_array(current($value))) {
                    // OR => array( array( ) )
                    foreach($value as $k => $v) {
                        if (!is_numeric($k)) {
                            $res[] = $this->condition($v, $k);
                        } else {
                            $res[] = $this->condition($v, 'AND');
                        }
                    }
                } else {
                    // OR = array( )
                    $res[] = $this->condition($value, $key);
                }
                $cond = $key;
                continue;
            }

            // prepare key
            $c = self::cond($key);
            $key = self::field($key);
            if ($c == "") {
                $c = "=";
            }
            if ($c == "%") {
                $c = " LIKE ";
                $value = '%'.$value.'%';
            }

            // prepare value
            if (is_numeric($value)){
                // nothing
            } elseif (is_string($value)) {
                if (strpos($value, '*.') !== false) {
                    $value = $this->findAndReplaceTableName($value);
                // Для Join запросов, считаем все, что с точкой - формула
                } elseif ( (strpos($value, '.') === false || $is_join == false) && self::isField($value) === false) { // функции и поля пропускаем
                    $value = '"'.self::escape($value).'"';
                } else {
                    $value = self::escape($value);
                }
            } elseif (is_null($value) || $value === 'NULL') {
                $c = ($c == '=' ? ' IS ' : ' IS NOT ');
                $value = 'NULL';
            } elseif (is_bool($value)) {
                $value = (int)$value;
            } elseif (is_array($value)) {
                $value = self::range($value);
                $c = ($c == "=" ? " IN " : " NOT IN ");
            } else {
                return new CException('Error condition. Unexpected type param.');
            }

            // prepare key
            if (strpos($key, '*.') !== false) {
                $key = $this->findAndReplaceTableName($key);
            } elseif (strpos($key, '.') === false) {
                $key = "`$key`";
            }

            $res[] = $key.$c.$value;
        }
        return count($res) > 0 ? '(' . implode(' '.$cond.' ', $res) .')' : '';
    }
    /**
      * Добавляет к запросу конструкцию WHERE
      * @param array|int $mixed ID или список условий
      * @param string $cond - условие AND, OR, XOR для присоединения к существующим условиям
      * Список условий может иметь следующие форматы
      * <pre>
      * array('ID>=' => 5)
      * array('ID' => array(1,2,3,4,5), 'NAME%' => 'Alexander')
      * array( array('OR' => array('!ID' => array(1,2,3,4,5), 'NAME%' => 'Alexander'))
      * </pre>
      * @return CQuery
      */
    public function where($mixed = false, $cond = 'AND') {
        $where = false;
        if ($mixed) {
            if (is_numeric($mixed) || is_array($mixed) && key($mixed) === 0 && is_numeric(current($mixed))) {
                $where = $this->condition(array('ID' => self::int($mixed)));
            } else if (is_array($mixed)) {
                $where = $this->condition($mixed, $cond);
            }
        }
        if ($where) {
            $this->_where = $this->_where ? '(' . $this->_where . ' '.$cond. ' '.$where . ')' : $where;
        }
        return $this;
    }
    /**
      * Добавляет к запросу конструкцию HAVING
      * @param array $condition список условий
      * @param string $cond - условие AND, OR, XOR для присоединения к существующим условиям
      */
    public function having(Array $condition, $cond = 'AND') {
        if ($condition) {
            if ($having = $this->condition($condition, $cond)) {
                $this->_having = $this->_having ? '(' . $this->_having . ' '.$cond. ' '.$having . ')' : $having;
            }
        }
        return $this;
    }
    /**
      * Добавляет к запросу конструкцию ORDER BY
      * @param array $arr список для сортировки array(ID, ASC) или array(ID => ASC, ...) , * - указывает на текущее имя таблицы
      * @return CQuery
      */
    public function order(Array $arr = null) {
        if ($arr) {
            $res = array();
            foreach($arr as $field => $order) {
                if (is_numeric($field)) {
                    $res[0] .= ' '.$order;
                } else {
                    $res[] = $this->findAndReplaceTableName($field). ' '.$order;
                }
            }
            $this->_order = ' ORDER BY '. implode(', ', $res).' ';
        }
        return $this;
    }
    /**
      * Добавляет к запросу конструкцию GROUP BY
      * @param array|string $groups поле или список полей для группировки, * - указывает на текущее имя таблицы
      * @return CQuery
      */
    public function group($groups = null) {
        if ($groups) {
            $res = array();
            foreach((array)$groups as $group) {
                $res[] = $this->findAndReplaceTableName($group);
            }
            $this->_group = ' GROUP BY '.implode(', ', $res) .' ';
        }
        return $this;
    }
    /**
      * Добавляет к запросу конструкцию LIMIT offset, limit
      * @param int|array $mixed число записей limit или массив array(offset, limit)
      * @return CQuery
      */
    public function limit($mixed, $limit=null) {
        if ($limit) {
            $offset = intval($mixed);
            $limit = intval($limit);
        } else if (is_array($mixed) && count($mixed) == 2) { // deprecated
            $offset = intval($mixed[0]);
            $limit = intval($mixed[1]);
        } else {
            $offset = 0;
            $limit = intval($mixed);
        }
        $this->_limit = ' LIMIT ' . $offset . ', ' . $limit;
        return $this;
    }
    /**
      * Перевод массив или строку к целому числу или массиву из целых чисел
      * @param string|array $int
      * @return int|array
      */
    public static function int($int) {
        if (is_array($int)) {
            $ii = array();
            foreach($int as $i) {
                $ii[] = self::int($i);
            }
            return $ii;
        }
        return intval($int);
    }
    /**
      * Проверяет, что выражение формула
      * @param string $string - выражение
      * @return boolean
      */
    public static function isField(&$string){
        if (strpos($string, 'DATE_ADD(') === 0 || strpos($string, 'NOW(') === 0) {
            return true;
        }
        $secret = self::getSecretKey();
        if (mb_strpos($string, $secret) !== false) {
            $string = mb_substr($string, mb_strlen($secret));
            return true;
        }
        return false;
    }
    /**
      * Обозначает выражение как формулу
      * @param string $string - выражение
      * @return string
      */
    public static function Formula($string) {
        $secret = self::getSecretKey();
        return $secret.str_replace($secret, '', $string);
    }
    /**
      * @todo Сделать генерируемый ключ
      */
    private static function getSecretKey() {
        if (!self::$secret) {
            self::$secret = CTool::random(16);
        }
        return '{'.self::$secret.'}';
    }
}