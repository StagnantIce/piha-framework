<?php

namespace piha\modules\storage;

use \piha\IModule;
use \piha\AModule;

class CStorageModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }

    public function configure(Array $config=null) {
    	parent::configure($config);
    }
}

return new CStorageModule();