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

    /** @var string $id - id контроллера */
    public $id = '';

    /** @var string $action_id - id экшена */
    public $action_id = '';

    /** @var array $params - параметры для передачи в контроллер*/
    private $params = null;

    /** @var string $layout - имя лейаута для вьюшки */
    public $layout = '';

    /** @var string $defaultAction - имя дефолтового экшена */
    protected $defaultAction = 'index';

    /**
      * Создать контроллер
      * @param string $action_id - id экшена
      * @param array $params - параметры для передачи в метод контроллера
      * @return CController
      */
    public function __construct($action_id, Array $params = null) {
        $this->action_id = $action_id ?: $this->defaultAction;
        $this->id = static::GetID();
        $this->params = $params;
    }

    /**
      * Получить название метода контроллера на основании id экшена
      * @param string $action_id - id экшена
      * @return string название метода
      */
    public static function getActionName($action_id) {
        return self::METHOD_NAME . ucfirst($action_id);
    }

    public function setReturn($return) {
        $this->_return = (boolean)$return;
    }

    /**
      * Запустить выполнение экшена
      * @param boolean $return вернуть или вывести
      * @return null
      */
    public function runAction($return = false) {
        $method = $this->getActionName($this->action_id);
        if (method_exists($this, $method)) {
            if ($return) {
                ob_start();
                ob_implicit_flush(false);
            }
            $this->beforeAction($this->action_id);
            call_user_func_array(array($this, $method), $this->params ?: array());
            if ($return) {
                return ob_get_clean();
            }
        } else {
            throw new CException('Bad method call ' . get_called_class().'->'.$method);
        }
    }

    /**
      * Выполнение функции перед экшеном
      * @param string $action id экшена
      * @return null
      */
    public function beforeAction($action_id) {
    }

    /**
      * Возвращает ID контроллера
      * @return string ID контроллера
      */
    public static function GetID() {
        $className = explode('\\', get_called_class());
        return str_replace('Controller', '', lcfirst($className[count($className)-1]));
    }

    /**
      * Возвращает путь для вызова экшена
      * @param string $action_id имя экешна без "action" вначале
      * @return string Путь для вызова экшена
      */
    public function url($route = '', Array $params=null) {
        if (!$route) {
            $route = $this->action_id ?: $this->defaultAction;
        }
        $route = trim($route, '/');
        if (strpos($route, '/') === false) {
            $route = $this->id . '/' . $route;
        }
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
    public function render($renderName = '', Array $context = null, $return = false) {
        $view = new CView($this->getViewId($renderName), $context);
        $view->setMiddleWare($this);
        $result = '';
        if ($this->layout) {
            $context['content'] = $view->render();
            $layoutView = new CView('/' . $this->layout, $context);
            $layoutView->setMiddleWare($this);
            $result = $layoutView->render();
        } else {
            $result = $view->render();
        }
        if ($return) {
            return $result;
        }
        echo $result;
    }

    /**
      * Отрисовать вьюшку относительно текущей позиции рендеринга
      * @param string $renderName имя вьюшки
      * @param array $context контекст для рендеринга
      * @param boolean $return вернуть или вывести
      * @return string
      */
    public function part($renderName = null, Array $context = null, $return = false) {
        $view = new CView($this->getViewId($renderName), $context);
        $view->setMiddleWare($this);
        $view->setPartAlias();
        $result = $view->render();
        if ($return) {
            return $result;
        }
        echo $result;
    }

    /**
      * Вернуть путь до вьюшки на основании контроллера, экшена и имени
      * @param string $renderName имя вьюшки
      * @return string
      */
    public function getViewId($renderName) {
        $view_id = $renderName ?: $this->action_id;
        return strpos($view_id, '/') === false ? CAlias::GetPath(array($this->id, $view_id)) : $view_id;
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