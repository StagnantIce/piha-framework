<?php

require_once (__DIR__ . '/../../index.php');

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