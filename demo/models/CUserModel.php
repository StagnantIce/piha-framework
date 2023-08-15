<?php

use piha\modules\orm\classes\CModel;

class CUserModel extends CModel {

	public $_name = '{{user}}';
	public $_label = 'Пользователи';
	public $confirmPassword = '';

	public function getColumns() {
		return array(
			'ID'        => array('type' => 'pk'),
			'LOGIN'     => array('type' => 'string', 'label' => 'Логин'),
			'PASSWORD'  => array('type' => 'string', 'label' => 'Пароль'),
			'EMAIL'     => array('type' => 'string', 'label' => 'Email'),
			'PHONE'     => array('type' => 'string'),
		);
	}

	public function getRelations() {
		return array(
		    self::TYPE_MANY => array(
		    	'groups' => array('ID', CUserGroupModel::className(), CGroupModel::className())
			)
		);
	}

	public function authorize() {
		$model = self::Get(array('EMAIL' => $this->email));
		if(!$model) {
			$model = self::Get(array('LOGIN' => $this->email));
		}
		if ($model && \Piha::user()->verifyPassword($this->password, $model->password)) {
			return $model;
		}
		return false;
	}

	public function registration() {
		$model = false;
		if ($this->email) {
			$model = CUserModel::Get(array('EMAIL' => $this->email));
		} elseif ($this->login) {
			$model = CUserModel::Get(array('LOGIN' => $this->login));
		}
		if (!$model) {
			$pass = $this->password;
			$this->password = \Piha::user()->hashPassword($this->password);
			if ($this->save()) {
				$this->password = $pass;
				return $this;
			}
			$this->password = $pass;
		}
		return false;
	}
}