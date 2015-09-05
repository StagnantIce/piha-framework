<?php

/**
* CMysqlConnection
* адаптер для работы с базой данных
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
*/

class CMysqlConnection {

    public static $conn; //mysqli
    private $_res = null;
    public static $numPages = 0;
    public static $last = '';

    public function query($query) {
        self::$last = $query;
        if (self::$conn->connect_errno) {
            printf("Соединение не удалось: %s\n", self::$conn->connect_error);
            exit();
        }
        $this->_res = self::$conn->query($query);
        if (!$this->_res) {
            throw new Exception($query . ' error' . self::$conn->error);
        }
        return $this->_res;
    }

    public function getResult() {
        return $this->_res;
    }

    public function fetch() {
        if ($row = $this->_res->fetch_assoc()) {
            return $row;
        }
        $this->_res->free();
        return false;
    }

    public function queryNav($query, $size = 20, $numPage = false) {
        $query = str_replace(array("\n", "\t"), "", $query);
        $query .= ' LIMIT '.($numPage * $size).', '.$size;
        self::$last = $query;
        $this->_res = $this->query($query);
        self::$numPages = current($this->query('SELECT FOUND_ROWS()')->fetch_assoc());
        return $this->_res;
    }

    public static function escape($string) {
        if (is_array($string)) {
            $ss = array();
            foreach($string as $s) {
                $ss[] = self::escape($s);
            }
            return $ss;
        }
        return self::$conn->escape_string($string);
    }

    public static function transaction() {
        self::$conn->query("START TRANSACTION");
    }

    public static function commit() {
        self::$conn->query("COMMIT");
    }

    public static function rollback() {
        self::$conn->rollback();
    }

    public static $column_cache = array();
    public static function tableFields($table)
    {
        if(!array_key_exists($table, self::$column_cache))
        {
            self::$column_cache[$table] = array();
            $res = self::$conn->query("SHOW COLUMNS FROM $table");
            while($row = $res->fetch_assoc())
            {
                self::$column_cache[$table][$row["Field"]] = preg_replace('/\(\d+\)/', '', $row['Type']);
            }
        }
        return self::$column_cache[$table];
    }


    public static function tableInsert($table, $prepareFields) {
        $q = 'INSERT INTO `'.$table.'` ('. implode(', ', array_keys($prepareFields)). ') VALUES ('. implode(', ', array_values($prepareFields)) .')';
        self::$last = $q;
        self::$conn->query($q);
        return self::$conn->insert_id;
    }

    public static function affectedRows() {
        return self::$conn->affected_rows;
    }

    public static function tableUpdate($table, $prepareFields, $where = "") {
        $q = 'UPDATE `'.$table .'` SET ';
        foreach($prepareFields as $k => &$v) $v = $k.' = '.$v;
        $q .= implode(',', $prepareFields);
        $q .= $where;
        self::$last = $q;
        self::$conn->query($q);
        return self::affectedRows();
    }
}