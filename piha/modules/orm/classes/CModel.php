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
namespace piha\modules\orm\classes;

use piha\modules\orm\COrmModule;
use piha\modules\core\classes\CCore;

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

    /** @var array Список связей моделей
      * <pre>
      * array('myModelsAdvanced' => array('ID',          'CMyModels',   'MY_MODEL_id', self::RELATION_TYPE_MANY))
      * array('myModelSimple'    => array('RELATION_ID', 'CMyRelation', 'ID',          self::RELATION_TYPE_ONE))
      * </pre>
    */
    public function getRelations() {
        return array();
    }

    const RELATION_TYPE_ONE = 'one';
    const RELATION_TYPE_MANY = 'many';

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
    public function getEmpty() {
        $columnDefaults = array();
        foreach($this->_columns as $key => $column) {
            $columnDefaults[$key] = is_array($column) && isset($column['default']) ? $column['default'] : null;
        }
        return $columnDefaults;
    }
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
      * Преобразует имя колонки таблицы в имя переменной PHP
      * @param string $key Имя столбца
      * @return string
      */
    private function toVar($key) {
        return lcfirst(implode('', array_map('ucfirst', explode('_', strtolower($key)))));
    }

    /**
      * Создание модели
      *
      * @param array $data Данные для инициализации
      * @return CModel
      */
    public function __construct(Array $data = null) {
        $data = array_replace($data ?: array(), $this->getEmpty());
        $keys = array_keys($this->_columns);
        if($keys !== array_map('strtoupper', $keys)) {
            throw new CException("Column names not in upper case.");
        }
        $dataObj = array();
        foreach($data as $key => $value) {
            $dataObj[$this->toVar($key)] = $value;
        }
        parent::__construct($dataObj);
    }

    private $_schema = '';
    public static function schema() {
        if (self::m()->_schema) {
            return self::m()->_schema;
        }
        self::m()->_schema = new CMigration(self::tableName(), self::tableColumns());
        return self::m()->_schema;
    }
    /**
      * @ignore
      */
    public function __call($name, $ps) {
        $result = false;
        if(method_exists(self::m(), 'Static' . $name)) {
            return call_user_func_array(array(self::m(), 'Static' . $name), $ps);
        }
        return parent::__call($name, $ps);
    }
    /**
      * @ignore
      */
    public function __get($name) {
        if (isset($this->_relations[$name])) {
            return $this->_relations[$name];
        }
        /** advanced define */
        $relations = static::m()->getRelations();
        if (isset($relations[$name])) {
            list($field, $class, $class_field, $type) = array_pad($relations[$name], 4, null);
            $type = $type ?: self::RELATION_TYPE_MANY;
            if (is_array($class_field)) {
                $where = array();
                foreach($this->toArray() as $k => $v) {
                    if (isset($class_field['*.'.$k])) {
                        $where[str_replace('#', '*', $class_field['*.'.$k])] = $v;
                    }
                }
            } elseif (is_string($class_field)) {
                $where = array('*.'.$class_field => $this->$field);
            } else {
                throw new CException("Error relation $name");
            }
            $this->_relations[$name] = $type == self::RELATION_TYPE_MANY ? $class::GetAll($where) : $class::Get($where);
            return $this->_relations[$name];
        }
        /** simple define */
        $key = strtoupper($name . '_ID');
        if (isset($this->_columns[$key]) && isset($this->_columns[$key]['object'])) {
            $object = $this->_columns[$key]['object'];
            $data = $this->toArray();
            $this->_relations[$name] = $data[$key] ? $object::Get($data[$key]) : null;
            return $this->_relations[$name];
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
        return CQuery::fromModel(static::className());
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
        $data = self::UpdateOrInsert($this->toArray(), $this->id ?: array());
        return $data['ID'];
    }

    /**
      * Пытается загрузить поля в объект из БД по ID или другим полям
      * @param array $by список параметров для условия
      * @return boolean удалось ли загрузить объект по ID или другим полям
      */
    public function Load(Array $by = null) {
        $where = $by ? $this->toArray($by) : $this->id;
        if ($where && $data = self::StaticGet($where)) {
            $this->fromArray(is_object($data) ? $data->toArray() : $data);
            return true;
        }
        return false;
    }

    /**
      * Пытается удалить объект из БД по ID
      * @param array $by список параметров для условия
      * @return boolean удалось ли удалить объект по ID
      */
    public function Remove(Array $by = null) {
        $where = $by ? $this->toArray($by) : $this->id;
        if ($where) {
            return self::Delete( $where );
        }
        return false;
    }
    /**
      * @ignore
      */
    public static function StaticGet($where = array(), $fields = false) {
        CCore::Validate($where, array('int', 'array'), true);
        CCore::Validate($fields, array('string', 'array', 'boolean'), true);
        return $fields ? self::q()->where($where)->one($fields) : self::q()->object($where);
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
    public static function StaticGetArray($key='ID', $field=false, $where = array(), $order = array()) {
        return self::q()->where($where)->order($order)->all($field, $key);
    }
    /**
      * @ignore
      */
    public static function StaticGetArrayName($where = array(), $order = array()) {
        return self::StaticGetArray('ID', 'NAME', $where, $order);
    }
    /**
      * @ignore
      */
    public static function StaticGetArrayCode($where = array(), $order = array()) {
        return self::StaticGetArray('ID', 'CODE', $where, $order);
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
        return $fields ? self::q()->where($where)->all($fields) : self::q()->objects($where);
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
        if ($where && self::StaticGet($where)) {
            self::Update($fields, $where);
        } else {
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