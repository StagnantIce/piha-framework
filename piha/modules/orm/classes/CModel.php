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
use piha\CException;

class CModel extends CDataObject {

    /** @var static array Кеширование объектов моделей */
    private static $_models = array();

    /** @var string Имя таблицы в Базе Данных */
    public $_name = '';

    /** @var string Имя модели, для вывода */
    protected $_label = '';

    private $_schema = '';

    private $_relationData = array();
    /** @var array Псевдо поля */
    //public $_fields = array();

    /** @var array Использовать заглавные буквы в именах полей */
    protected $_isUpperCase = true;

    const TYPE_ONE = 1;
    const TYPE_MANY = 2;

    /** primary key */
    public $_pk = 'ID';

    /** @var array Список связей моделей
      * <pre>
      * array('myModelsAdvanced' => array('ID',          'CMyModels',   'MY_MODEL_id', self::RELATION_TYPE_MANY))
      * array('myModelSimple'    => array('RELATION_ID', 'CMyRelation', 'ID',          self::RELATION_TYPE_ONE))
      * </pre>
    */
    public function getRelations() {
        return array();
    }

    public function getColumns() {
        return array();
    }

    public function isNew() {
        $pk = $this->toVar($this->_pk);
        return !$this->$pk;
    }

    /**
      * Возвращает имя таблицы БД для модели к которой обратились через данный метод
      * @return string
      */
    public static function tableName() {
        return COrmModule::quoteTableName(self::m()->_name);
    }
    /**
      * Возвращает имя модели к которой обратились через данный метод
      * @return string
      */
    public static function label() {
        return self::m()->_label;
    }

    /** @ignore */
    public static function StaticGetColumn($k) {
        $cols = self::m()->getColumns();
        return isset($cols[$k]) ? $cols[$k] : false;
    }
    /** @ignore */
    public static function StaticGetType($k) {
        $col = self::getColumn($k);
        return $col ? $col['type'] : false;
    }
    /** @ignore */
    public static function StaticGetFieldKeys() {
        return array_keys(self::m()->getColumns());
    }
    /** @ignore */
    public static function StaticGetLabel($k) {
        $col = self::getColumn($k);
        return ($col && isset($col['label'])) ? $col['label'] : $k;
    }
    /** @ignore */
    public static function StaticGetObject($k) {
        $col = self::getColumn($k);
        return ($col && isset($col['object'])) ? $col['object'] : false;
    }
    /** @ignore */
    public static function StaticGetFieldNames() {
        $arr = array();
        foreach(self::getFieldKeys() as $k) {
            $arr[$k] = self::getLabel($k);
        }
        return $arr;
    }
    /** @ignore */
    public static function StaticGetObjectField($className) {
        return array_search($className, self::StaticGetTableRelations());
    }
    /** @ignore */
    public static function StaticGetTableRelations() {
        $arr = array();
        foreach(self::StaticGetFieldKeys() as $k){
            if ($ob = self::StaticGetObject($k)) {
                $arr[$k] = $ob;
            }
        }
        return $arr;
    }
    /**
      * Возвращает пустой массив записи модели к которой обратились через данный метод
      * @todo Нужно сделать согласно типу столбцов
      * @return array
      */
    public function getEmpty() {
        $columnDefaults = array();
        foreach($this->getColumns() as $key => $column) {
            $columnDefaults[$key] = is_array($column) && isset($column['default']) ? $column['default'] : null;
        }
        return $columnDefaults;
    }
    /**
      * Обновляет строку в БД таблицу модели
      *
      * @param int|array $where - массив с условиями или id
      * @return int сколько строк затронуто
      */
    public static function StaticUpdate(Array $fields, $where = "") {
        return self::q()->update($fields, $where);
    }
    /**
      * Вставляет строку в БД таблицу модели
      * @param array $fields - данные для вставки
      * @return int id новой записи
      * @todo Добавить обработку default в fields из self::tableColumns();
      */
    public static function StaticInsert(Array $fields = null) {
        return self::q()->insert($fields);
    }
    /**
      * Удаляет строку из БД таблицы модели
      * @param int|array $where - массив с условиями или id
      * @return int сколько строк затронуто
      */
    public static function StaticDelete($where = false) {
        return self::q()->remove($where);
    }

