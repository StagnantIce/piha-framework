<?php

namespace piha\modules\user;

use piha\IModule;
use piha\AModule;

class CUserModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }

}

return new CUserModule();