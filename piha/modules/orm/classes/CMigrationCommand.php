<?
namespace piha\modules\orm\classes;
use piha\modules\orm\models\CMigrationModel;
use piha\modules\orm\classes\CQuery;
use piha\modules\orm\COrmModule;
use piha\CAlias;
use piha\modules\core\classes\CCommand;
use piha\CException;

class CMigrationCommand extends CCommand {

    protected function before() {
        CMigrationModel::schema()->createTable();
    }

	protected function help($method = null, $argv = null, $message = null) {
		if (!$method) {
		    echo "\nplease use command: 'create', 'up', 'insert', 'delete', 'down'\n\n";
		    exit();
		}
		echo "\n".$message . "\n\n";
	}

	private function getMigrationPath($alias = 'app') {
		$ds = CAlias::ds();

		$migrationPaths = COrmModule::GetInstance()->config('migrationPaths');

		if (!$migrationPaths) {
		    throw new CException("Orm config migrationPaths not found");
		}

		if ($alias) {
		    if (isset($migrationPaths[$alias])) {
		        $migrationPath = $migrationPaths[$alias];
		    } else {
		        throw new CException("Migration path with alias {$alias} not found. Available aliases: ". implode(', ', array_keys($migrationPaths)));
		    }
		} else {
		    $migrationPath = reset($migrationPaths);
		}
		return CAlias::GetPath($migrationPath);
	}

	public function commandCreate($name, $alias = 'app') {
		$ds = CAlias::ds();
		$migrationPath = $this->getMigrationPath($alias);
		echo "Execute {$name}";
		echo "CREATE DATABASE MIGRATION......\n\n";
        if ($name && $name = preg_replace('/[^A-Za-z_0-9]/', '', $name)) {
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
            $f = fopen($migrationPath . $ds . $class_file_name .'.php', 'w+');
            fputs($f, $new_migration);
            fclose($f);
            echo "MIGRATION CREATE SUCCESS $migrationPath/$class_file_name.php\n\n";
        }
    }

	public function commandDelete($name) {
        if ($name = preg_replace('/[^A-Za-z_0-9]/', '', $name)) {
            CMigrationModel::Delete(array('NAME' => $name));
        } else {
            echo "please, write migration name (A-z, _, 0-9)\n\n";
            exit();
        }
	}

	public function commandInsert($name) {
        if ($name = preg_replace('/[^A-Za-z_\d]/', '', $name)) {
            $t = preg_replace('/[^0-9]/', '', $name);
            CMigrationModel::Insert(array('MIGRATION' => $t, 'NAME' => $name));
        } else {
            echo "please, write migration name (A-z, _)\n\n";
            exit();
        }
	}

	public function commandDown($name, $alias = 'app') {
        $ds = CAlias::ds();
        $migrationPath = $this->getMigrationPath($alias);
        if ($name && $name = preg_replace('/[^A-Za-z_]/', '', $name)) {
            $mname = CMigrationModel::StaticGetAll(array('%NAME' => $name), 'NAME');
            if (count($mname) == 1) {
                $file = $mname[0] . '.php';
                CQuery::transaction();
                echo "$file...";
                require($migrationPath . $ds . $file);
                $className = str_replace('.php', '', $file);
                try {
                    $className::down();
                    CMigrationModel::Delete(array('NAME' => $className));
                    CQuery::commit();
                } catch ( CException $e ) {
                    CQuery::rollback();
                    echo $e;
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
	}

	public function commandUp($alias = 'app') {
		$ds = CAlias::ds();
		$migrationPath = $this->getMigrationPath($alias);
        echo $migrationPath . "...\n\n";
        echo "START DATABASE MIGRATION......\n\n";

        $skipMigrations = CMigrationModel::StaticGetAll(array(), 'TIMESTAMP');

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
                require($migrationPath . $ds . $file);
                $className = str_replace('.php', '', $file);
                try {
                    $className::up();
                    CMigrationModel::Insert(array('NAME' => $className, 'TIMESTAMP' => $t));
                    CQuery::commit();
                } catch ( CException $e ) {
                    CQuery::rollback();
                    echo $e;
                    echo "!!! ERROR MIGRATE: $className\n\n";
                    exit();
                }
                echo "ok\n";
            }
        }

        echo " SUCCESS......\n\n";
	}
}