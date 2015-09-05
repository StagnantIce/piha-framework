<?php

/**
* CAdminModel
* класс для работы с моделями в админке
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @abstract
*/

abstract class CAdminModel extends CDataObject {

    /**
      * Возвращает объект для модели к которой обратились через данный метод
      * @return CModel
      */
    public static function m() {return static::model();}

    /**
      * Возвращает новый объект запроса для модели к которой обратились через данный метод
      * @return CQuery
      */
    public static function q() {return new CQuery(self::m()->_name, self::m()->_columns);}
    /**
      * Выполняет sql запрос к таблице модели к которой обратились через данный метод
      * @return CQuery
      */
    public static function execute($query) {return self::q()->setQuery($query)->execute();}
    /**
      * Выполняет sql запрос с пейджинацией к таблице модели к которой обратились через данный метод
      * @return CQuery
      */
    public static function executeNav($query, $size = 20, $numPage = false) {return self::q()->setQuery($query)->execute($size, $numPage);}
    /**
      * Возвращает тип модели, object|array
      * @return string
      */
    public static function modelType() {return self::m()->_modelType;}
    /**
      * Возвращает имя таблицы БД для модели к которой обратились через данный метод
      * @return string
      */
    public static function tableName() { return self::m()->_name;}
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
    public static function getType($k) { $col = self::getColumn( str_replace(array('_1', '_2'), '', $k) ); return $col ? $col['type'] : false;}
    /** @ignore */
    public static function getFieldKeys() { return array_keys(self::tableColumns());}
    /** @ignore */
    public static function getSize($k) { $col = self::getColumn($k); return (isset($col) && isset($col['size'])) ? $col['size'] : 0;}
    /** @ignore */
    public static function getLabel($k) { $col = self::getColumn($k); return (isset($col) && isset($col['label'])) ? $col['label'] : $k;}
    /** @ignore */
    public static function getObject($k) { $col = self::getColumn($k); return (isset($col) && isset($col['object'])) ? $col['object'] : false;}
    /** @ignore */
    public static function getFieldNames() { $arr = array(); foreach(self::getFieldKeys() as $k) $arr[$k] = self::getLabel($k); return $arr;}
    /** @ignore */
    public static function getTableRelations() {$arr = array(); foreach(self::getFieldKeys() as $k) if ($ob = self::getObject($k)) $arr[$k] = $ob; return $arr;}
    /**
      * Возвращает пустой массив записи модели к которой обратились через данный метод
      * @todo Нужно сделать согласно типу столбцов
      * @return array
      */
    public static function getEmpty() {$arr = array(); foreach(self::getFieldKeys() as $k) $arr[$k] = "";return $arr;}
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
      * Выполняет запрос к модели через массив методов
      * @deprecated используйте метод q() для построения запросов
      * @param array $methods - методы объекта CQuery
      * @param boolean $nav - с пейджинацией или без
      * @param int $pageSize - количество строк для вывода
      * @return CQuery
      */
    public static function query(Array $methods = null, $nav = false, $pageSize = 20) {
        $cQuery = self::buildQuery($methods);
        return $nav ? self::executeNav($cQuery->getQuery(), $pageSize, false) : self::execute($cQuery->getQuery());
    }

    /**
     * @deprecated используйте метод q() для построения запросов
     * @param $methods
     * @return CQuery
     */
    public static function buildQuery($methods){
        if (!is_null($methods)) {
            $obj = self::q();
            foreach($methods as $method => $param) {
                if(is_numeric($method)) {
                    $method = $param;
                    $param = null;
                }
                call_user_func(array($obj, $method), $param);
            }
        }
        return $obj;
    }
    /**
      * Выполняет запрос к модели через массив методов с присоединением смежных таблиц
      * @deprecated используйте метод q() для построения запросов
      * @param array $methods - методы объекта CQuery
      * @param boolean $nav - с пейджинацией или без
      * @param int $pageSize - количество строк для вывода
      * @return CQuery
      */
    public static function queryRelated(Array $methods = null, $nav = false, $pageSize = 20) {
        $join = array();
        $select = array();

        $relations = self::getTableRelations();
        if (count($relations) > 0) {
            $join = array_keys($relations);
            $select = array('*');
            foreach($relations as $key => $modelName) {
                $model = CModel::model($modelName);
                if (!is_object($model)) {
                    throw new Exception ($modelName . ' model does not exists');
                }
                $keys = $model->getFieldKeys();
                $getters = $model->_getters;
                foreach($keys as $key2) {
                    if ($key2 <> 'ID') {
                        $value = str_replace('_ID', '', $key) . '_' . $key2;
                    } else {
                        $value = $key . '_' . $key2;
                    }
                    $key3 = $key . '.' . $key2;
                    if (isset($getters[$key2])) {
                        $key3 = call_user_func(array($model, $getters[$key2]), $key);
                    }

                    $select[ $key3 ] = $value;
                }
            }
        }

        $newMethods = Array();
        $newMethods['select'] = $select;
        $newMethods['left'] = $join;

        if ($methods) {
            $newMethods = array_merge($newMethods, $methods);
        }

        return self::query($newMethods, $nav, $pageSize);
    }

