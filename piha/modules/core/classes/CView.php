<?php
/**
* CView
* класс для работы с представлениями
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/

namespace piha\modules\core\classes;
use piha\modules\core\CCoreModule;
use piha\CAlias;
use piha\CException;
use piha\AClass;

class CView extends AClass {

    /** @var object $_middleWare - объект для расширения (например контроллер) */
    private $_middleWare = null;

    /** @var string $_file - имя файла представления */
    private $_file = '';

    /** @var array $_context - контекст для передачи в представление */
    private $_context = null;

    /** @var string $_path - путь до файла представления */
    private $_path = '';

    public static $currentPath = '';

    public function getContext($context) {
        return $context;
    }

    /**
      * Создать новое представление
      * @param string $file - имя файла относительно алиаса
      * @param array $context - контекст в рамках которого будет отображаться представление
      * @return CView
      */
    public function __construct($file, Array $context = null, $middleware = null) {
        $this->_file = $file;
        $this->_context = $this->getContext($context);
        $this->_middleWare = $middleware;
    }

    public function getPath() {
        return $this->_path;
    }

    /**
      * Установить путь до представления в виде алиаса или пути
      * @return null
      */
    public function setPath($path) {
    	$this->_path = $path;
    }

    /**
      * Получить путь до файла представления по имени файла
      * @return string
      */
    public function getAlias($file) {
    	if (strncmp($file,'//',2) === 0) {
    		return '@webroot';
    	} else if (strncmp($file,'/',1) === 0) {
    		return $this->_middleWare->getLayoutPath();
    	} else {
    		return $this->getPath();
    	}
    }

    public function css($file, $path=null) {
        if (!$path) {
            $path = $this->getAlias($file);
        }
        $file = CAlias::file(self::getFile($file,'css'), $path);
        \Piha::asset()->css($file);
    }

    public function js($file, $path=null) {
        if (!$path) {
            $path = $this->getAlias($file);
        }
        $file = CAlias::file(self::getFile($file,'js'), $path);
        \Piha::asset()->js($file);
    }

    /**
      * Получить имя файла
      * @return string
      */
    public static function getFile($file, $ext='php') {
    	return ltrim($file, '/') . '.'.$ext;
    }

    /**
      * Вернуть представление в рамках контекста
      * @return string
      */
    public function render($path=null, $ext='php') {
        // если не задали путь, ищем по алиасу
        if (!$path) {
            $path = $this->getAlias($this->_file);
        }

        // если нет алиаса, и не передали путь, считаем как текущий
        if (!$path) {
            $path = self::$currentPath;
        }

        if ($path) {
            self::$currentPath = $path;
        }

        $file = CAlias::file(self::getFile($this->_file, $ext), $path);
        if (!file_exists($file)) {
            throw new CException("Error render {$this->_file}. File {$file} not found. Path: $path");
        }
        $this->_path = dirname($file);
        ob_start();
        ob_implicit_flush(false);
        extract($this->_context ?: array(), EXTR_OVERWRITE);
        require($file);
        return ob_get_clean();
    }

    /**
      * Доступ к свойствам контроллера из вьюшки
      * @param string $name - имя свойства
      * @return mixed
      */
    public function __get($name) {
        if ($this->_middleWare) {
           $this->_middleWare->view = $this;
    	   return $this->_middleWare->$name;
        }
        return parent::__get($name);
    }

    /**
      * Вызов методов контроллера из вьюшки
      * @param string $method - имя метода
      * @param string $ps - список параметров
      * @return mixed
      */
    public function __call($method, $ps) {
        if ($this->_middleWare) {
           $this->_middleWare->view = $this;
    	   return call_user_func_array(array($this->_middleWare, $method), $ps);
        }
        return parent::__call($method, $ps);
    }

    /**
      * Извлечь данные из выражения
      * @param mixed $mixed - выражение
      * @param mixed $objectMixed - параметры если необходимы
      */
	public static function Value($mixed = null, $objectMixed = null) {
		if (is_callable($mixed)) {
            $objectMixed = is_array($objectMixed) ? $objectMixed : array('data' => $objectMixed);
			return call_user_func_array($mixed, $objectMixed);
        } else if (is_string($mixed) && strpos($mixed, '$data') !== false) {
			if (is_object($objectMixed)) {
                $data = $objectMixed;
            } else if (is_array($objectMixed)) {
                $data = new \stdclass();
				foreach($objectMixed as $param) {
					foreach($param as $key => $value) {
						$data->$key = $value;
					}
				}
            }
            return eval('return ' . rtrim($mixed, ';') . ';');
		}
		return $mixed;
	}
}