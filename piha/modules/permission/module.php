<?

namespace piha\modules\permission;

use \piha\IModule;
use \piha\AModule;

class CPermissionModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }

    public function configure(Array $config=null) {
    	parent::configure($config);
    	\Piha::service('permission', new classes\CPermission());
    }
}

return new CPermissionModule();