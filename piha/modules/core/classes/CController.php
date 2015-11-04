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
    public $view = null;
    private $params = null;
    public $layout = '';

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
    public function render($view_id = null, Array $context = null) {
        $this->getView()->setViewFile($view_id, $this->id, $this->action_id);
        $this->getView()->setViewContext($context);
        if ($this->layout) {
            $this->getView()->setFile('/' . $this->layout);
            $this->getView()->setContext($context);
            $this->renderView();
        } else {
            $this->content();
        }
    }

    public function getView() {
        if ($this->view === null) {
            $this->view = new CView();
        }
        return $this->view;
    }

    public function renderView() {
        $fileName = $this->getView()->getFile();
        $context = $this->getView()->getContext();
        if (!file_exists($fileName)) {
            throw new CException("File {$fileName} not found.");
        }
        if ($context) {
            extract($context, EXTR_OVERWRITE);
        }
        unset($context);
        require($fileName);
    }

    public function part($name, Array $context = null) {
        $this->getView()->setFile($name);
        $this->getView()->setContext($context);
        $this->renderView();
    }

    public function content() {
        $this->getView()->setFile($this->getView()->getViewFile());
        $this->getView()->setContext($this->getView()->getViewContext());
        $this->renderView();
    }

    /**
      * Выполнить перенаправление по URL
      * @param string $url url для перенаправления
      * @return null
      */
    public function redirect($url = '') {
        $url = $this->url($url);
        Header("Location: $url");
        exit();
    }

}