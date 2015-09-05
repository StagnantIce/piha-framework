<?php

/**
* CModel
* класс для описания структуры таблицы БД и методов доступа к ней
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @method addColumn($column, $type) CMigration
* @method dropColumn($column, $type) CMigration
* @method dropTable() CMigration
* @method static CQuery GetList($select = array(), $filters = null, $related = true, $nav = true, $other_methods = false) - Выдает список сущностей
*
* @method static array GetArrayRelated(string $key, string $name, array|int $where, array $inner) возращает ассоциативный массив по ключу
* @method static array GetObject(int|array $where, array $fields) возращает объект по условию
* @method static array GetObjects(int|array $where, array $fields) возращает объекты по условию
* @method static array GetArray(string $key, string $name, mixed $where = array(), array $inner = array()) возращает массив по ключу
* @method static array GetArrayFromQuery(string $query, string $key, string $name) возращает массив по sql запросу
* @method static array Get(int|array $where, array $fields) возращает первую запись по условию
* @method static int GetID(int|array $where = array()) возращает первый ID по условию
* @method static array GetIDs(int|array $where) возращает все ID по условию
* @method static string GetCode(int|array $where) возращает первое значения поля CODE по условию
* @method static string GetName(int|array $where) возращает первое значения поля NAME по условию
* @method static array GetArrayName(int|array $where = array(), array $inner = array(), array $order = array()) возращает массив ID[NAME]
* @method static array GetArrayCode(int|array $where, array $inner) возращает массив ID[CODE]
* @method static int GetCodeID(string $code) возращает ID по коду
* @method static array|CModel|Exception GetByCode(string $code) возращает запись по коду
* @method static array|CModel|Exception GetByName(string $name) возращает запись по имени
* @method static array GetAll(array $where = array(), array $fields = array()) возращает все запись по условию, с указанием полей
* @method static array GetOrCreate(array $where) создает запись в БД если ее нет, и возвращает ее
* @method static array Set(array|int $where, array $fields) делает, что и Update, но условие идет впереди
*/

class CModel extends CAdminModel {

    /** @var static array Кеширование объектов моделей */
    private static $_models = array();

    /** @var array Преобразования перед вытаскиванием из базы */
    public $_getters = array();

    /** @var string Имя таблицы в Базе Данных */
    public $_name = '';

    /** @var string Имя модели, для вывода */
    public $_label = '';

    /** @var array Список сохраненных связей */
    public $_relations = array();

    /** @var array Список связей моделей */
    public function getRelations() {
        return array();
    }

    const MODEL_TYPE_OBJECT = 'object';
    const MODEL_TYPE_ARRAY = 'array';

    const RELATION_TYPE_ONE = 'one';
    const RELATION_TYPE_MANY = 'many';

    /** @var string Тип вытаскиваемых объектов из модели, массив или объект */
    public $_modelType = self::MODEL_TYPE_ARRAY;

    /**
      * Создание модели
      *
      * @param array $data Данные для инициализации
      * @return CModel
      */
    public function __construct(Array $data = null) {
        if (!is_null($data)) {
            $this->_modelType = self::MODEL_TYPE_OBJECT;
        }

        if ($this->_modelType == self::MODEL_TYPE_OBJECT) {
            parent::__construct($data);
        }
    }

    /**
      * Удаление всех индексов из таблицы
      *
      * @return null
      */
    public function dropIndexTable() {
        $table = $this->tableName();
        $res = CQuery::create()->setQuery("SHOW CREATE TABLE $table")->execute();
        while ($row = $res->Fetch()) {
            // remove constrains
            if(preg_match_all('/CONSTRAINT `(.*)` FOREIGN KEY/', next($row), $matchArr)) {
                foreach($matchArr[1] as $key) {
                    try { $this->dropForeignKey($key); } catch(Exception $e){}
                }
            }
            // remove keys
            if(preg_match_all('/KEY `(.*)` \(/', $row[1], $matchArr)) {
                foreach($matchArr[1] as $key) {
                    try { $this->dropIndex($key); } catch(Exception $e){}
                }
            }
        }
    }

