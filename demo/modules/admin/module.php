<?php

use \piha\IModule;
use \piha\AModule;

class CAdminModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }
}

return new CAdminModule();