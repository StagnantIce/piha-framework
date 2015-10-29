<?php

namespace piha\modules\core\classes;

use piha\modules\core\CCoreModule;

class CRouter {

    const PARAM_NAME = 'r';

    public function __construct() {
        if (CCoreModule::Config('prettyUrl', false)) {
            $route = parse_url($_SERVER['REQUEST_URI']);
            $route = trim($route['path'], '/');
        } else {
            isset($_GET[self::PARAM_NAME]) && $route = trim($_GET[self::PARAM_NAME]);
        }
        if (!isset($route)) {
            $route = CCoreModule::Config('homeController');
        }
        list($controller, $action) = explode('/', $route, 2);
        $controller = ucfirst($controller) . 'Controller';
        return new $controller($action);
    }
}