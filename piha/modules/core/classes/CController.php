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
use piha\CException;

class CController {

    const METHOD_NAME = 'action';

    public $id;
    public $action_id;
    public $view_id;
    public $viewPath;
    public $layoutPath;
    public $layout = null;
    private $layoutRendering = false;
    private $viewParams = null;
    private $params = null;

    /** @ignore */
    public function __construct($action, Array $params = null) {
        $this->action_id = $action ?: 'index';
        $this->id = static::GetID();
        $this->params = $params;
    }

    public static function getActionName($action) {
        return self::METHOD_NAME . ucfirst($action);
    }

    public function run() {
        $method = $this->getActionName($this->action_id);
        if (method_exists($this, $method)) {
            $this->beforeAction($this->action_id);
            call_user_func_array(array($this, $method), $this->params ?: array());
        } else {
            throw new CException('Bad method call ' . get_called_class().'->'.$method);
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
    public function url($route = '', Array $params=null) {
        return \Piha::router()->buildUrl($route, $params);
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
        $this->view_id = $view_id ?: $this->action_id;

        if ($this->layout) {
            $this->viewParams = $params;
            $this->layoutRendering = true;
            $this->requireFile($this->layout .'.php', CCoreModule::Config('layoutPath'), $params);
            $this->layoutRendering = false;
        } else {
            $this->content($params);
        }
    }

    public function requireFile($file, $alias, $context) {
        $file = CAlias::file($file, $alias);
        if (!file_exists($file)) {
            throw new CException("File {$file} not found.");
        }
        if ($context) {
            extract($context, EXTR_SKIP);
            unset($context);
        }
        require($file);
    }

    /*public function __get($name) {
        if (isset($this->params[$name])) return $this->params[$name];
        throw new CException("Param {$name} not found.");
    }*/

    public function part($name, Array $params = null) {
        $this->requireFile($name . '.php', $this->layoutRendering ? CCoreModule::Config('layoutPath') : CCoreModule::Config('viewPath'), $params);
    }

    public function content() {
        $this->requireFile(CAlias::path(array($this->id, $this->view_id)) . '.php', CCoreModule::Config('viewPath'), $this->viewParams);
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