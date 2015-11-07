<?php

require (__DIR__ . '/../index.php');
require (__DIR__ . '/models/CUserModel.php');
require (__DIR__ . '/models/CGroupModel.php');
require (__DIR__ . '/models/CUserGroupModel.php');

class QueryTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		CUserModel::schema()->createTable();
		CGroupModel::schema()->createTable();
		CUserGroupModel::schema()->createTable(true);
	}

	public function tearDown() {
		CUserGroupModel::schema()->dropTable();
		CUserModel::schema()->dropTable();
		CGroupModel::schema()->dropTable();
	}

    public function testSelect() {
    	CUserModel::Insert(array('NAME' => 'test1'));
    	CUserModel::Insert(array('NAME' => 'test2'));
    	$this->assertEquals(CUserModel::q()->select('NAME')->where(array('NAME' => 'test1'))->one('NAME'), 'test1');
    }
}
