<?php

/**
* CAdminModel
* класс для работы с моделями в админке
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @abstract
* @todo refactoring....
*/
namespace piha\modules\orm\classes;
use piha\CException;

class CAdminModel {

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
                    throw new CException ($modelName . ' model does not exists');
                }
                $keys = $model->StaticGetFieldKeys();
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
                    $type = self::getType(str_replace(array('_1', '_2', '', $filterName)));
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
            throw new CException("Тип фильтра не определен");
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
    public static function UpdateForm(CModel $model, $id = false ) {
        if ($id === false) {
            if (isset($_GET['ID'])) {
                $id = intval($_GET['ID']);
            }
            if (isset($_POST['ID'])) {
                $id = intval($_POST['ID']);
            }
        }
        $data = array();
        foreach($model->StaticGetFieldKeys() as $key) {
            $type = $model->getType($key);
            if ($type == 'file' && isset($_FILES[ $key ]) && $_FILES[ $key ]["tmp_name"] <> "") {
                //$size = self::getSize($key);
                //$file_id = CFileHelper::upload($_FILES[ $key ], '', $size);
                if (!$file_id) return false;
                $data[ $key ] = $file_id;
            }
            if ($type == 'image' && isset($_FILES[ $key ]) && $_FILES[ $key ]["tmp_name"] <> "") {
                //$size = self::getSize($key);
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
        return $model->update( $data, $id );
    }
    /**
      * Добавляет запись из формы в админке, загружая файлы и картинки
      * @return int id втавленной записи
      */
    public static function InsertForm(CModel $model) {
        $data = array();
        foreach($model->StaticGetFieldKeys() as $key) {
            $type = $model->getType($key);
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
        return $model->insert( $data );
    }
}