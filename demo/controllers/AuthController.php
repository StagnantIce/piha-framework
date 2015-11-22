<?php

use piha\modules\core\classes\CController;
use piha\modules\bootstrap3\widgets\CForm;
use piha\modules\store\classes\CStore;

class AuthController extends CController {

	public $layout = 'main';
	public function actionLogin() {
		$loginForm = CForm::post(array('model' => new CUserModel()));
		if ($loginForm->isSubmit()) {
			$userModel = $loginForm->getModel();
			if($authModel = $userModel->authorize()) {
				CStore::session()->set('auth', $authModel->id);
				$this->redirect('home/index');
			} else {
				$loginForm->addError('Логин или пароль введены не верно');
			}
		}
		$this->render('login', array('form' => $loginForm));
	}

	public function actionReg() {
		$regForm = CForm::post(array('model' => new CUserModel()));
		if ($regForm->isSubmit()) {
			$userModel = $regForm->getModel();
			if ($userModel->password === $userModel->confirmPassword) {
				if ($authModel = $userModel->registration()) {
					if ($authModel->authorize()) {
						CStore::session()->set('auth', $authModel->id);
						$this->redirect('home/index');
					} else {
						$regForm->addError('Error');
					}
				} else {
					$regForm->addError('Такой пользователь уже зарегистрирован');
				}
			} else {
				$regForm->addError('Пароль и подтверждение пароля не совпадают');
			}
		}
		$this->render('reg', array('form' => $regForm));
	}

	public function actionRecovery() {
		$this->render();
	}
}