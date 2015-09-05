<?php

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package main\yii
 * @since 1.0
 */

class CMigration {
        /**
     * @var array the abstract column types mapped to physical column types.
     * @since 1.1.6
     */
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

    /**
     * Quotes a table name for use in a query.
     * A simple table name does not schema prefix.
     * @param string $name table name
     * @return string the properly quoted table name
     * @since 1.1.6
     */
    public function quoteTableName($name)
    {
        return '`'.$name.'`';
    }

    /**
     * Quotes a column name for use in a query.
     * A simple column name does not contain prefix.
     * @param string $name column name
     * @return string the properly quoted column name
     * @since 1.1.6
     */
    public function quoteColumnName($name)
    {
        return '`'.$name.'`';
    }

    /**
     * Converts an abstract column type into a physical column type.
     * The conversion is done using the type map specified in {@link columnTypes}.
     * These abstract column types are supported (using MySQL as example to explain the corresponding
     * physical types):
     * <ul>
     * <li>pk: an auto-incremental primary key type, will be converted into "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY"</li>
     * <li>string: string type, will be converted into "varchar(255)"</li>
     * <li>text: a long string type, will be converted into "text"</li>
     * <li>integer: integer type, will be converted into "int(11)"</li>
     * <li>boolean: boolean type, will be converted into "tinyint(1)"</li>
     * <li>float: float number type, will be converted into "float"</li>
     * <li>decimal: decimal number type, will be converted into "decimal"</li>
     * <li>datetime: datetime type, will be converted into "datetime"</li>
     * <li>timestamp: timestamp type, will be converted into "timestamp"</li>
     * <li>time: time type, will be converted into "time"</li>
     * <li>date: date type, will be converted into "date"</li>
     * <li>binary: binary data type, will be converted into "blob"</li>
     * </ul>
     *
     * If the abstract type contains two or more parts separated by spaces (e.g. "string NOT NULL"), then only
     * the first part will be converted, and the rest of the parts will be appended to the conversion result.
     * For example, 'string NOT NULL' is converted to 'varchar(255) NOT NULL'.
     * @param string $type abstract column type
     * @return string physical column type.
     * @since 1.1.6
     */
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
    /**
     * Builds a SQL statement for creating a new DB table.
     *
     * The columns in the new  table should be specified as name-definition pairs (e.g. 'name'=>'string'),
     * where name stands for a column name which will be properly quoted by the method, and definition
     * stands for the column type which can contain an abstract DB type.
     * The {@link getColumnType} method will be invoked to convert any abstract type into a physical one.
     *
     * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
     * inserted into the generated SQL.
     *
     * @param string $table the name of the table to be created. The name will be properly quoted by the method.
     * @param array $columns the columns (name=>definition) in the new table.
     * @param string $options additional SQL fragment that will be appended to the generated SQL.
     * @return string the SQL statement for creating a new DB table.
     * @since 1.1.6
     */
    public function createTable2($table, $columns, $options=null)
    {
        $cols=array();
        foreach($columns as $name=>$type)
        {
            if(is_string($name))
                $cols[]="\t".$this->quoteColumnName($name).' '.$this->getColumnType($type);
            else
                $cols[]="\t".$type;
        }
        $sql="CREATE TABLE IF NOT EXISTS".$this->quoteTableName($table)." (\n".implode(",\n",$cols)."\n)";
        return $options===null ? $sql . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci': $sql.' '.$options;
    }

    /**
     * Builds a SQL statement for renaming a DB table.
     * @param string $table the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB table.
     * @since 1.1.6
     */
    public function renameTable($table, $newName)
    {
        return 'RENAME TABLE ' . $this->quoteTableName($table) . ' TO ' . $this->quoteTableName($newName);
    }

    /**
     * Builds a SQL statement for dropping a DB table.
     * @param string $table the table to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a DB table.
     * @since 1.1.6
     */
    public function dropTable($table)
    {
        return "DROP TABLE IF EXISTS ".$this->quoteTableName($table);
    }

    /**
     * Builds a SQL statement for truncating a DB table.
     * @param string $table the table to be truncated. The name will be properly quoted by the method.
     * @return string the SQL statement for truncating a DB table.
     * @since 1.1.6
     */
    public function truncateTable($table)
    {
        return "TRUNCATE TABLE ".$this->quoteTableName($table);
    }

    /**
     * Builds a SQL statement for adding a new DB column.
     * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
     * @param string $column the name of the new column. The name will be properly quoted by the method.
     * @param string $type the column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for adding a new column.
     * @since 1.1.6
     */
    public function addColumn($table, $column, $type)
    {
        return 'ALTER TABLE ' . $this->quoteTableName($table)
            . ' ADD ' . $this->quoteColumnName($column) . ' '
            . $this->getColumnType($type);
    }

