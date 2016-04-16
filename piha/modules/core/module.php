<?php

namespace piha\modules\core;

use \piha\IModule;
use \piha\AModule;
use \piha\modules\core\classes\CRouter;
use \piha\modules\core\classes\CRequest;
use \piha\modules\core\classes\CAsset;


class CCoreModule extends AModule implements IModule {

    public static function getDir() {
        return __DIR__;
    }

    public function configure(Array $config=null) {
        parent::configure($config);
    }

    public static function start() {
        defined('PIHA_CONSOLE') or define('PIHA_CONSOLE', false);
        defined('PIHA_INCLUDE') or define('PIHA_INCLUDE', false);

        if (PIHA_CONSOLE === false) {
            session_start();
        }

        $request = new CRequest();
        $router = new CRouter($request);
        \Piha::service('request', $request);
        \Piha::service('router', $router);
        \Piha::service('asset', new CAsset());
        if (PIHA_CONSOLE === false && PIHA_INCLUDE === false) {
            $controller = $router->getController();
            \Piha::service('controller', $controller);

            \Piha::controller()->runAction();
        }
    }
}

return new CCoreModule();