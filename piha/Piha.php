<?

use piha\CAlias;
use piha\AModule;
use piha\IModule;
use piha\modules\core\classes\CRouter;

require 'CAlias.php';
require 'AModule.php';
require 'IModule.php';

class Piha extends AModule implements IModule {

    private $start_time = null;

    public static function getDir() {
        return __DIR__;
    }

    public static function autoloader($className) {
        //if (strpos($className, 'piha\\') !== false) {
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

    public $router = null;

    public static function app(Array $modules=null, Array $config=null) {
        if (!self::HasInstance()) {
            if (!$modules) {
                throw new \Exception('Piha modules not defined');
            }
            self::SetInstance(new self($modules));

            $config = array_replace_recursive(CAlias::requireFile('config.php', '@piha'), $config);
            AModule::ConfigureAll($config);

            self::GetInstance()->start();
        }
        return self::GetInstance();
    }

    private function __construct(Array $modules) {
        CAlias::path('@piha', __DIR__);
        CAlias::path('@modules', array('@piha', 'modules'));

        $this->start_time = time() + microtime();

        spl_autoload_register('Piha::autoloader');

        foreach($modules as $module) {
            AModule::Add($module);
        }
    }

    private function start() {
        defined('PIHA_CONSOLE') or define('PIHA_CONSOLE', false);
        defined('PIHA_INCLUDE') or define('PIHA_INCLUDE', false);

        if (PIHA_CONSOLE == false && PIHA_INCLUDE == false) {
            $this->router = new CRouter();
            $this->router->runController();
        }
    }
}


spl_autoload_register('Piha::autoloader');