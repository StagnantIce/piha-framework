<?

use piha\CAlias;
use piha\AModule;

require 'CAlias.php';

class Piha {

    private $start_time = null;
    private static $app = null;

    public static function autoloader($className) {
        if (strpos($className, 'piha\\') !== false) {
            $className = explode('\\', $className);
            array_shift($className);
            array_unshift($className, '@piha');
            $fileName = end($className) . '.php';
            array_pop($className);
            CAlias::requireFile($fileName, $className);
        }
    }

    public function getTime() {
        return (time() + microtime()) - self::app()->start_time;
    }

    private $controller = null;

    public function app(Array $modules=null, Array $config=null) {
        if (!self::$app) {
            if (!$modules) {
                throw new \Exception('Piha modules not defined');
            }
            self::$app = new self($modules, $config);
        }
        return self::$app;
    }

    private function __construct(Array $modules, Array $config=null, $route=false) {
        CAlias::path('@piha', __DIR__);
        CAlias::path('@modules', array('@piha', 'modules'));

        $this->start_time = time() + microtime();

        spl_autoload_register('Piha::autoloader');

        foreach($modules as $module) {
            AModule::Add($module);
        }

        $config = array_replace_recursive(CAlias::requireFile('config.php', '@piha'), $config);
        AModule::ConfigureAll($config);

        defined('PIHA_CONSOLE') or define('PIHA_CONSOLE', false);
        defined('PIHA_INCLUDE') or define('PIHA_INCLUDE', false);

        if (PIHA_CONSOLE == false && PIHA_INCLUDE == false) {
            $this->controller = new CRouter();
        }
    }
}


spl_autoload_register('Piha::autoloader');