    /**
     * Builds a SQL statement for dropping a DB column.
     * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
     * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a DB column.
     * @since 1.1.6
     */
    public function dropColumn($table, $column)
    {
        return "ALTER TABLE ".$this->quoteTableName($table)
            ." DROP COLUMN ".$this->quoteColumnName($column);
    }

    /**
     * Builds a SQL statement for renaming a column.
     * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
     * @param string $name the old name of the column. The name will be properly quoted by the method.
     * @param string $newName the new name of the column. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB column.
     * @since 1.1.6
     */
    public function renameColumn($table, $name, $newName)
    {
        return "ALTER TABLE ".$this->quoteTableName($table)
            . " RENAME COLUMN ".$this->quoteColumnName($name)
            . " TO ".$this->quoteColumnName($newName);
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for changing the definition of a column.
     * @since 1.1.6
     */
    public function alterColumn($table, $column, $type)
    {
        return 'ALTER TABLE ' . $this->quoteTableName($table) . ' CHANGE '
            . $this->quoteColumnName($column) . ' '
            . $this->quoteColumnName($column) . ' '
            . $this->getColumnType($type);
    }

    /**
     * Builds a SQL statement for adding a foreign key constraint to an existing table.
     * The method will properly quote the table and column names.
     * @param string $name the name of the foreign key constraint.
     * @param string $table the table that the foreign key constraint will be added to.
     * @param string $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
     * @param string $refTable the table that the foreign key references to.
     * @param string $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
     * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @return string the SQL statement for adding a foreign key constraint to an existing table.
     * @since 1.1.6
     */
    public function addForeignKey($table, $name, $columns, $refTable, $refColumns, $delete=null, $update=null)
    {
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

        return $sql;
    }

    /**
     * Builds a SQL statement for dropping a foreign key constraint.
     * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping a foreign key constraint.
     * @since 1.1.6
     */
    public function dropForeignKey($table, $name)
    {
        return 'ALTER TABLE '.$this->quoteTableName($table)
            .' DROP FOREIGN KEY '.$this->quoteColumnName($name);
    }

    /**
     * Builds a SQL statement for creating a new index.
     * @param string $name the name of the index. The name will be properly quoted by the method.
     * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
     * @param string $column the column(s) that should be included in the index. If there are multiple columns, please separate them
     * by commas. Each column name will be properly quoted by the method, unless a parenthesis is found in the name.
     * @param boolean $unique whether to add UNIQUE constraint on the created index.
     * @return string the SQL statement for creating a new index.
     * @since 1.1.6
     */
    public function createIndex($table, $name, $column, $unique=false)
    {
        $cols=array();
        $columns=preg_split('/\s*,\s*/',$column,-1,PREG_SPLIT_NO_EMPTY);
        foreach($columns as $col)
        {
            if(strpos($col,'(')!==false)
                $cols[]=$col;
            else
                $cols[]=$this->quoteColumnName($col);
        }
        return ($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ')
            . $this->quoteTableName($name).' ON '
            . $this->quoteTableName($table).' ('.implode(', ',$cols).')';
    }

    /**
     * Builds a SQL statement for dropping an index.
     * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping an index.
     * @since 1.1.6
     */
    public function dropIndex($table, $name)
    {
        return 'DROP INDEX '.$this->quoteTableName($name).' ON '.$this->quoteTableName($table);
    }

    /**
     * Builds a SQL statement for adding a primary key constraint to an existing table.
     * @param string $name the name of the primary key constraint.
     * @param string $table the table that the primary key constraint will be added to.
     * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
     * Array value can be passed since 1.1.14.
     * @return string the SQL statement for adding a primary key constraint to an existing table.
     * @since 1.1.13
     */
    public function addPrimaryKey($table, $name, $columns)
    {
        if(is_string($columns))
            $columns=preg_split('/\s*,\s*/',$columns,-1,PREG_SPLIT_NO_EMPTY);
        foreach($columns as $i=>$col)
            $columns[$i]=$this->quoteColumnName($col);
        return 'ALTER TABLE ' . $this->quoteTableName($table) . ' ADD CONSTRAINT '
            . $this->quoteColumnName($name) . '  PRIMARY KEY ('
            . implode(', ', $columns). ' )';
    }

    /**
     * Builds a SQL statement for removing a primary key constraint to an existing table.
     * @param string $name the name of the primary key constraint to be removed.
     * @param string $table the table that the primary key constraint will be removed from.
     * @return string the SQL statement for removing a primary key constraint from an existing table.
     * @since 1.1.13
     */
    public function dropPrimaryKey($table, $name)
    {
        return 'ALTER TABLE ' . $this->quoteTableName($table) . ' DROP CONSTRAINT '
            . $this->quoteColumnName($name);
    }

}