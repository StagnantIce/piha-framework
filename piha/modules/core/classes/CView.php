<?php
/**
* CView
* work with view template
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/
namespace piha\modules\core\classes;
use piha\modules\core\CCoreModule;
use piha\CAlias;
use piha\CException;

class CView {

    private $layoutRendering = false;
    private $view_id = '';
    private $context = null;
    private $viewContext = null;
    private $file = '';
    private $viewFile = '';

	public function __contruct() {
        if (CCoreModule::Config('cleanView', true)) {
            unset($_POST, $_GET, $_REQUEST);
        }
	}

    public function getFile() {
    	$file = $this->file;
    	$this->layoutRendering = false;
    	if (strncmp($file,'//',2) === 0) {
    		$file = ltrim($file, '/');
    		$file = CAlias::file($file .'.php', '@webroot');
    	} else if (strncmp($file,'/',1) === 0) {
    		$this->layoutRendering = true;
    		$file = ltrim($file, '/');
    		$file = CAlias::file($file .'.php', CCoreModule::Config('layoutPath'));
    	} else {
    		$file = CAlias::file($file .'.php', CCoreModule::Config('viewPath'));
    	}
    	return $file;
    }

    public function setViewFile($view_id, $controller_id, $action_id) {
        $view_id = $view_id ?: $action_id;
        if (strpos($view_id, '/') === false) {
            $view_id = CAlias::path(array($controller_id, $view_id));
        }
        $this->viewFile = $view_id;
    }

    public function setContext(Array $context = null) {
    	$this->context = $context;
    }

    public function setViewContext(Array $context = null) {
    	$this->viewContext = $context;
    }

    public function getViewContext() {
    	return $this->viewContext;
    }

    public function getViewFile() {
    	return $this->viewFile;
    }

    public function getContext() {
    	return $this->context;
    }

    public function setFile($file) {
    	$this->file = $file;
    }
}