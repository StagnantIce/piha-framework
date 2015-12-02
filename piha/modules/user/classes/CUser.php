<?php

namespace piha\modules\user\classes;
use piha\AClass;
use piha\CException;
use piha\modules\user\CUserModule;

class CUser extends AClass {

	private $storage;

	private function getStorage() {
		if (!$this->storage) {
			$storageClass = CUserModule::config('storageClass');
			if (!$storageClass) {
				throw new CException("Config 'storageClass' for user module not defined.");
			}
			$this->storage = new $storageClass('user');
		}
		return $this->storage;
	}

	private function getModelClass() {
		$modelClass = CUserModule::config('modelClass');
		if (!$modelClass) {
			throw new CException("Config 'modelClass' for user module not defined.");
		}
		return $modelClass;
	}

	public function getModel() {
		$class = $this->getModelClass();
		if ($id = $this->getId()) {
			return $class::StaticGet($this->getId());
		}
		return false;
	}

	public function setId($id) {
		if (!$id) {
			throw new CException("Error authorized");
		}
		$this->getStorage()->set('user.id', $id);
	}

	public function getId() {
		return $this->getStorage()->get('user.id');
	}

	public function delId() {
		$this->getStorage()->del('user.id');
	}

	public function hasPermission($name) {
		\Piha::IncludeModule('permission');
		return \Piha::permission()->hasPermission($this->getId(), $name);
	}

	public function assign($name) {
		\Piha::IncludeModule('permission');
		return \Piha::permission()->assign($this->getId(), $name);
	}

	public function hashPassword($password) {
		if (!function_exists('crypt')) {
			throw new CException("Crypt lib not found");
		}
		return crypt($password);
	}

	public function verifyPassword($password, $hash) {
		if (!function_exists('crypt')) {
			throw new CException("Crypt lib not found");
		}
		$ret = crypt($password, $hash);
		if (!is_string($ret) || $this->_strlen($ret) != $this->_strlen($hash) || $this->_strlen($ret) <= 13) {
			return false;
		}
		$status = 0;
		for ($i = 0; $i < $this->_strlen($ret); $i++) {
			$status |= (ord($ret[$i]) ^ ord($hash[$i]));
		}
		return $status === 0;
	}


	private function _strlen($binary_string) {
		if (function_exists('mb_strlen')) {
			return mb_strlen($binary_string, '8bit');
		}
		return strlen($binary_string);
	}
}