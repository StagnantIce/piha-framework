<?php

namespace piha\modules\core\classes;

use piha\modules\core\CCoreModule;

class CRouter {

    const PARAM_NAME = 'r';
    private $location = '';
    private $path = '';
    private $host = '';
    private $params = array();
    private $controller = null;
    private $method = '';

    public function __construct() {
        $this->location = $_SERVER['REQUEST_URI'];
        $url = parse_url($this->location);
        $this->path = $url['path'];
        $shema = isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https': 'http';
        $this->host = $shema . '://' . $_SERVER['SERVER_NAME'];
        $this->params = $_GET;
        $this->method = $_SERVER['REQUEST_METHOD'];

        $route = false;
        if (CCoreModule::Config('prettyUrl', false)) {
            $route = trim($this->path, '/');
        } else if (isset($this->params[self::PARAM_NAME])) {
            $route = trim($_GET[self::PARAM_NAME]);
        }
        if (!$route) {
            $route = CCoreModule::Config('homeController');
        }

        list($controller, $action) = explode('/', $route, 2);
        $controller = ucfirst($controller) . 'Controller';
        if (class_exists($controller)) {
            $this->controller = new $controller($action);
        } else {
            throw new CCoreException("Error route '{$route}'. Controller '{$controller}' not found.");
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