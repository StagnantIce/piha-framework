<?php

/**
* CBitrixConnection
* адаптер для работы с базой данных через битрикс
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
*/
namespace piha\modules\orm\classes;

/* Adapter for DB and CDBResult */
class CBitrixConnection {

    /** @var $_res CDBResult */
    public $_res = null;

    public function query($query) {
        global $DB;
        CQuery::$last = $query;
        if (!is_object($DB)) throw new \Exception("No bitrix connect ". serialize($DB));
        $this->_res = $DB->Query($query);
        return $this->_res;
    }

    /**
     * @return CDBResult
     */
    public function getResult() {
        return $this->_res;
    }

    public function fetch() {
        return $this->_res->Fetch();
    }

    public function ExtractFields() {
        return $this->_res->ExtractFields();
    }

    public function getNext() {
        return $this->_res->getNext();
    }

    /**
     * Построение HTML строки пагинации
     *
     * @param CBitrixComponent $component
     * @param $navigationTitle
     * @param string $templateName
     * @param bool $showAlways
     * @return string
     */
    public function GetPageNavStringEx($component = null, $navigationTitle = '', $templateName = '', $showAlways = false) {
        return $this->_res->GetPageNavStringEx($component, $navigationTitle, $templateName, $showAlways);
    }


    public function queryNav($query, $size = 20, $numPage = false) {
        $query = str_replace(array("\n", "\t", "SQL_CALC_FOUND_ROWS"), "", $query);
        $countRes = $this->query('SELECT COUNT(*) as COUNT FROM (' . $query. ') as asdf');
        $countArr = $this->_res->fetch();
        $count = $countArr['COUNT'];

        // set to debug mode
        global $DB;
        $DB->ShowSqlStat = true;

        // construct CDBResult
        $dbRes = new CDBResult();
        $nPageSize = array("nPageSize" => $size, "iNumPage" => $numPage);

        if ($count === false) throw new Exception("Not valid count query: ".  $countQuery);

        // init navigation session
        //$dbRes->NavStart();
        $dbRes->NavQuery($query, $count, $nPageSize);

        CQuery::$last = $DB->arQueryDebug[ count($DB->arQueryDebug) - 1 ]['QUERY'];
        $DB->ShowSqlStat = false;

        $this->_res = $dbRes;
        return $this->_res;
    }

    /**
     * Экранировать строку
     *
     * @param $string
     * @return array|string
     * @throws Exception
     */
    public static function escape($string) {
        if (is_array($string)) {
            $ss = array();
            foreach($string as $s) {
                $ss[] = self::escape($s);
            }
            return $ss;
        }
        global $DB;
        if (!is_object($DB)) throw new Exception("No bitrix connect");
        return $DB->ForSql($string);
    }

    /**
     * Открыть транзакцию
     */
    public static function transaction() {
        global $DB;
        $DB->StartTransaction();
    }

    /**
     * Применить изменения
     */
    public static function commit() {
        global $DB;
        $DB->Commit();
    }

    /**
     * Откатить изменения
     */
    public static function rollback() {
        global $DB;
        $DB->Rollback();
    }

    /**
     * Получить столбцы таблицы
     *
     * @param $table
     * @return mixed
     */
    public static function tableFields($table)
    {
        global $DB;
        if(!array_key_exists($table, $DB->column_cache))
        {
            $DB->column_cache[$table] = array();
            $DB->DoConnect();
            $rs = @mysql_list_fields($DB->DBName, $table, $DB->db_Conn);
            if($rs > 0)
            {
                $intNumFields = mysql_num_fields($rs);
                while(--$intNumFields >= 0)
                {
                    $ar = array(
                        "NAME" => mysql_field_name($rs, $intNumFields),
                        "TYPE" => mysql_field_type($rs, $intNumFields),
                    );
                    $DB->column_cache[$table][$ar["NAME"]] = $ar["TYPE"];
                }
            } else {
                throw new CCoreException("Table $table not exists, or db connect failed");
            }
        }
        return $DB->column_cache[$table];
    }


    /**
     * Добавить запись в таблицу
     *
     * @param $table
     * @param $prepareFields
     * @return bool|int|string
     */
    public static function tableInsert($table, $prepareFields) {
        global $DB;
        if (ATestUnit::$instance) {
            ATestUnit::$instance->stopTimer();
        }
        $ins = $DB->Insert($table, $prepareFields);
        if (ATestUnit::$instance) {
            ATestUnit::$instance->startTimer();
        }
        CQuery::$last = 'INSERT INTO '.$table.'(`'. implode('`, `', array_keys($prepareFields)). '`) VALUES ('. implode(', ', array_values($prepareFields)) .')';
        return $ins;
    }

    /**
     * Получить количество записей, измененных SQL-командами
     *
     * @return int
     */
    public function affectedRows() {
        global $DB;
        return $this->getResult()->AffectedRowsCount();
    }

    /**
     * Обновление таблицы
     *
     * @param $table
     * @param $prepareFields
     * @param string $where
     * @return bool|int
     */
    public static function tableUpdate($table, $prepareFields, $where = "") {
        global $DB;
        if (ATestUnit::$instance) {
            ATestUnit::$instance->stopTimer();
        }
        $up = $DB->Update($table, $prepareFields, $where);
        if (ATestUnit::$instance) {
            ATestUnit::$instance->startTimer();
        }
        $q = 'UPDATE '.$table .' SET ';
        foreach($prepareFields as $k => &$v) $v = '`'.$k.'` = '.$v;
        $q .= implode(', ', $prepareFields);
        $q .= $where;
        CQuery::$last = $q;
        return $up;
    }

    /**
     * @param $key
     * @return mixed
     * Переобращение к свойствам CDBResult
     */
    public function __get($key) {
        return $this->_res->$key;
    }

    /**
     * @param $name
     * @param $arguments
     * Перехват вызовов CDBResult
     *
     * @deprecated - Не будет срабатывать если вызов идет через CExtendClass
     */
    public function __call($name, $arguments) {
        if (method_exists('CDBResult', $name)) {
            if (!$this->_res) {
                $this->_res = new CDBResult();
            }
            return call_user_func_array(array(&$this->_res, $name), $arguments);
        } else {
            throw new CCoreException(sprintf('The required method "%s" does not exist for %s', $name, get_class($this)));
        }
    }


    /**
     * Установка или инициализация _res
     *
     * @param null $result
     * @return $this
     */
    private function setResult($result = null){
        if (!is_null($result)){
            if ($result instanceof CDBResult) {
                $this->_res = $result;
            }else{
                throw new CCoreException(sprintf('$result should be instanceof CDBResult, not %s', get_class($result)));
            }
        }else{
            $this->_res = new CDBResult();
        }
        return $this;
    }

}