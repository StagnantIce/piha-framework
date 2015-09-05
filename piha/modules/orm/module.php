<?php

class COrmModule extends AModule {

    public function getRoot() {
        return __DIR__;
    }

    public function getPaths() {
        return array('classes');
    }

    public static function GetAdminMenu() {
        return array();
    }

    public function configure($config=null) {
    	parent::configure($config);
		$db = $this->config('database');
		$className = $this->config('className');
		$className::$conn = new mysqli($db['host'], $db['login'], $db['password'], $db['name']);
		$className::$conn->query("SET NAMES '".$db['encode']."'");
    }
}

return COrmModule::Register();