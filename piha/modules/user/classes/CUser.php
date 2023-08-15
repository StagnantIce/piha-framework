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
		return crypt($password, CUserModule::Config('salt', 'piha'));
	}

	public function verifyPassword($password, $hash) {
		return self::hashPassword($password) === $hash;
	}
}
