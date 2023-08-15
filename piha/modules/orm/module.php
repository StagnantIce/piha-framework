<?php

namespace piha\modules\orm;

use piha\CException;
use piha\IModule;
use piha\AModule;
use piha\modules\orm\classes\CMigrationCommand;

class COrmModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }

    public function configure(Array $config=null) {
    	parent::configure($config);
        //conect
		$db = $this->config('database');
		$className = $this->config('className');
        $conn = $className::$conn;
        if (is_null($conn)) {
            if (!function_exists('mysqli_init') && !extension_loaded('mysqli')) {
                throw new CException("We don\'t have mysqli!!!");
            }
            try {
                $className::$conn = new \mysqli($db['host'], $db['login'], $db['password'], $db['name']);
            } catch (\Exception $e) {
                throw new CException($e->getMessage() . ' ' . \json_encode($db) . '. Please configure mysql settings in config.php');
            }
    		$className::$conn->query("SET NAMES '".$db['encode']."'");
        }
        //register migrate
        \Piha::command('migrate', CMigrationCommand::className());
    }

    public static function quoteTableName($name)
    {
        $name = trim($name, '`');
        return  '`'. (trim($name, '{}') <> $name ? self::GetInstance()->config('database/prefix', '') . trim($name, '{}') : $name) .'`';
    }
}

return new COrmModule();