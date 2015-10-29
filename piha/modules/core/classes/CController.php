<?php
/**
* CController
* класс для работы с запросами от пользователя
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/
namespace piha\modules\core\classes;
use piha\modules\core\CCoreModule;
use piha\CAlias;

class CController {

    const METHOD_NAME = 'action';

    /** @var string $location путь до скрипта, вызвавший action */
    public $location = '';
    public $id;
    public $action_id;
    public $view_id;
    public $viewPath;
    public $layoutPath;
    public $layout = null;
    private $layoutRendering = false;

    /** @ignore */
    public function __construct($action) {
        $this->location = $_SERVER['PHP_SELF'];
        $this->action_id = $action;
        $this->id = static::GetID();
        $method = self::METHOD_NAME . ucfirst($action);
        if (method_exists($this, $method)) {
            $this->beforeAction($this->action_id);
            $this->$method();
        } else {
            throw new CCoreException('Bad method call ' . get_called_class().'->'.$method);
        }
    }

    public function beforeAction($action) {
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
      * @param string $action_id имя экешна без "action" вначале
      * @return string Путь для вызова экшена
      */
    public function url($action_id=null) {
        if (CCoreModule::Config('prettyUrl', false)) {
            if (strpos($action_id, '/') > 0) {
                $route = parse_url('/' . $action_id);
            } else {
              $route = parse_url('/' . static::GetID() . '/' . $action_id);
            }
            $route = '/' . trim($route['path'], '/');
            return $route;
        }
        $action_id = $action_id ?: $this->action_id;
        if (strpos($action_id, '/') > 0) {
            return $this->location . '?'. CRouter::PARAM_NAME . '=' . $action_id;
        }

        return $this->location . '?' . CRouter::PARAM_NAME. '=' . static::GetID() . '/' . $action_id;
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
    public function render($view_id = null, Array $params = null) {
        if (CCoreModule::Config('cleanView', true)) {
            unset($_POST, $_GET, $_REQUEST);
        }
        if ($params) {
            extract($params, EXTR_SKIP);
        }

        $this->view_id = $view_id ?: $this->action_id;

        if ($this->layout) {
            $this->layoutRendering = true;
            $this->requireFile($this->layout .'.php', CCoreModule::Config('layoutPath'));
            $this->layoutRendering = false;
        } else {
            $this->content();
        }
    }

    public function requireFile($file, $alias) {
        $file = CAlias::file($file, $alias);
        if (!file_exists($file)) {
            throw new CCoreException("File {$file} not found.");
        }
        require($file);
    }

    public function part($name) {
        $this->requireFile($name . '.php', $this->layoutRendering ? CCoreModule::Config('layoutPath') : CCoreModule::Config('viewPath'));
    }

    public function content() {
        $this->requireFile(CAlias::path(array($this->id, $this->view_id)) . '.php', CCoreModule::Config('viewPath'));
    }

    /**
      * Выполнить перенаправление по URL
      * @param string $url url для перенаправления, по умолчанию место вызова
      * @return null
      */
    public function redirect($url = '') {
        if (!$url) $url = $this->location;
        $url = $this->url($url);
        //CAjaxHelper::clear();
        Header("Location: $url");
        exit();
    }

}