    /**
      * Создание индексов для таблицы (должно быть в описании столбцов)
      *
      * @param boolean $drop Нужно ли удалять индекс перед созданием
      * @return null
      */
    public function createIndexTable($drop = true) {
        if ($drop) {
            $this->dropIndexTable();
        }
        $types = $this->_columns;
        $prefix = CCore::module('orm')->config('prefix', '');
        foreach($this->_columns as $key => $column) {
            if (is_array($column)) {
                if ($model = self::getObject($key)) {
                    $name = $model::tableName();
                    $delete = isset($column['delete']) ? $column['delete']: null;
                    $update = isset($column['update']) ? $column['update']: null;
                    $keyName = 'fk_' . strtolower($key) . '__' . str_replace($prefix, '', $this->_name) . '__' . str_replace($prefix, '', $name);
                    if (mb_strlen($keyName) > 30) {
                        $keyName = 'fk_' . strtolower($key) . '_'. md5($keyName);
                    }
                    $this->addForeignKey($keyName, $key, $name, 'ID', $delete, $update);
                }
                if (isset($column['index'])) {
                    if ($column['index'] === true) {
                        $this->createIndex('k_'. strtolower($key), $key, isset($column['unique']));
                    } else {
                        $columns=preg_split('/\s*,\s*/',$column['index'],-1,PREG_SPLIT_NO_EMPTY);
                        $this->createIndex('k_'. strtolower(implode('__', $columns)), $column['index'], isset($column['unique']));
                    }
                }
            }
        }
    }
    /**
      * Добавление столбца на основании описания
      *
      * @param string $key название столбца
      * @param string $type тип столбца (не обязательно)
      * @return string SQL код
      */
    public function addColumn($key, $type=null)
    {
        $column = $this->_columns[$key];
        $type = $type ?: $column['type'] . (isset($column['default']) ? " DEFAULT '".$column['default']."'": '');
        return $this->addColumn2($key, $type);
    }
    /**
      * Создание таблицы на основании описания столбцов
      *
      * @param boolean $index Нужно ли индексы после создания таблицы
      * @return null
      */
    public function createTable($index = false) {
        $types = $this->_columns;
        foreach($this->_columns as $key => $column) {
            if (is_array($column)) {
                $types[$key] = $column['type'] . (isset($column['default']) ? " DEFAULT '".$column['default']."'": '');
            } else {
                $types[$key] = $column;
            }
        }
        $this->createTable2($types);
        if ($index) {
            $this->createIndexTable();
        }
    }

    /**
      * Инстяляция модуля, метод можно переопределить если необходимо
      */
    public function install() {}

    /**
      * @ignore
      */
    public function __call($name, $ps) {
        $result = false;
        if(method_exists('CMigration', $name)) {
            $object = new CMigration();
            array_unshift($ps, $this->_name);
            $query = call_user_func_array(array(&$object, $name), $ps);
            unset($object);
            return CQuery::create()->setQuery($query)->execute();
        } else if($this->_modelType === self::MODEL_TYPE_ARRAY && method_exists(self::model(), 'Static' . $name)) {
            return call_user_func_array(array(self::model(), 'Static' . $name), $ps);
        }
        return parent::__call($name, $ps);
    }
    /**
      * @ignore
      */
    public function __get($name) {
        if ($this->_modelType === self::MODEL_TYPE_OBJECT) {
            if (isset($this->_relations[$name])) {
                return $this->_relations[$name];
            }
            $relations = static::m()->getRelations();
            if (isset($relations[$name])) {
                $r = $relations[$name];
                $object = $r['object'];
                $type = isset($r['type']) ? $r['type']: self::RELATION_TYPE_MANY;
                $condition = $r['condition'];
                $where = array();
                foreach($this->toArray() as $k => $v) {
                    if (isset($condition['*.'.$k])) {
                        $where[str_replace('#', '*', $condition['*.'.$k])] = $v;
                    }
                }
                $this->_relations[$name] = $type == self::RELATION_TYPE_MANY ? $object::GetAll($where) : $object::Get($where);
                return $this->_relations[$name];
            }
            $key = strtoupper($name . '_ID');
            if (isset($this->_columns[$key]) && isset($this->_columns[$key]['object'])) {
                $object = $this->_columns[$key]['object'];
                $data = $this->toArray();
                $this->_relations[$name] = $data[$key] ? $object::Get($data[$key]) : null;
                return $this->_relations[$name];
            }
        }
        return parent::__get($name);
    }

    /**
      * @ignore
      */
    public function __set($name, $value) {
        if (isset($this->_relations[$name])) {
            unset($this->_relations[$name]);
        }
        return parent::__set($name, $value);
    }

