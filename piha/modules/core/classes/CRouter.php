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
        list($module, $controller, $action, $params) = $this->getControllerParams($this->_route);

        if (class_exists($controller)) {
           return new $controller($module::GetInstance(), $action, $params);
        } else {
            throw new CException("Error route '{$this->_route}'. Controller '{$controller}' not found.");
        }
    }

    public function buildUrl($route = '', Array $params = null) {
        $params = $params ?: array();
        $host = '/';
        $route = trim($route, '/');
        if (CCoreModule::Config('prettyUrl', false)) {
            $host .= $route . '/';
        } else {
            $params[self::PARAM_NAME] = $route;
        }
        list($module, $controller, $action, $ps) = $this->getControllerParams($route);
        if (!class_exists($controller)) {
            throw new CException("Controller class {$controller} not found.");
        }
        $action = $controller::getActionName($action);
        $controller::className($action);
        //if (!method_exists($controller, $action)) {
        //    throw new CException("Action method {$action}() for controller {$controller} not found.");
        //}
        if (CCoreModule::Config('smartUrl', false)) {
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
        $module = \Piha::Config('defaultModule');
        if (!$module) {
            throw new CException("Default module config not found.");
        }
        $arrRoute = array();
        if ($route) {
            $arrRoute = explode('/', $route);
            if (count($arrRoute) > 0) {
                if (isset(\Piha::app()->moduleRoutes[$arrRoute[0]])) {
                    $module = \Piha::app()->moduleRoutes[$arrRoute[0]];
                    $arrRoute = array_slice($arrRoute, 1);
                    $route = implode('/', $arrRoute);
                }
            }
        }
        $moduleObj = \Piha::GetModule($module);
        if (!$arrRoute) {
            $arrRoute = explode('/', $moduleObj::Config('defaultController'));
        }

        if (count($arrRoute) < 2) {
            throw new CException("Error route url '{$route}' for module '{$module}'");
        }
        $controller = $arrRoute[0];
        $action = $arrRoute[1];
        $params = array_slice($arrRoute, 2);
        return array($moduleObj, $moduleObj::Config('controllerNamespace', '\\') . ucfirst($controller) . 'Controller', $action, $params);
    }
}