<?php

require_once (__DIR__ . '/BaseTest.php');


class MigrationTest extends PHPUnit_Framework_TestCase {

	public function testAlert() {
		CUserModel::schema()->createTable();
		CGroupModel::schema()->createTable();
		CUserGroupModel::schema()->createTable(true);
		CUserModel::schema()->dropColumn('LOGIN');
		CUserModel::schema()->addColumn('LOGIN');
		//CUserModel::schema()->renameColumn('LOGIN', 'LOGIN2');
		//CUserModel::schema()->renameColumn('LOGIN2', 'LOGIN');
		CUserModel::schema()->alterColumn('LOGIN', 'int');
		CUserGroupModel::schema()->dropTable();
		CGroupModel::schema()->dropTable();
		CUserModel::schema()->dropTable();
	}
}