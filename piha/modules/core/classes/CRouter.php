<?php

class CRouter {

    const PARAM_NAME = 'action';

    public function __construct() {
        isset($_GET[self::PARAM_NAME]) && $route = trim($_GET[self::PARAM_NAME]);
        if (!isset($route)) {
            $route = CCoreModule::GetInstance()->config('homeController');
        }
        list($controller, $action) = explode('/', $route, 2);
        $controller = ucfirst($controller) . 'Controller';
        if (class_exists($controller)) { // check controller
            return new $controller($action);
        } else {
            throw new CCoreException("Class $controller not found.");
        }
    }
}