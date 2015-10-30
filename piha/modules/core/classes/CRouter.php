<?php

namespace piha\modules\core\classes;

use piha\modules\core\CCoreModule;
use piha\CException;

class CRouter {

    const PARAM_NAME = 'r';
    private $params = array();
    private $controller = null;

    public function __construct() {
        $route = false;
        if (CCoreModule::Config('prettyUrl', false)) {
            $route = trim(\Piha::request()->path, '/');
        } else if ($route = \Piha::request()->get(self::PARAM_NAME)) {
            $route = trim($route);
        }
        if (!$route) {
            $route = CCoreModule::Config('homeController');
        }

        list($controller, $action) = explode('/', $route, 2);
        $controller = ucfirst($controller) . 'Controller';
        if (class_exists($controller)) {
            $this->controller = new $controller($action);
        } else {
            throw new CException("Error route '{$route}'. Controller '{$controller}' not found.");
        }
    }

    public function runController() {
        $this->controller->run();
    }

    public function buildUrl($route = '', Array $params = null) {
        if (!$route) {
            $route = 'index';
        }
        if (strpos($route, '/') === false) {
            $route = $this->controller->id . '/' . $route;
        }
        $params = $params ?: array();
        $host = '/';
        if (CCoreModule::Config('prettyUrl', false)) {
            $host .= $route . '/';
        } else {
            $params[self::PARAM_NAME] = $route;
        }
        return $host . ($params ? '?' . http_build_query($params) : '');
    }
}