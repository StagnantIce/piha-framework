<?php

namespace piha\modules\core\classes;

use piha\modules\core\CCoreModule;
use piha\CException;

class CRouter {

    const PARAM_NAME = 'r';
    private $_route = '';

    public function __construct(CRequest $request) {
        $route = false;
        if (CCoreModule::Config('prettyUrl', false)) {
            $route = trim($request->path, '/');
        } else if ($route = $request->get(self::PARAM_NAME)) {
            $route = trim($route);
        }
        $this->_route = $route;
    }

    public function getController() {
        list($controller, $action, $params) = $this->getControllerParams($this->_route);

        if (class_exists($controller)) {
           return new $controller($action, $params);
        } else {
            throw new CException("Error route '{$this->_route}'. Controller '{$controller}' not found.");
        }
    }

    public function buildUrl($route = '', Array $params = null) {
        $params = $params ?: array();
        $host = '/';
        if (CCoreModule::Config('prettyUrl', false)) {
            $host .= $route . '/';
        } else {
            $params[self::PARAM_NAME] = $route;
        }
        if (CCoreModule::Config('smartUrl', false)) {
            list($controller, $action, $ps) = $this->getControllerParams($route);
            if (!class_exists($controller)) {
                throw new CException("Controller class {$controller} not found.");
            }
            $action = $controller::getActionName($action);
            if (!method_exists($controller, $action)) {
                throw new CException("Action method {$action}() for controller {$controller} not found.");
            }
            $f = new \ReflectionMethod($controller, $action);
            $fParams = array_slice($f->getParameters(), count($ps));
            foreach ($fParams as $param) {
                if (isset($params[$param->name])) {
                    $host .= $params[$param->name] . '/';
                    unset($params[$param->name]);
                }
            }
        }
        return $host . ($params ? '?' . http_build_query($params) : '');
    }

    private function getControllerParams($route = '') {
        if (!$route) {
            $route = CCoreModule::Config('homeController');
        }
        $arrRoute = explode('/', $route);
        if (count($arrRoute) < 2) {
            throw new CException("Error route url {$route}");
        }
        $controller = $arrRoute[0];
        $action = $arrRoute[1];
        $params = array_slice($arrRoute, 2);
        return array(ucfirst($controller) . 'Controller', $action, $params);
    }
}