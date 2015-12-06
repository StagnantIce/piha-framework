<?php

/**
* CAdminController
* класс для организации админки
*
* @author Alexeew Artemiy <tria-aa@mail.ru>
* @package piha
*/
namespace piha\modules\user\classes;
use piha\CException;
use piha\modules\user\CUserModule;
use piha\modules\core\classes\CController;
use piha\modules\orm\classes\CListModel;
use piha\modules\bootstrap3\widgets\CForm;


abstract class AAdminController extends CController {

	abstract function modelClass();

	public function getViewPath() {
		return __DIR__ . '/../';
	}

	public function beforeAction() {
		if (!\Piha::user()->hasPermission('admin')) {
			if ($authPage = CUserModule::Config('authPage')) {
				$this->redirect($authPage);
			} else {
				die("No permission");
			}
		}
	}

	public function actionIndex() {
		$class = $this->modelClass();
    	$list = new CListModel();
    	$list->setQuery($class::q());
        $this->render('admin/index', array('list' => $list));
	}

	public function actionCreate() {
		$class = $this->modelClass();
		$model = new $class();
		$form = CForm::post(array('model' => $model));
		if ($form->isSubmit() && $form->isValid()) {
			if ($model->save()) {
				$this->redirect('index');
			}
		}
    	$this->render('admin/create', array('model' => $model, 'form' => $form));
	}

    public function actionDelete($id) {
    	$class = $this->modelClass();
        if ($model = $class::Get($id)) {
        	$model->remove();
        }
        $this->redirect('index');
    }

    public function actionUpdate($id) {
    	$class = $this->modelClass();
    	$model = $class::Get($id);
    	$form = CForm::post(array('model' => $model));
		if ($form->isSubmit() && $form->isValid()) {
			if ($model->save()) {
				$this->redirect('index');
			}
		}
    	$this->render('admin/update', array('model' => $model, 'form' => $form));
    }

}