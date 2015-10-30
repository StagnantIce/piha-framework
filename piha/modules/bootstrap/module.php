<?

namespace piha\modules\bootstrap;

use \piha\IModule;
use \piha\AModule;

class CBootstrapModule {

    public static function getDir() {
        return __DIR__;
    }

}

return new CBootstrapModule();