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

class CModel extends CDataObject {

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
      * Возвращает тип модели, object|array
      * @return string
      */
    public static function modelType() {return self::m()->_modelType;}
    /**
      * Возвращает имя таблицы БД для модели к которой обратились через данный метод
      * @return string
      */
    public static function tableName() {
        $name = self::m()->_name;
        $prefix = COrmModule::GetInstance()->config('prefix', '');
        return str_replace('{{' . $name . '}}', $prefix . $name, $name);
    }
    /**
      * Возвращает имя модели к которой обратились через данный метод
      * @return string
      */
    public static function label() { return self::m()->_label;}
    /**
      * Возвращает список столбцов модели к которой обратились через данный метод
      * @return array
      */
    public static function tableColumns() { return self::m()->_columns;}

    /** @ignore */
    public static function getColumn($k) { $cols = self::tableColumns(); return isset($cols[$k]) ? $cols[$k] : false;}
    /** @ignore */
    public static function getType($k) { $col = self::getColumn($k); return $col ? $col['type'] : false;}
    /** @ignore */
    public static function getFieldKeys() { return array_keys(self::tableColumns());}
    /** @ignore */
    public static function getSize($k) { $col = self::getColumn($k); return ($col && isset($col['size'])) ? $col['size'] : 0;}
    /** @ignore */
    public static function getLabel($k) { $col = self::getColumn($k); return ($col && isset($col['label'])) ? $col['label'] : $k;}
    /** @ignore */
    public static function getObject($k) { $col = self::getColumn($k); return ($col && isset($col['object'])) ? $col['object'] : false;}
    /** @ignore */
    public static function getFieldNames() { $arr = array(); foreach(self::getFieldKeys() as $k) $arr[$k] = self::getLabel($k); return $arr;}
    /** @ignore */
    public static function getTableRelations() {$arr = array(); foreach(self::getFieldKeys() as $k) if ($ob = self::getObject($k)) $arr[$k] = $ob; return $arr;}
    /**
      * Возвращает пустой массив записи модели к которой обратились через данный метод
      * @todo Нужно сделать согласно типу столбцов
      * @return array
      */
    public static function getEmpty() {return self::m()->emptyArray();}
    /**
      * Обновляет строку в БД таблицу модели
      *
      * @param int|array ;where - массив с условиями или id
      * @return int сколько строк затронуто
      */
    public static function Update(Array $fields, $where = "") {
        return self::q()->update($fields, $where);
    }
    /**
      * Вставляет строку в БД таблицу модели
      * @param array $fields - данные для вставки
      * @return int id новой записи
      * @todo Добавить обработку default в fields из self::tableColumns();
      */
    public static function Insert(Array $fields = null) {
        return self::q()->insert($fields);
    }
    /**
      * Удаляет строку из БД таблицы модели
      * @param int|array $where - массив с условиями или id
      * @return int сколько строк затронуто
      */
    public static function Delete($where = false) {
        return self::q()->remove($where);
    }

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

    private $_schema = '';
    public static function schema() {
        if (self::m()->_schema) {
            return self::m()->_schema;
        }
        self::m()->_schema = new CMigration(self::tableName());
        return self::m()->_schema;
    }
    /**
      * @ignore
      */
    public function __call($name, $ps) {
        $result = false;
        if($this->_modelType === self::MODEL_TYPE_ARRAY && method_exists(self::model(), 'Static' . $name)) {
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
    public static function m($className = "") {
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

    public static function q() {
        return new CQuery(self::tableName(), self::m()->_columns);
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
}