    /**
     * @param string $className Имя класса
     * @return bool|CModel
     */
    public static function model($className = "") {
        if ($className === "") {
            $className = self::className();
        }

        if(isset(self::$_models[$className]))
            return self::$_models[$className];
        elseif(class_exists($className)) {
            self::$_models[$className] = new $className(null);
            return self::$_models[$className];
        } else {
            return false;
        }
    }
    /**
      * Формирует запись согласно объекту CQuery
      * @static
      * @param CQuery $q объект запроса
      * @param array $fields список полей
      * @param boolean $object вернуть объект
      * @return array|CModel
      */
    public static function Fetch(CQuery $q, $fields = false, $object = false) {
        $row = $q->Fetch(); // return false if end
        if (!$row || (!$fields && self::modelType() === self::MODEL_TYPE_ARRAY && !$object)) {
            return $row;
        }

        if (!$fields && self::modelType() === self::MODEL_TYPE_OBJECT || $object) {
            return new static($row);
        }

        return CQuery::parseRow($row, $fields);
    }
    /**
      * Формирует массив из записей согласно объекту CQuery
      * @static
      * @param CQuery $q объект запроса
      * @param array $fields список полей
      * @param boolean $object вернуть объекты
      * @return array
      */
    public static function FetchAll(CQuery $q, $fields = false, $object = false) {
        $ret = array();
        while ( ($row = self::fetch($q, $fields, $object)) || ($row !== false)) {
            if (!is_null($row)) {
                $ret[] = $row;
            }
        }
        return $ret;
    }
    /**
      * Создает запись согласно sql запросу
      * @static
      * @param string $query sql запрос
      * @param array $fields список полей
      * @return array
      * @todo переписать на CQuery
      */
    public static function Parse($query, $fields = false) {
        return self::fetch(self::execute($query), $fields);
    }
    /**
      * Создает записи согласно sql запросу
      * @static
      * @param string $query sql запрос
      * @param array $fields список полей
      * @return array
      * @todo переписать на CQuery
      */
    public static function ParseAll($query, $fields = false) {
        return self::fetchAll(self::execute($query), $fields);
    }

    /**
      * Проверяет, существует ли объект по условию
      * @static
      * @param int|array $where условия или ID
      * @return boolean
      */
    public static function Exists($where) {
        return self::StaticGet($where) ? true: false;
    }
    /**
      * @ignore
      */
    public static function StaticGetArrayRelated($key = 'ID', $name = "", $where = array(), $inner = array()) {
        CCore::Validate($where, array('int', 'array'), true);
        return CTools::toArray(self::selectAll(false, array('WHERE' => $where, 'inner' => $inner), true), $key, $name);
    }

    /**
      * @ignore
      * @deprecated
      */
    public static function StaticGetObject($where = array(), $fields = false) {
        $row = self::StaticGet($where, $fields);
        return new CObjectModel($row['ID'], $row, self::m());
    }

    /**
      * @ignore
      * @deprecated
      */
    public static function StaticGetObjects($where = array(), $fields = false) {
        $arr = self::StaticGetAll($where, $fields);
        $objs = array();
        foreach($arr as $row) {
            $objs[] = new CObjectModel($row['ID'], $row, self::m());
        }
        return $objs;
    }
    /**
      * @ignore
      */
    public static function StaticGetArray($key = 'ID', $name = "", $where = array(), $inner = array(), $order = array()) {
        CCore::Validate($where, array('int', 'array'), true);
        return CTools::toArray(self::selectAll(false, array('WHERE' => $where, 'inner' => $inner, 'order' => $order), false), $key, $name);
    }
    /**
      * @ignore
      */
    public static function StaticGetArrayFromQuery($query, $key = 'ID', $name = "") {
        return CTools::toArray(self::fetchAll(self::execute($query)), $key, $name);
    }

    /**
      * Сохраняет объект в БД
      * @return int id сохраненной записи
      */
    public function Save() {
        $data = self::UpdateOrInsert($this->toArray(), $this->GetID() ?: array());
        return $data['ID'];
    }

    /**
      * Пытается загрузить поля в объект из БД по ID или другим полям
      * @param array $by список параметров для условия
      * @return boolean удалось ли загрузить объект по ID или другим полям
      */
    public function Load(Array $by = null) {
        $where = $by ? $this->toArray($by) : $this->GetID();
        if ($where && $data = self::StaticGet($where)) {
            $this->fromArray(is_object($data) ? $data->toArray() : $data);
            return true;
        }
        return false;
    }

