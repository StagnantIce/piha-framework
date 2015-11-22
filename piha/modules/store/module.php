<?

namespace piha\modules\store;

use \piha\IModule;
use \piha\AModule;

class CStoreModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }

}

return new CStoreModule();