<?php

/**
* AModule
* класс для организации модулей
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/

namespace piha;


abstract class AModule {

    /** @var static array Массив объектов модулей */
    private static $modules = array();

    public static function GetID() {
        return basename(static::getDir());
    }

    public static function Add($module, $path=null) {
        CAlias::path($module, array('modules', $module));
        CAlias::requireFile('module.php', $module);
        CAlias::includeFile('events.php', $module);
    }

    public static function AddAll() {
        foreach(func_get_args() as $module) {
            self::Add($module);
        }
    }

    public function configure($config=null) {
        $this->getObjectModule()->config = is_array($config) ? $config : (is_string($config) ? CAlias::requireFile($config) : null);
    }

    public static function ConfigureAll($configs) {
        $configs = is_array($configs) ? $configs : (is_string($configs) ? CAlias::requireFile($configs) : null);
        if ($configs) {
            foreach($configs as $key => $config) {
                self::GetInstance($key)->configure($config);
            }
        }
    }

    private function autoloader($className) {
        $className = explode('\\', $className);
        $className = end($className) . '.php';
        foreach( $this->getDirPaths() as $dir ) {
            if (is_array($dir)) {
                CAlias::includeFile($className, $dir);
            } else {
                CAlias::includeFile($className, array($dir));
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

    public static function config($param, $default=null) {
        if (is_string($param)) {
            $res = static::GetInstance()->config;
            foreach(explode('/', $param) as $p) {
                if (!array_key_exists($p, $res)) {
                    if(!is_null($default)) return $default;
                    throw new \Exception("Module '".static::GetID()."' config not found. Param with name '$param' not found in config.php");
                }
                $res = $res[$p];
            }
            return $res;
        }
        throw new \Exception('Error '.static::GetID().'::config. Please, see documentation');
    }
}
