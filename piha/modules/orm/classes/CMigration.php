<?php

namespace piha\modules\orm\classes;
use piha\modules\orm\COrmModule;
use piha\CException;

class CMigration {

    public $columnTypes=array(
        'pk' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
        'string' => 'varchar(255)',
        'text' => 'text',
        'int18' => 'int(18)',
        'int' => 'int(11)',
        'file' => 'int(11)',
        'image' => 'int(11)',
        'integer' => 'int(11)',
        'float' => 'float',
        'decimal' => 'decimal',
        'datetime' => 'datetime',
        'timestamp' => 'timestamp',
        'time' => 'time',
        'date' => 'date',
        'binary' => 'blob',
        'boolean' => 'tinyint(1)',
        'money' => 'decimal(19,4)',
        'char' => 'char(1)'
    );

    private $_table = '';
    private $_columns = '';
    private $_q = '';
    public function __construct($table, $columns) {
        $this->_table = $table;
        $this->_columns = $columns;
    }

    public function execute() {
        return CQuery::create()->setQuery($this->_q)->execute();
    }

    /**
      * Удаление всех индексов из таблицы
      *
      * @return null
      */
    public function dropIndexTable() {
        $table = $this->_table;
        $res = CQuery::create()->setQuery("SHOW CREATE TABLE $table")->execute();
        while ($row = $res->Fetch()) {
            // remove constrains
            if(preg_match_all('/CONSTRAINT `(.*)` FOREIGN KEY/', next($row), $matchArr)) {
                foreach($matchArr[1] as $key) {
                    try { $this->dropForeignKey($key); } catch(\Exception $e){}
                }
            }
            // remove keys
            if(preg_match_all('/KEY `(.*)` \(/', $row[1], $matchArr)) {
                foreach($matchArr[1] as $key) {
                    try { $this->dropIndex($key); } catch(\Exception $e){}
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
        $prefix = COrmModule::GetInstance()->config('prefix', '');
        foreach($this->_columns as $key => $column) {
            if (is_array($column)) {
                if (isset($column['object']) && $model = $column['object']) {
                    if (!class_exists($model)) {
                        throw new CException("Class {$model} not found");
                    }
                    if (!in_array(__NAMESPACE__ . '\CModel', class_parents($model))) {
                        throw new CException("Class {$model} is not instance of CModel");
                    }
                    $name = $model::tableName();
                    $delete = isset($column['delete']) ? $column['delete']: null;
                    $update = isset($column['update']) ? $column['update']: null;
                    $keyName = 'fk_' . strtolower($key) . '__' . str_replace($prefix, '', trim($this->_table, '{}')) . '__' . str_replace($prefix, '', trim($name, '{}'));
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

    public static function quoteTableName($name)
    {
        return COrmModule::quoteTableName($name);
    }

    public function quoteColumnName($name)
    {
        return '`'.$name.'`';
    }

    public function getColumnType($type)
    {
        if(isset($this->columnTypes[$type]))
            return $this->columnTypes[$type];
        elseif(($pos=strpos($type,' '))!==false)
        {
            $t=substr($type,0,$pos);
            return (isset($this->columnTypes[$t]) ? $this->columnTypes[$t] : $t).substr($type,$pos);
        }
        else
            return $type;
    }

    public function renameTable($newName)
    {
        $table = $this->_table;
        $this->_q = 'RENAME TABLE ' . $this->quoteTableName($table) . ' TO ' . $this->quoteTableName($newName);
        $this->_table = $newName;
        return $this->execute();
    }

    public function dropTable()
    {
        $table = $this->_table;
        $this->_q = "DROP TABLE IF EXISTS ".$this->quoteTableName($table);
        return $this->execute();
    }

    public function truncateTable()
    {
        $table = $this->_table;
        $this->_q = "TRUNCATE TABLE ".$this->quoteTableName($table);
        return $this->execute();
    }

    /**
      * Создание таблицы на основании описания столбцов
      *
      * @param boolean $index Нужно ли индексы после создания таблицы
      * @return null
      */
    public function createTable($index = false, $options = null) {
        $types = array();
        foreach($this->_columns as $key => $column) {
            if (is_array($column)) {
                $types[$key] = $column['type'] . (isset($column['default']) ? " DEFAULT '".$column['default']."'": '');
            } else {
                $types[$key] = $column;
            }
        }
        $table = $this->_table;
        $cols=array();
        foreach($types as $name=>$type)
        {
            if(is_string($name))
                $cols[]="\t".$this->quoteColumnName($name).' '.$this->getColumnType($type);
            else
                $cols[]="\t".$type;
        }
        $sql="CREATE TABLE IF NOT EXISTS ".$this->quoteTableName($table)." (\n".implode(",\n",$cols)."\n)";
        $this->_q = $options===null ? $sql . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci': $sql.' '.$options;
        $this->execute();

        if ($index) {
            $this->createIndexTable(false);
        }
    }

    public function addColumn($column, $type=null)
    {
        $col = $this->_columns[$column];
        $type = $type ?: $col['type'] . (isset($col['default']) ? " DEFAULT '".$col['default']."'": '');
        $table = $this->_table;
        $this->_q = 'ALTER TABLE ' . $this->quoteTableName($table)
            . ' ADD ' . $this->quoteColumnName($column) . ' '
            . $this->getColumnType($type);
        return $this->execute();
    }

    public function dropColumn($column)
    {
        $table = $this->_table;
        $this->_q = "ALTER TABLE ".$this->quoteTableName($table)
            ." DROP COLUMN ".$this->quoteColumnName($column);
        return $this->execute();
    }

    public function renameColumn($name, $newName)
    {
        $table = $this->_table;
        $this->_q = "ALTER TABLE ".$this->quoteTableName($table)
            . " RENAME COLUMN ".$this->quoteColumnName($name)
            . " TO ".$this->quoteColumnName($newName);
        return $this->execute();
    }

    public function alterColumn($column, $type)
    {
        $table = $this->_table;
        $this->_q = 'ALTER TABLE ' . $this->quoteTableName($table) . ' CHANGE '
            . $this->quoteColumnName($column) . ' '
            . $this->quoteColumnName($column) . ' '
            . $this->getColumnType($type);
        return $this->execute();
    }

    public function addForeignKey($name, $columns, $refTable, $refColumns, $delete=null, $update=null)
    {
        $table = $this->_table;
        $columns=preg_split('/\s*,\s*/',$columns,-1,PREG_SPLIT_NO_EMPTY);
        foreach($columns as $i=>$col)
            $columns[$i]=$this->quoteColumnName($col);
        $refColumns=preg_split('/\s*,\s*/',$refColumns,-1,PREG_SPLIT_NO_EMPTY);
        foreach($refColumns as $i=>$col)
            $refColumns[$i]=$this->quoteColumnName($col);
        $sql='ALTER TABLE '.$this->quoteTableName($table)
            .' ADD CONSTRAINT '.$this->quoteColumnName($name)
            .' FOREIGN KEY ('.implode(', ', $columns).')'
            .' REFERENCES '.$this->quoteTableName($refTable)
            .' ('.implode(', ', $refColumns).')';
        if($delete!==null)
            $sql.=' ON DELETE '.$delete;
        if($update!==null)
            $sql.=' ON UPDATE '.$update;

        $this->_q = $sql;
        return $this->execute();
    }

    public function dropForeignKey($name)
    {
        $table = $this->_table;
        $this->_q = 'ALTER TABLE '.$this->quoteTableName($table)
            .' DROP FOREIGN KEY '.$this->quoteColumnName($name);
        return $this->execute();
    }

    public function createIndex($name, $column, $unique=false)
    {
        $table = $this->_table;
        $cols=array();
        $columns=preg_split('/\s*,\s*/',$column,-1,PREG_SPLIT_NO_EMPTY);
        foreach($columns as $col)
        {
            if(strpos($col,'(')!==false)
                $cols[]=$col;
            else
                $cols[]=$this->quoteColumnName($col);
        }
        $this->_q = ($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ')
            . $this->quoteTableName($name).' ON '
            . $this->quoteTableName($table).' ('.implode(', ',$cols).')';
        return $this->execute();
    }

    public function dropIndex($name)
    {
        $table = $this->_table;
        $this->_q = 'DROP INDEX '.$this->quoteTableName($name).' ON '.$this->quoteTableName($table);
        return $this->execute();
    }

    public function addPrimaryKey($name, $columns)
    {
        $table = $this->_table;
        if(is_string($columns))
            $columns=preg_split('/\s*,\s*/',$columns,-1,PREG_SPLIT_NO_EMPTY);
        foreach($columns as $i=>$col)
            $columns[$i]=$this->quoteColumnName($col);
        $this->_q = 'ALTER TABLE ' . $this->quoteTableName($table) . ' ADD CONSTRAINT '
            . $this->quoteColumnName($name) . '  PRIMARY KEY ('
            . implode(', ', $columns). ' )';
        return $this->execute();
    }

    public function dropPrimaryKey($name)
    {
        $table = $this->_table;
        $this->_q = 'ALTER TABLE ' . $this->quoteTableName($table) . ' DROP CONSTRAINT '
            . $this->quoteColumnName($name);
        return $this->execute();
    }

}