    /**
      * Преобразует имя колонки таблицы в имя переменной PHP
      * @param string $key Имя столбца
      * @return string
      */
    public function toVar($key) {
        if (!$this->_isUpperCase) {
            return $key;
        }
        return lcfirst(implode('', array_map('ucfirst', explode('_', strtolower($key)))));
    }

    /**
      * Преобразует имя переменной PHP в колонку таблицы
      * @param string $var Имя переменной
      * @return string
      */
    public function toKey($var) {
        if (!$this->_isUpperCase) {
            return $var;
        }
        return strtoupper(preg_replace('/([A-Z])([a-z]+)/', '_$1$2', lcfirst($var)));
    }

    public function merge(Array $data = null, Array $props = null) {
        $data = array_replace($this->toArray(), $data ?: array());
        return $this->fromArray($data, $props);
    }

    private function collectRelationData(Array $data = null) {
        $this->_relationData = array();
        foreach($this->getRelations() as $type => $relation) {
            foreach($relation as $name => $classes) {
                if (isset($data[$name])) {
                    $this->_relationData[$name] = $data[$name];
                }
            }
        }
    }

    public function saveRelations() {
        $data = $this->_relationData;
        foreach($data as $name => $values) {
            $relations = $this->getRelations();
            if (isset($relations[self::TYPE_MANY][$name])) {
                $relation = $relations[self::TYPE_MANY][$name];
            } else if (isset($relations[self::TYPE_ONE][$name])) {
                $relation = $relations[self::TYPE_ONE][$name];
            }
            if (count($relation) == 3) {
                $relationModel = $relation[2];
                $className = $relation[1];
                $fieldOwn = $className::StaticGetObjectField(static::className());
                $fieldNext = $className::StaticGetObjectField($relationModel);
                $className::StaticDelete(array($fieldOwn => $this->id));
                foreach((array)$values as $id) {
                    $className::StaticGetOrCreate(array($fieldOwn => $this->id, $fieldNext => $id));
                }
            }
        }
    }

    /**
      * Переопределение метода
      * @return array
      */
    public function fromArray(Array $data = null, Array $props = null) {
        $keys = array_keys($this->getColumns());
        $this->_isUpperCase = COrmModule::Config('uppercase', $this->_isUpperCase);
        if($this->_isUpperCase && $keys !== array_map('strtoupper', $keys)) {
            throw new CException("Column names not in upper case.");
        }
        $this->collectRelationData($data);
        $dataObj = array();
        foreach($data as $key => $value) {
            $dataObj[$this->toVar($key)] = $value;
        }
        parent::fromArray($dataObj, $props);
    }
    /**
      * Вернуть данные из модели в виде массива
      * @return array
      */
    public function toArray(Array $props = null) {
        $result = array();
        $data = parent::toArray($props);
        foreach($data as $key => $value) {
            $result[$this->toKey($key)] = $value;
        }
        return $result;
    }

    /**
      * Создание модели
      *
      * @param array $data Данные для инициализации
      * @return CModel
      */
    public function __construct(Array $data = null) {
        $data = array_replace($this->getEmpty(), $data ?: array());
        $this->fromArray($data);
    }

    public static function schema() {
        if (self::m()->_schema) {
            return self::m()->_schema;
        }
        self::m()->_schema = new CMigration(self::tableName(), self::m()->getColumns());
        return self::m()->_schema;
    }
    /**
      * @ignore
      */
    public function __get($name) {
        /** advanced define */
        $relation = $type = null;
        $relations = $this->getRelations();
        if (isset($relations[self::TYPE_MANY][$name])) {
            $relation = $relations[self::TYPE_MANY][$name];
            $type = self::TYPE_MANY;
        }

        if (isset($relations[self::TYPE_ONE][$name])) {
            $relation = $relations[self::TYPE_ONE][$name];
            $type = self::TYPE_ONE;
        }
        if ($relation) {
            $fieldName = array_shift($relation);
            if ($this->_isUpperCase) {
                $field = $this->toVar($fieldName);
            } else {
                $field = $fieldName;
            }
            $columns = $this->getColumns();
            if (!isset($columns[$fieldName])) {
                throw new CException("Relation field '{$field}' not defined");
            }
            $q = null;
            $prevClassName = null;
            $relation = array_reverse($relation);
            foreach($relation as $className) {
                if ($q) {
                    $q->left(array($className => array('#.' . $className::StaticGetObjectField($prevClassName) => '*.' . $className::m()->_pk)));
                } else {
                    $q = $className::q();
                }
                $prevClassName = $className;
            }
            $whereField = $prevClassName::StaticGetObjectField(static::className());
            $whereField = $whereField ?: $prevClassName::m()->_pk;
            $where = array($prevClassName::tableName() . '.'.$whereField => $this->$field);
            return $type === self::TYPE_MANY ? $q->objects($where) : $q->object($where);
        }
        /** simple define */
        $key = strtoupper($name . '_' . $this->_pk);
        if ($object = static::StaticGetObject($key)) {
            $data = $this->toArray();
            return isset($data[$key]) ? $object::Get($data[$key]) : null;
        }
        return parent::__get($name);
    }

