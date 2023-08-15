<?php

namespace piha\modules\user;

use \piha\IModule;
use \piha\AModule;

class CUserModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }

    public function configure(Array $config=null) {
    	parent::configure($config);
    	\Piha::service('user', new classes\CUser());
    }
}

return new CUserModule();