<?php

class CCoreModule extends AModule {

    public function getRoot() {
        return __DIR__;
    }

    public function getPaths() {
    	$paths = array('classes');
        foreach ($this->config('paths') as $path) {
        	$paths[] = BASE_PATH . DS . $path;
        }
        return $paths;
    }

    public static function GetAdminMenu() {
        return array();
    }
}

return CCoreModule::Register();