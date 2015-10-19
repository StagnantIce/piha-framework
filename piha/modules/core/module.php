<?php

class CCoreModule extends AModule implements IModule {

    public function getDir() {
        return __DIR__;
    }

    public function getDirPaths() {
    	$paths = array('classes');
        foreach ($this->config('paths') as $path) {
        	$paths[] = PIHA_BASE_PATH . DS . $path;
        }
        return $paths;
    }
}

return CCoreModule::Register();