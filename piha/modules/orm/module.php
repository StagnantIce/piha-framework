<?php

namespace piha\modules\orm;

use \piha\IModule;
use \piha\AModule;
//use \piha\modules\orm\classes\CMysqlConnection;

class COrmModule extends AModule implements IModule {

    public function getDir() {
        return __DIR__;
    }

    public function getDirPaths() {
        return array(
            array(self::GetID(), 'classes'),
            array(self::GetID(), 'models')
        );
    }

    public function configure($config=null) {
    	parent::configure($config);
		$db = $this->config('database');
		$className = $this->config('className');
		$className::$conn = new \mysqli($db['host'], $db['login'], $db['password'], $db['name']);
		$className::$conn->query("SET NAMES '".$db['encode']."'");
    }

    public static function quoteTableName($name)
    {
        return  '`'. (trim($name, '{}') <> $name ? self::GetInstance()->config('database/prefix', '') . trim($name, '{}') : $name) .'`';
    }
}

return COrmModule::Register();