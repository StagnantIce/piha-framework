<?php

require (__DIR__ . '/../index.php');

use piha\modules\user\models\CUserModel;
use piha\modules\user\models\CGroupModel;
use piha\modules\user\models\CUserGroupModel;

abstract class BaseTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		CUserModel::schema()->createTable();
		CGroupModel::schema()->createTable();
		CUserGroupModel::schema()->createTable();
	}

	public function tearDown() {
		CUserGroupModel::schema()->dropTable();
		CUserModel::schema()->dropTable();
		CGroupModel::schema()->dropTable();
	}
}