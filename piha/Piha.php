<?

use piha\CAlias;
use piha\AModule;
use piha\IModule;
use piha\CException;
use piha\modules\core\classes\CRouter;
use piha\modules\core\classes\CRequest;
use piha\modules\core\classes\CController;
use piha\modules\core\classes\CView;

require 'AClass.php';
require 'CException.php';
require 'CAlias.php';
require 'AModule.php';
require 'IModule.php';

class Piha extends AModule implements IModule {

    private $start_time = null;
    private static $objects = array();

    public static function getDir() {
        return __DIR__;
    }

    public static function autoloader($className) {
        $className = explode('\\', $className);
        if ($className[0] === 'piha') {
            array_shift($className);
            array_unshift($className, '@piha');
            $fileName = end($className) . '.php';
            array_pop($className);
            CAlias::requireFile($fileName, $className);
        } else {
            foreach(self::app()->config('autoload', array()) as $path) {
                CAlias::includeFile(end($className) . '.php', $path);
            }
        }
    }

    public function getTime() {
        return (time() + microtime()) - self::app()->start_time;
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

    public static function app($dir=__DIR__) {
        if (!self::HasInstance()) {
            self::SetInstance(new self($dir));
        }
        return self::GetInstance();
    }

    public function configure(Array $config = null) {
        $config = $config ?: array();
        $configs = array_replace_recursive(CAlias::requireFile('config.php', '@piha'), $config);
        parent::configure($configs['piha']);
        unset($configs['piha']);
        foreach($configs as $key => $config) {
            AModule::Add($key);
            self::GetInstance($key)->configure($config);
        }
        return self::GetInstance();
    }

    public static function shutdown() {
        echo new CException();
    }

    private function __construct($dir) {
        CAlias::SetAlias('@piha', __DIR__);
        CAlias::SetAlias('@modules', array('@piha', 'modules'));
        CAlias::SetAlias('@webroot', $dir);
        CAlias::SetAlias('@demo', array('@piha', '..', 'demo'));

        $this->start_time = time() + microtime();
        spl_autoload_register('Piha::autoloader');
        //register_shutdown_function(self::className('shutdown'));
        date_default_timezone_set('Europe/Moscow');
    }

    public function start() {
        defined('PIHA_CONSOLE') or define('PIHA_CONSOLE', false);
        defined('PIHA_INCLUDE') or define('PIHA_INCLUDE', false);

        $request = new CRequest();
        $router = new CRouter($request);
        $this->service('request', $request);
        $this->service('router', $router);
        if (PIHA_CONSOLE === false && PIHA_INCLUDE === false) {
            $controller = $router->getController();
            $this->service('controller', $controller);
            $controller->runAction();
        }
    }
}