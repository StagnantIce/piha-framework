<?

use piha\CAlias;
use piha\AModule;
use piha\IModule;
use piha\CException;

require 'AClass.php';
require 'CException.php';
require 'CAlias.php';
require 'AModule.php';
require 'IModule.php';

class Piha extends AModule implements IModule {

    private $start_time = null;
    public $moduleRoutes = array();
    private static $autoloaderPaths = array();

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
            foreach(self::$autoloaderPaths as $path) {
                CAlias::includeFile(end($className) . '.php', $path);
            }
        }
    }

    public function getTime() {
        return (time() + microtime()) - self::app()->start_time;
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
            self::Add($key, isset($config['path']) ? $config['path'] : '@modules');
            if (isset($config['route'])) {
                $this->moduleRoutes[$config['route']] = $key;
            }
            if (isset($config['autoload'])) {
                foreach((array)$config['autoload'] as $autoload) {
                    self::$autoloaderPaths[] = $autoload;
                }
            }
            self::GetInstance($key)->configure($config);
        }
        return self::GetInstance();
    }

    public static function shutdown() {
        echo new CException();
    }

    public static function IncludeModule($id=null) {
        if (!self::HasInstance($id)) {
            self::Add($id);
        }
    }

    public static function GetModule($id=null) {
        if (!$id) {
            $id = self::Config('defaultModule');
        }
        if (!$id) {
            throw new CException("Module '$id' not found.");
        }
        return self::GetInstance($id);
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
        self::GetInstance('core')->start();
    }
}