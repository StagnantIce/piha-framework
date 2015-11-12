<?php

require (__DIR__ . '/../index.php');
require (__DIR__ . '/models/CUserModel.php');
require (__DIR__ . '/models/CGroupModel.php');
require (__DIR__ . '/models/CUserGroupModel.php');

class BaseTest extends PHPUnit_Framework_TestCase {

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