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

class CView {

	/** @var string $_file - имя файла представления */
	private $_file = '';

	/** @var array $_context - контекст для передачи в представление */
	private $_context = null;

	/** @var string $_alias - путь до файла представления */
	private $_alias = '';

	/** @var string $partAlias - относительный путь до файла представления */
	private static $partAlias = '';

    /**
      * Создать новое представление
      * @param string $file - имя файла относительно алиаса
      * @param array $context - контекст в рамках которого будет отображаться представление
      * @return CView
      */
    public function __construct($file, Array $context = null) {
        $this->_file = $file;
        $this->_context = $context;
    }

    /**
      * Установить путь до представления в виде алиаса
      * @return null
      */
    public function setAlias($alias) {
    	$this->_alias = $alias;
    }

    /**
      * Получить путь до файла представления по имени файла
      * @return string
      */
    public function getAlias() {
    	if ($this->_alias) {
    		return $this->_alias;
    	}
    	$file = $this->_file;
    	if (strncmp($file,'//',2) === 0) {
    		return '@webroot';
    	} else if (strncmp($file,'/',1) === 0) {
    		return CCoreModule::Config('layoutPath');
    	} else {
    		return CCoreModule::Config('viewPath');
    	}
    }

    /**
      * Получить имя файла
      * @return string
      */
    public function getFile() {
    	return ltrim($this->_file, '/') . '.php';
    }

    /**
      * Установить представлению относительный путь
      * @return null
      */
    public function setPartAlias() {
    	$this->_alias = self::$partAlias;
    }

    /**
      * Вернуть представление в рамках контекста
      * @return string
      */
	public function render() {
		self::$partAlias = $this->getAlias();
        $file = CAlias::file($this->getFile(), self::$partAlias);
		if (!file_exists($file)) {
            throw new CException("File {$file} not found.");
        }
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
    	return \Piha::controller()->$name;
    }

    /**
      * Вызов методов контроллера из вьюшки
      * @param string $method - имя метода
      * @param string $ps - список параметров
      * @return mixed
      */
    public function __call($method, $ps) {
    	return call_user_func_array(array(\Piha::controller(), $method), $ps);
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