    /**
     * @param string $className Имя класса
     * @return bool|CModel
     */
    public static function m() {
        $className = static::className();

        if(isset(self::$_models[$className])) {
            return self::$_models[$className];
        }

        self::$_models[$className] = new static();
        return self::$_models[$className];
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
    public static function StaticExists($where) {
        return self::q()->where($where)->count();
    }

    /**
      * Сохраняет объект в БД
      * @return int id сохраненной записи
      */
    public function Save() {
        $data = self::StaticUpdateOrInsert($this->toArray(), $this->id);
        $this->id = $data->id;
        return $data->id;
    }

    /**
      * Пытается загрузить поля в объект из БД по ID или другим полям
      * @param array $by список параметров для условия
      * @return boolean удалось ли загрузить объект по ID или другим полям
      */
    public function Load(Array $by = null) {
        $where = $by ? $this->toArray($by) : $this->id;
        if ($where) {
            if ($data = self::StaticGet($where)) {
                $this->fromObject($data);
                return true;
            }
            throw new CException("Model not loaded");
        }
        throw new CException("Model load condition not found");
    }

    /**
      * Пытается удалить объект из БД по ID
      * @param array $by список параметров для условия
      * @return boolean удалось ли удалить объект по ID
      */
    public function Remove(Array $by = null) {
        $where = $by ? $this->toArray($by) : $this->id;
        if ($where) {
            return self::StaticDelete( $where );
        }
        throw new CException("Model remove condition not found");
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
    public static function StaticGetArrayObjects($key='ID', $where = array(), $order = array()) {
        return self::q()->order($order)->objects($where, 'AND', $key);
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
    public static function StaticGetCodeName($code) {
        if ($obj = self::StaticGetByCode($code)) {
            return $obj->name;
        }
        return false;
    }
    /**
      * @ignore
      */
    public static function StaticGetCodeID($code) {
        if ($obj = self::StaticGetByCode($code)) {
            return $obj->id;
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
        self::StaticInsert($where);
        return self::StaticGet($where);
    }

    /**
      * Обновляет или вставляет объект в БД
      * @param array $fields поля для вставки / обновления
      * @param int|array $where условие на обновление
      * @static
      * @return array|CModel запись
      */
    public static function StaticUpdateOrInsert(Array $fields, $where = "") {
        if ($where) {
            CCore::Validate($where, array('int', 'array'), true);
        }
        if ($where && self::StaticExists($where)) {
            self::StaticUpdate($fields, $where);
        } else {
            $where = self::StaticInsert($fields);
        }
        return self::StaticGet($where);
    }

    /**
      * @ignore
      */
    public static function StaticSet($where = "", Array $fields) {
        CCore::Validate($where, array('int', 'array'), true);
        return self::StaticUpdate($fields, $where);
    }

    //  ArrayAccess interface
    public function offsetExists($offset) {
        return parent::offsetExists($this->toVar($offset));
    }
    /** @ignore */
    public function offsetGet ( $offset ) {
        return parent::offsetGet( $this->toVar($offset));
    }
    /** @ignore */
    public function offsetSet ( $offset ,  $value ) {
        return parent::offsetSet( $this->toVar($offset), $value);
    }
    /** @ignore */
    public function offsetUnset ( $offset ) {
        return parent::offsetUnset( $this->toVar($offset));
    }
}