    /**
      * Пытается удалить объект из БД по ID
      * @return boolean удалось ли удалить объект по ID
      */
    public function Erase() {
        if ($id = $this->GetID()) {
            return self::Delete( $id );
        }
        return false;
    }
    /**
      * @ignore
      */
    public static function StaticGet($where = array(), $fields = false) {
        CCore::Validate($where, array('int', 'array'), true);
        CCore::Validate($fields, array('string', 'array', 'boolean'), true);
        return self::parse(self::q()->where($where)->getQuery($fields), $fields);
    }
    /**
      * @ignore
      */
    public static function StaticGetID($where = array()) {
        return self::StaticGet($where, 'ID');
    }
    /**
      * @ignore
      */
    public static function StaticGetIDs($where = array()) {
        return self::StaticGetAll($where, 'ID');
    }
    /**
      * @ignore
      */
    public static function StaticGetCode($where = array()) {
        return self::StaticGet($where, 'CODE');
    }
    /**
      * @ignore
      */
    public static function StaticGetName($where = array()) {
        return self::StaticGet($where, 'NAME');
    }
    /**
      * @ignore
      */
    public static function StaticGetArrayName($where = array(), $inner = array(), $order = array()) {
        return self::StaticGetArray('ID', 'NAME', $where, $inner, $order);
    }
    /**
      * @ignore
      */
    public static function StaticGetArrayCode($where = array(), $inner = array()) {
        return self::StaticGetArray('ID', 'CODE', $where, $inner);
    }
    /**
      * @ignore
      */
    public static function StaticGetCodeID($code) {
        if ($obj = self::StaticGetByCode($code)) {
            return $obj['ID'];
        }
        return false;
    }
    /**
      * @ignore
      */
    public static function StaticGetByCode($code) {
        return self::StaticGet(array('CODE' => CQuery::escape($code)));
    }
    /**
      * @ignore
      */
    public static function StaticGetByName($name) {
        return self::StaticGet(array('NAME' => CQuery::escape($name)));
    }
    /**
      * @ignore
      */
    public static function StaticGetAll($where = array(), $fields = false) {
        CCore::Validate($where, array('int', 'array'), true);
        CCore::Validate($fields, array('string', 'array', 'boolean'), true);

        return self::parseAll(self::q()->where($where)->getQuery($fields), $fields);
    }

    /**
      * @ignore
      */
    public static function StaticGetOrCreate(Array $where = null) {
        if ($get = self::StaticGet($where)) {
            return $get;
        }
        self::Insert($where);
        return self::StaticGet($where);
    }

    /**
      * Обновляет или вставляет объект в БД
      * @param array $fields поля для вставки / обновления
      * @param int|array $where условие на обновление
      * @static
      * @return array|CModel запись
      */
    public static function UpdateOrInsert(Array $fields, $where = "") {
        CCore::Validate($where, array('int', 'array'), true);
        if (!$where || self::Update($fields, $where) == 0) {
            $where = self::Insert($fields);
        }
        return self::StaticGet($where);
    }

    /**
      * @ignore
      */
    public static function StaticSet($where = "", Array $fields) {
        CCore::Validate($where, array('int', 'array'), true);
        return self::Update($fields, $where);
    }

    /**
      * Делает выбор записи из БД
      * @param array $fields поля таблиц
      * @param array $methods методы объекта CQuery
      * @param boolean @related загружать ли связанные таблицы
      * @deprecated �?спользуйте статический метод q() для выполнения запросов
      * @return array|CModel
      * @static
      */
    public static function Select($fields = false, $methods = null, $related = false) {
        $res = $related ? self::m()->queryRelated($methods) : self::m()->query($methods);
        return self::m()->fetch($res, $fields);
    }

    /**
      * Делает выбор записей из БД
      * @param array $fields поля таблиц
      * @param array $methods методы объекта CQuery
      * @param boolean @related загружать ли связанные таблицы
      * @deprecated �?спользуйте статический метод q() для выполнения запросов
      * @return array
      * @static
      */
    public static function SelectAll($fields = false, $methods = null, $related = false) {
        $res = $related ? self::m()->queryRelated($methods) : self::m()->query($methods);
        return self::m()->fetchAll($res, $fields);
    }
}