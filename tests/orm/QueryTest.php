<?php

require_once (__DIR__ . '/BaseTest.php');

use piha\modules\user\models\CGroupModel;
use piha\modules\user\models\CUserModel;

class CoreTest extends BaseTest {

    public function testSelect() {
    	CUserModel::Insert(array('LOGIN' => 'test1'));
    	CUserModel::Insert(array('LOGIN' => 'test2'));
    	$this->assertEquals(CUserModel::q()->select('LOGIN')->where(array('LOGIN' => 'test1'))->one('LOGIN'), 'test1');
    }
}
