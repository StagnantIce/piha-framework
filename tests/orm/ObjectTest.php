<?php

require_once (__DIR__ . '/BaseTest.php');

use piha\modules\orm\classes\CDataObject;
use piha\modules\user\models\CGroupModel;
use piha\modules\user\models\CUserModel;

class ObjectTest extends BaseTest {

   /**
     * @expectedException piha\CException
     */
    public function testName1()
    {
    	$obj = new CDataObject(array('0Name' => 'Name1'));
    }

   /**
     * @expectedException piha\CException
     */
    public function testName2()
    {
    	$obj = new CDataObject(array('Nam1e' => 'Name2'));
    }

   /**
     * @expectedException piha\CException
     */
    public function testName3()
    {
    	$obj = new CDataObject(array('NmВам' => 'Name2'));
    }

   /**
     * @expectedException piha\CException
     */
    public function testName5()
    {
    	$obj = new CDataObject(array('Name1' => 'Name1'));
    }

   /**
     * @expectedException piha\CException
     */
    public function testProperty()
    {
    	$obj = new CDataObject(array('name1' => 'Name1'));
    	$obj->name2 = '2';
    }

    public function testName4()
    {
    	$obj = new CDataObject(array('name1' => 'Name1'));
    	$this->assertEquals($obj->name1, 'Name1');
    }

    public function testGetSet() {
    	$obj = new CDataObject(array('na1' => 'Name1'));
    	$this->assertEquals($obj->na1, 'Name1');
    	$this->assertEquals($obj->getNa1(), 'Name1');
    	$obj->setNa1('Name3');
    	$this->assertEquals($obj->getNa1(), 'Name3');
    }

    public function testArray() {
    	$obj = new CDataObject(array('na1' => 'Name1'));
    	$obj['na1'] = 'Name4';
    	$this->assertEquals($obj['na1'], 'Name4');
    	$this->assertEquals(isset($obj['na1']), true);
    	foreach($obj as $name => $value) {
    		$this->assertEquals($name, 'na1');
    		$this->assertEquals($value, 'Name4');
    	}

    	unset($obj['na1']);
    	$this->assertEquals(isset($obj['na1']), false);

    	$data = array('na1' => 'Name1', 'na2' => 'Name2');
    	$obj = new CDataObject($data);
    	$this->assertEquals($obj->toArray(), $data);
    	$this->assertEquals($obj->toArray(array('na1')), array('na1' => 'Name1'));

    	$obj->fromArray(array('na2' => 'Name3'));
    	$this->assertEquals($obj->na2, 'Name3');
    	$this->assertEquals(isset($obj['na1']), false);
    }

    public function testEvent() {
    	// на событие вызваем className
    	CDataObject::on('event', CDataObject::className('className'));

    	// срабатвает событие, передаем ему параметр className
    	$result = CDataObject::trigger('event', 'className');
    	$this->assertEquals(CDataObject::className('className'), current($result));
    }
}
