<?php

/** Класс для организации модулей */
require 'IModule.php';

abstract class AModule {

    private static $modules = array();

    public static function GetID() {
        return basename(static::getDir());
    }

    public static function file($path, $message = '') {
        if (!file_exists($path)) {
            throw new Exception("File $path not found! $message");
        }
        return require($path);
    }

    public static function Add($module, $path=PIHA_CORE_PATH) {
        $module = 'modules' . DS . $module . DS;
        $abs_module = $path . DS . $module;
        if (is_dir( $abs_module )) {
            // attach module.php
            if (file_exists($abs_module . 'module.php')) {
                require($abs_module . 'module.php');
            }
            // attach events.php
            $eventFile = $abs_module . DS . 'events.php';
            if (file_exists($eventFile)) {
                require($eventFile);
            }
        } else {
            throw new Exception("Module path $abs_module not found.");
        }
    }

    public static function AddAll() {
        foreach(func_get_args() as $module) {
            self::Add($module);
        }
    }

    public function configure($config=null) {
        $this->getObjectModule()->config = is_array($config) ? $config : (is_string($config) ? self::file($config) : null);
    }

    public static function ConfigureAll($configs) {
        $configs = is_array($configs) ? $configs : (is_string($configs) ? self::file($configs) : null);
        if ($configs) {
            foreach($configs as $key => $config) {
                self::GetInstance($key)->configure($config);
            }
        }
    }

    private function autoloader($className) {
        $root = static::getDir();
        foreach( $this->getDirPaths() as $dir ) {
            $dir = strpos($dir, DS) === false ? $root . DS . $dir : $dir;
            $file = $dir . DS . $className. '.php';
            if (file_exists($file)) {
                require_once($file);
                return;
            }
        }
    }

    public static function Register() {
        self::$modules[static::GetID()] = new static;
        spl_autoload_register(array(self::$modules[static::GetID()], 'autoloader'));
        return self::$modules[static::GetID()];
    }

    public static function GetInstance($id=null) {
        $id = $id?:static::GetID();
        if (isset(self::$modules[$id])) {
            return self::$modules[$id];
        }
        throw new Exception("Module $id not found");
    }

    public static function getObjectModule() {
        $className = get_called_class();
        return $className::GetInstance();
    }

    public function config($param, $default=null) {
        if (is_string($param)) {
            $res = $this->config;
            foreach(explode('/', $param) as $p) {
                if (!array_key_exists($p, $res)) {
                    if(!is_null($default)) return $default;
                    throw new Exception("Module '".static::GetID()."' config not found. Param with name '$param' not found in config.php");
                }
                $res = $res[$p];
            }
            return $res;
        }
        throw new Exception('Error '.static::GetID().'::config. Please, see documentation');
    }
}
