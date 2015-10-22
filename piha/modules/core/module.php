<?php

namespace piha\modules\core;

use \piha\IModule;
use \piha\AModule;
use \piha\CAlias;

class CCoreModule extends AModule implements IModule {

    public function getDir() {
        return __DIR__;
    }

    public function getDirPaths() {
    	$paths = array( array(self::GetID(), 'classes') );
        foreach ($this->config('paths') as $path) {
        	$paths[] = $path;
        }
        return $paths;
    }
}

return CCoreModule::Register();