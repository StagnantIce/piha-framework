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
        $alias = '@'.$module;
        CAlias::SetAlias($alias, array('@modules', $module));
        $obj = CAlias::requireFile('module.php', $alias);
        self::SetInstance($obj);
        CAlias::includeFile('events.php', $alias);
    }

    public function configure(Array $config=null) {
        $this->getObjectModule()->config = is_array($config) ? $config : (is_string($config) ? CAlias::requireFile($config) : null);
    }

    public static function HasInstance() {
        return isset(self::$modules[static::GetID()]);
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

    public static function config($param, $default=null) {
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
}