    /* ADMIN FUNCTIONS */
    public static function filterToWhere(array $filters = null) {
        $where = array();

        if ($filters) {
            foreach($filters as $filterName => $value) {

                if (!$value)
                    continue;

                if (is_array($value) && isset($value['type']) && isset($value['value'])) {
                    $type = $value['type'];
                    $value = $value['value'];
                } else {
                    $type = self::getType($filterName);
                }

                $tableName = '*.';
                if (strpos($filterName, '.') > 0) $tableName = '';
                $filterName = $tableName.$filterName;

                switch( $type ) {
                    case 'datetime':
                    case 'timestamp':
                        // Фильтр по дате
                        if (strpos($filterName, '_1') !== false) {
                            $value = MkDateTime(FmtDate($value,"D.M.Y"),"d.m.Y");
                            $where['>='.substr($filterName, 0, -2) ] = date('Y-m-d H:i:s', $value);
                        }
                        if (strpos($filterName, '_2') !== false) {
                            $value = MkDateTime(FmtDate($value,"D.M.Y")." 23:59","d.m.Y H:i");
                            $where['<='.substr($filterName, 0, -2) ] = date('Y-m-d H:i:s', $value);
                        }
                    break;
                    case 'int':
                    case 'int18':
                    case 'file':
                    case 'image':
                    case 'pk':
                        $where['='.$filterName] = CQuery::int($value);
                    break;
                    case 'string':
                    case 'text':
                        $where['%'.$filterName] = CQuery::escape($value);
                    break;
                    case 'char':
                        $where['='.$filterName] = CQuery::escape($value);
                    break;
                    default:
                        return false;
                }
            }
        }
        return $where;
    }

    /**
      * Вытаскивает данные для админки из таблиц согласно фильтру в админке
      * @param array $select
      * @param array $filters
      * @param boolean $related
      * @param boolean $nav
      * @param array $other_methods
      * @todo перепиать на метод q()
      * @return CQuery
      */
    public static function StaticGetList($select = array(), $filters = null, $related = true, $nav = true, $other_methods = false) {
        $model = self::m();
        $where = static::filterToWhere($filters);
        if ($where === false) {
            throw new Exception("Тип фильтра не определен");
        }

        $methods = array();
        $methods['where'] = $where;
        $methods['select'] = $select;

        if ($other_methods) {
            $methods = array_merge($methods, $other_methods);
        }

        return $related ? $model->queryRelated($methods->ToArray(), $nav) : $model->query($methods->ToArray(), $nav);
    }

    /**
      * Обновляет запись из формы в админке, загружая файлы и картинки
      * @return int возвращает количество затронутых записей, то есть 0 или 1
      */
    public static function UpdateForm( $id = false ) {
        if ($id === false) {
            if (isset($_GET['ID'])) {
                $id = intval($_GET['ID']);
            }
            if (isset($_POST['ID'])) {
                $id = intval($_POST['ID']);
            }
        }
        $data = array();
        foreach(self::getFieldKeys() as $key) {
            $type = self::getType($key);
            if ($type == 'file' && isset($_FILES[ $key ]) && $_FILES[ $key ]["tmp_name"] <> "") {
                $size = self::getSize($key);
                //$file_id = CFileHelper::upload($_FILES[ $key ], '', $size);
                if (!$file_id) return false;
                $data[ $key ] = $file_id;
            }
            if ($type == 'image' && isset($_FILES[ $key ]) && $_FILES[ $key ]["tmp_name"] <> "") {
                $size = self::getSize($key);
                //$file_id = CFileHelper::uploadImage($_FILES[ $key ], $size);
                if (!$file_id) return false;
                $data[ $key ] = $file_id;
            }
            if (isset($_GET[ $key ]) && $key <> "ID") {
                $data[ $key ] = $_GET[ $key ];
            }
            if (isset($_POST[ $key ]) && $key <> "ID") {
                $data[ $key ] = $_POST[ $key ];
            }
        }
        return self::update( $data, $id );
    }
    /**
      * Добавляет запись из формы в админке, загружая файлы и картинки
      * @return int id втавленной записи
      */
    public static function InsertForm() {
        $data = array();
        foreach(self::getFieldKeys() as $key) {
            $type = self::getType($key);
            if ($type == 'file' && isset($_FILES[ $key ]) && $_FILES[ $key ]["tmp_name"] <> "") {
                $size = self::getSize($key);
                //$file_id = CFileHelper::upload($_FILES[ $key ], '', $size);
                if (!$file_id) return false;
                $data[ $key ] = $file_id;
            }
            if ($type == 'image') {
                $size = self::getSize($key);
                //$file_id = CFileHelper::uploadImage($_FILES[ $key ], $size);
                if (!$file_id) return false;
                $data[ $key ] = $file_id;
            }
            if (isset($_POST[ $key ]) && $key <> "ID") {
                $data[ $key ] = $_POST[ $key ];
            }
            if (isset($_GET[ $key ]) && $key <> "ID") {
                $data[ $key ] = $_GET[ $key ];
            }
        }
        return self::insert( $data );
    }
}