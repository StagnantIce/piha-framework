<?php

namespace piha\modules\bootstrap3;

use \piha\IModule;
use \piha\AModule;

class CBSModule {

    public static function getDir() {
        return __DIR__;
    }

}

return new CBSModule();