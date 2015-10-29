<?php

namespace piha\modules\core;

use \piha\IModule;
use \piha\AModule;
use \piha\CAlias;

class CCoreModule extends AModule implements IModule {

    public function getDir() {
        return __DIR__;
    }
}

return new CCoreModule();