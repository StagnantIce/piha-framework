<?php

require (__DIR__ . '/BaseTest.php');

class CoreTest extends BaseTest {

    public function testSelect() {
    	CUserModel::Insert(array('NAME' => 'test1'));
    	CUserModel::Insert(array('NAME' => 'test2'));
    	$this->assertEquals(CUserModel::q()->select('NAME')->where(array('NAME' => 'test1'))->one('NAME'), 'test1');
    }
}
