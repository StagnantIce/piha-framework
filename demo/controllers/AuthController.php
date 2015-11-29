<?php

use piha\modules\core\classes\CController;
use piha\modules\bootstrap3\widgets\CForm;
use piha\modules\store\classes\CStore;

class AuthController extends CController {

	public $layout = 'main';
	public function actionLogin() {
		$loginForm = CForm::post(array('model' => new CUserModel()));
		$loginForm->fieldEmail('EMAIL', array('require'));
		$loginForm->fieldPassword('PASSWORD', array('require'));

		if ($loginForm->isSubmit() && $loginForm->isValid()) {
			$userModel = $loginForm->getModel();
			if($authModel = $userModel->authorize()) {
				\Piha::user()->setId($authModel->id);
				$this->redirect('home/index');
			} else {
				$loginForm->addError('Логин или пароль введены не верно');
			}
		}
		$this->render('login', array('form' => $loginForm));
	}

	public function actionReg() {
		$regForm = CForm::post(array('model' => new CUserModel()));
		$regForm->fieldEmail('EMAIL', array('require'));
		$regForm->fieldPassword('PASSWORD', array('min' => 6, 'max' => 16, 'require'));
		$regForm->fieldPassword('CONFIRM_PASSWORD', array('require'));
		$regForm->fieldText('LOGIN', array('require'));
		if ($regForm->isSubmit() && $regForm->isValid()) {
			$userModel = $regForm->getModel();
			if ($userModel->password === $userModel->confirmPassword) {
				if ($authModel = $userModel->registration()) {
					if ($authModel->authorize()) {
						\Piha::user()->setId($authModel->id);
						$this->redirect('home/index');
					} else {
						$regForm->addError('Ошибка авторизации');
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

	public function actionLogout() {
		\Piha::user()->delId();
		$this->redirect('home/index');
	}

	public function actionRecovery() {
		$this->render();
	}
}