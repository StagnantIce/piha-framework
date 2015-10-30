<?php

namespace piha\modules\core;

use \piha\IModule;
use \piha\AModule;

class CCoreModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }
}

return new CCoreModule();