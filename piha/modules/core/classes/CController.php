<?php
/**
* CController
* класс для работы с запросами от пользователя
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/

class CController {

    const METHOD_NAME = 'action';

    /** @var string $location путь до скрипта, вызвавший action */
    public $location = '';
    public $id;
    public $action_id;
    public $viewPath;
    public $layoutPath;

    /** @ignore */
    public function __construct($action) {
        $this->location = $_SERVER['PHP_SELF'];
        $this->action_id = $action;
        $this->id = static::GetID();
        $method = self::METHOD_NAME . $action;
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            throw new BadMethodCallException(get_called_class().'->'.$method);
        }
    }

    /**
      * Возвращает ID контроллера
      * @return string ID контроллера
      */
    public static function GetID() {
        return str_replace('Controller', '', lcfirst(get_called_class()));
    }

    /**
      * Возвращает путь для вызова экшена
      * @param string $actionName имя экешна без "action" вначале
      * @return string Путь для вызова экшена
      */
    public function getUrl($actionName) {
        if (strpos($actionName, '/') > 0) {
            return $this->location . '?'. self::PARAM_NAME . '=' . $actionName;
        }
        return $this->location . '?' . self::PARAM_NAME. '=' . static::GetID() . '/' . $actionName;
    }

    /**
      * Очищает буфер и выводит JSON
      * @param array $arr массив для JSON
      * @return null
      */
    public function renderJSON($arr = array()) {
        CAjaxHelper::send($arr);
    }

    /**
      * Получить из массива значение по имени ключа
      * @param string $arr массив со значениями
      * @param string $key имя ключа
      * @param string $default значение по умолчанию в случае отсутствия параметра
      * @return mixed значение параметра
      */
    public function fromArray(Array $arr, $key, $default = null) {
        if (isset($arr[$key])) return $arr[$key];
        return $default;
    }

    /**
      * Получить из массива $_GET значение по имени ключа
      * @param string $key имя ключа
      * @param string $default значение по умолчанию в случае отсутствия параметра
      * @return mixed значение параметра
      */
    public function get($key, $default = null) {
        return $this->fromArray($_GET, $key, $default);
    }

    /**
      * Получить из массива $_POST значение по имени ключа
      * @param string $key имя ключа
      * @param string $default значение по умолчанию в случае отсутствия параметра
      * @return mixed значение параметра
      */
    public function post($key, $default = null) {
        return $this->fromArray($_POST, $key, $default);
    }

    /**
      * Установить, проверить существование или вытащить flash сообщение
      * @param string $type тип сообщения
      * @param mixed $message сообщение, False в случае проверки и null в случае извлечения
      * @return mixed null, boolean или сообщение в случае извлечения
      */
    public function flash($type, $message = null) {
        if ($message === False) {
            return isset($_SESSION['FLASH_MESSAGE'][$type]);
        }
        if ($message === null && isset($_SESSION['FLASH_MESSAGE'][$type])) {
            $message = $_SESSION['FLASH_MESSAGE'][$type];
            unset($_SESSION['FLASH_MESSAGE'][$type]);
            return $message;
        }
        $_SESSION['FLASH_MESSAGE'][$type] = $message;
    }

    /**
      * Отрендерить шаблон согласно параметрам
      * @param string $path путь до шаблона, по умолчанию место вызова
      * @param array $params список параметров
      * @return null
      */
    public function render($path = null, Array $params = null) {
        unset($_POST, $_GET, $_REQUEST);
        if ($params) {
            extract($params, EXTR_SKIP);
        }
        $this->viewPath = CCore::module()->config('viewPath') . DS .  $this->id . DS . $this->action_id . '.php';
        $this->layoutPath = CCore::module()->config('layoutPath') . DS . $this->layout .'.php';

        if ($this->layout) {
            $this->requireFile($this->layoutPath);
        } else {
            $this->content();
        }
    }

    public function part($path, $name) {
        $this->requireFile(CCore::module()->config('viewPath') . DS .  $path . DS . $name . '.php');
    }

    public function content() {
        $this->requireFile($this->viewPath);
    }

    public function requireFile($path) {
        if (!file_exists($path)) {
            throw new CCoreException("File $path not found! $message");
        }
        return require($path);
    }

    /**
      * Выполнить перенаправление по URL
      * @param string $url url для перенаправления, по умолчанию место вызова
      * @return null
      */
    public function redirect($url = '') {
        if (!$url) $url = $this->location;
        //CAjaxHelper::clear();
        Header("Location: $url");
        exit();
    }

}