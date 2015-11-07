#!/usr/bin/env php
<?php

echo "\n *** MIGRATION DATABASE TOOL **** \n\n";

define("PIHA_CONSOLE", true);

require_once(__DIR__ . '/../../index.php');

CMigrationModel::schema()->createTable();

if (!isset($argv[1])) {
    echo "please use params: 'create', 'up', 'undeploy', 'down'\n\n";
    exit();
}

$defaultPath = PIHA_CORE_PATH . DS . 'deploy' . DS . 'migrations';
$migrationPaths = COrmModule::GetInstance()->config('migrationPaths', array($defaultPath));

if (count($migrationPaths) > 1) {
    $alias = '';
    foreach($argv as $arg) {
        if (strpos($arg, '--alias=') !== false) {
            $alias = str_replace('--alias=', '', $arg);
            break;
        }
    }
    if (isset($migrationPaths[$alias])) {
        $migrationPath = $migrationPaths[$alias];
    } else {
        throw new Exception("Alias for migration path not found: $alias");
    }
} else {
    $migrationPath = reset($migrationPaths);
}

echo "MIGRATION PATH: {$migrationPath}\n\n";

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

    $class_file_name = 'm' . $time . '__' . $name;
    $new_migration = '<?php

    /*
     * Date: '.$date.'
     * Please, write your migartion code to up() method
     * You can use CQuery and throw new Exception().
    */

    class '.$class_file_name.' {

        public static function up() {

        }

        public static function down() {

        }
    }
    ';
            $f = fopen($migrationPath . DS . $class_file_name .'.php', 'w+');
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
            CMigrationModel::Insert(array('MIGRATION' => $t, 'NAME' => $name));
        } else {
            echo "please, write migration name (A-z, _)\n\n";
            exit();
        }
    break;

    case 'down':
        if (isset($argv[2])) {
            $name = preg_replace('/[^A-Za-z_]/', '', $argv[2]);
            $mname = CMigrationModel::GetAll(array('%NAME' => $name), 'NAME');
            if (count($mname) == 1) {
                $file = $mname[0] . '.php';
                CQuery::transaction();
                echo "$file...";
                require('migrations/'.$file);
                $className = str_replace('.php', '', $file);
                try {
                    $className::down();
                    CMigrationModel::Delete(array('NAME' => $className));
                    CQuery::commit();
                } catch ( Exception $e ) {
                    CQuery::rollback();
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

        $skipMigrations = CMigrationModel::GetAll(array(), 'TIMESTAMP');

        $migrations = Array();
        $dir = opendir($migrationPath);
        while ($file = readdir($dir)) {
            if ( $file != "." && $file != ".." && !is_dir( $dir . $file ) && strpos($file,'.php') > 0) {
                $time = intval(mb_substr($file,1));
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
        if (ksort($migrations)) {
            foreach($migrations as $t => $file) {
                CQuery::transaction();
                echo "$file...";
                require($migrationPath . DS . $file);
                $className = str_replace('.php', '', $file);
                try {
                    $className::up();
                    CMigrationModel::Insert(array('NAME' => $className, 'TIMESTAMP' => $t));
                    CQuery::commit();
                } catch ( Exception $e ) {
                    CQuery::rollback();
                    echo $e->getMessage();
                    echo "!!! ERROR MIGRATE: $className\n\n";
                    exit();
                }
                echo "ok\n";
            }
        }

        echo " SUCCESS......\n\n";

    break;
    default:
        echo "please use params: 'create', 'up', 'undeploy', 'down'\n\n";
}