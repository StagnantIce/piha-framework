<?php

/**
* AModule
* класс для организации модулей
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/

namespace piha;


abstract class AModule extends AClass {

    /** @var static array Массив объектов модулей */
    private static $modules = array();
    private $config = array();
    private static $objects = array();
    private static $commands = array();

    public static function GetID() {
        return basename(static::getDir());
    }

    public static function Add($module, $path) {
        if (isset(self::$modules[$module])) {
            throw new CException("Module '{$module}' already added.");
        }
        $alias = '@'.$module;
        CAlias::SetAlias($alias, array(CAlias::GetPath($path), $module));
        $obj = CAlias::requireFile('module.php', $alias);
        self::SetInstance($obj);
        CAlias::includeFile('events.php', $alias);
    }

    public function configure(Array $config=null) {
        $this->getObjectModule()->config = is_array($config) ? $config : (is_string($config) ? CAlias::requireFile($config) : null);
        if (isset($config['services'])) {
            foreach($config['services'] as $name => $service) {
                self::service($name, $service);
            }
        }
        if (isset($config['commands'])) {
            foreach($config['commands'] as $name => $command) {
                self::command($name, $command);
            }
        }
    }

    public static function HasInstance($id=null) {
        $id = $id?:static::GetID();
        return isset(self::$modules[$id]);
    }

    public static function SetInstance($obj) {
        self::$modules[basename($obj->getDir())] = $obj;
    }

    public static function GetInstance($id=null) {
        $id = $id?:static::GetID();
        if (isset(self::$modules[$id])) {
            return self::$modules[$id];
        }
        throw new CException("Module '{$id}' not found");
    }

    public static function getObjectModule() {
        $className = get_called_class();
        return $className::GetInstance();
    }

    public static function SetConfig($param, $value) {
        static::GetInstance()->config[$param] = $value;
    }

    public static function Config($param, $default=null) {
        if (is_string($param)) {
            $res = static::GetInstance()->config;
            foreach(explode('/', $param) as $p) {
                if (!array_key_exists($p, $res)) {
                    if (strpos($param, '@') !== false) {
                        if(!is_null($default)) {
                            return $default;
                        }
                    } else {
                        return $default;
                    }
                    throw new CException("Module '".static::GetID()."' config require and not found. Param with name '$param' not found in config.php");
                }
                $res = $res[$p];
            }
            return $res;
        }
        throw new CException('Error '.static::GetID().'::config. Please, see documentation');
    }

    public static function command($name, $command)
    {
        if (isset(self::$commands[$name])) {
             throw new CException("Command '{$name}' is already exists.");
        }
        self::$commands[$name] = $command;
    }

    public static function getCommands()
    {
        print_r(array_keys(self::$commands));
    }

    public static function execute($name, $argv) {
        if (!isset(self::$commands[$name])) {
            throw new CException("Command '{$name}' not found.");
        }
        if (class_exists(self::$commands[$name])) {
            $class = self::$commands[$name];
            return new $class($name, $argv);
        }

        if (!is_callable(self::$commands[$name])) {
            throw new CException("Command '{$name}' is not callable.");
        }
        return call_user_func_array(self::$commands[$name], array($name, $argv));
    }

    public static function service($name, $mixed) {
        if (isset(self::$objects[$name])) {
             throw new CException("Object '{$name}' is already exists in Service Locator.");
        }
        self::$objects[$name] = $mixed;
    }

    public static function __callStatic($name, $params) {
        if (!isset(self::$objects[$name])) {
            throw new CException("Object '{$name}' is not register in Service Locator.");
        }else if (is_callable(self::$objects[$name])) {
            return call_user_func(self::$objects[$name]);
        } else if (is_object(self::$objects[$name])) {
            return self::$objects[$name];
        }
        throw new CException("Object '{$name}' is not callable in Service Locator.");
    }
}
