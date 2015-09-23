#!/usr/bin/env php
<?php

echo "\n *** MIGRATION DATABASE TOOL **** \n\n";

define("PIHA_CONSOLE", true);

require_once('../index.php');

CMigrationModel::shema()->createTable();

if (!isset($argv[1])) {
    echo "please use params: 'create', 'up', 'undeploy', 'down'\n\n";
    exit();
}

switch ($argv[1]) {
    case 'create':
        echo "CREATE DATABASE MIGRATION......\n\n";
        $name = false;
        if (isset($argv[2])) {
            $name = preg_replace('/[^A-Za-z_0-9]/', '', $argv[2]);
        }
        if ($name) {
            $time = time();
            $date = date('d.m.Y H:i:s');

    $class_file_name = $time . '__' . $name;
    $new_migration = '<?php

    /*
     * Date: '.$date.'
     * Please, write your migartion code to up() method
     * You can use mysql_query() and throw new Exception(mysql_error()).
    */

    class '.$class_file_name.' {

        public static function up() {

        }

        public static function down() {

        }
    }
    ';
            $f = fopen('migrations/' . $class_file_name .'.php', 'w+');
            fputs($f, $new_migration);
            fclose($f);
            echo "MIGRATION CREATE SUCCESS migrations/$class_file_name.php\n\n";
        } else {
            echo "please, write migration name (A-z, _, 0-9)\n\n";
            exit();
        }
    break;

    case 'undeploy':
        if (isset($argv[2])) {
            $name = preg_replace('/[^A-Za-z_0-9]/', '', $argv[2]);
            CMigrationModel::Delete(array('NAME' => $name));
        } else {
            echo "please, write migration name (A-z, _, 0-9)\n\n";
            exit();
        }
    break;

    case 'deploy':
        if (isset($argv[2])) {
            $name = preg_replace('/[^A-Za-z_\d]/', '', $argv[2]);
            $t = preg_replace('/[^0-9]/', '', $name);
            mysql_query("INSERT INTO `deploy_migrations` (`MIGRATION`, `NAME`) VALUES ($t, '$name')") or die(mysql_error());
        } else {
            echo "please, write migration name (A-z, _)\n\n";
            exit();
        }
    break;

    case 'down':
        if (isset($argv[2])) {
            $name = preg_replace('/[^A-Za-z_]/', '', $argv[2]);
            $res = mysql_query("SELECT NAME FROM `deploy_migrations` WHERE NAME LIKE '".$name."%'");
            if ($f = mysql_fetch_array($res)) {
                $file = $f['NAME'] . '.php';
                mysql_query("START TRANSACTION");
                echo "$file...";
                require('migrations/'.$file);
                $className = str_replace('.php', '', $file);
                try {
                    $className::down();
                    $result = mysql_query("DELETE FROM `deploy_migrations` WHERE NAME = '$className'");
                    if (!$result) {
                        throw new Exception(mysql_error());
                    }
                    mysql_query("COMMIT");
                } catch ( Exception $e ) {
                    mysql_query("ROLLBACK");
                    mysql_query("SET AUTOCOMMIT=1");
                    echo "!!! ERROR DOWNGRATE: $className\n\n";
                    exit();
                }
                echo "ok\n";
            } else {
                echo "Not found ".$name."\n\n";
            }
        } else {
            echo "please, write migration name (A-z, _)\n\n";
            exit();
        }
    break;

    case 'up':
        echo "START DATABASE MIGRATION......\n\n";

        $skipMigrations = CMigrationModel::GetAll('TIMESTAMP');

        $migrations = Array();
        $dir = opendir('migrations');
        while ($file = readdir($dir)) {
            if ( $file != "." && $file != ".." && !is_dir( $dir . $file ) && strpos($file,'.php') > 0) {
                $time = intval($file);
                if (!in_array($time, $skipMigrations)) {
                    echo "Find new migration $file...\n";
                    $migrations[(int) $time] = $file;
                }
            }
        }

        if (count($migrations) == 0) {
            echo "Nothing to migrate.\n\n";
            exit();
        }

        echo "\nMIGRATE....\n\n";
        mysql_query("SET AUTOCOMMIT=0");
        if (ksort($migrations)) {
            foreach($migrations as $t => $file) {
                mysql_query("START TRANSACTION");
                echo "$file...";
                require('migrations/'.$file);
                $className = str_replace('.php', '', $file);
                try {
                    $className::up();
                    $result = CMigrationModel::Insert(array('NAME' => $className, 'TIMESTAMP' => $time));
                    if (!$result) {
                        throw new Exception(mysql_error());
                    }
                    mysql_query("COMMIT");
                } catch ( Exception $e ) {
                    mysql_query("ROLLBACK");
                    mysql_query("SET AUTOCOMMIT=1");
                    echo $e->getMessage();
                    echo "!!! ERROR MIGRATE: $className\n\n";
                    exit();
                }
                echo "ok\n";
            }
        }
        mysql_query("SET AUTOCOMMIT=1");

        echo " SUCCESS......\n\n";

    break;
    default:
        echo "please use params: 'create', 'up', 'undeploy', 'down'\n\n";
}
