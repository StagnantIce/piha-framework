<?php

class CCoreModule extends AModule {

    public function getRoot() {
        return __DIR__;
    }

    public function getPaths() {
    	$paths = array('classes');
        foreach ($this->config('paths') as $path) {
        	$paths[] = PIHA_BASE_PATH . DS . $path;
        }
        return $paths;
    }
}

return CCoreModule::Register();