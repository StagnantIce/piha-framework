<?

use piha\CAlias;
use piha\AModule;
use piha\IModule;
use piha\modules\core\classes\CRouter;
use piha\modules\core\classes\CRequest;
use piha\modules\core\classes\CController;
use piha\modules\core\classes\CView;

require 'CException.php';
require 'CAlias.php';
require 'AModule.php';
require 'IModule.php';

class Piha extends AModule implements IModule {

    private $start_time = null;
    private $request = null;
    private $router = null;
    private $controller = null;


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

    public static function request() {
        return self::app()->request;
    }

    public static function router() {
        return self::app()->router;
    }

    public static function controller() {
        return self::app()->controller;
    }

    public static function app($dir) {
        if (!self::HasInstance()) {
            self::SetInstance(new self($dir));
        }
        return self::GetInstance();
    }

    public function configure(Array $config = null) {
        $configs = array_replace_recursive(CAlias::requireFile('config.php', '@piha'), $config);
        parent::configure($configs['piha']);
        $modules = $this->config('modules');
        foreach($modules as $module) {
            AModule::Add($module);
        }
        unset($configs['piha']);
        foreach($configs as $key => $config) {
            self::GetInstance($key)->configure($config);
        }
        return self::GetInstance();
    }

    private function __construct($dir) {
        CAlias::SetAlias('@piha', __DIR__);
        CAlias::SetAlias('@modules', array('@piha', 'modules'));
        CAlias::SetAlias('@webroot', $dir);

        $this->start_time = time() + microtime();
        spl_autoload_register('Piha::autoloader');
    }

    public function start() {
        defined('PIHA_CONSOLE') or define('PIHA_CONSOLE', false);
        defined('PIHA_INCLUDE') or define('PIHA_INCLUDE', false);

        if (PIHA_CONSOLE === false && PIHA_INCLUDE === false) {
            $this->request = new CRequest();
            $this->router = new CRouter($this->request);
            $this->view = new CView();
            $this->controller = $this->router->getController();
            $this->controller->runAction();
        }
    }
}