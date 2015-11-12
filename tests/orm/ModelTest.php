<?php

require (__DIR__ . '/BaseTest.php');

use piha\modules\orm\classes\CDataObject;

class ModelTest extends BaseTest {

    public function testModel() {

        $id = 999999999;
        $class = 'CGroupModel';
        //echo "START\n";
        $class::Delete($id);
        $name = 'unique name';
        $code = 'unique code';
        $data = array('ID' => $id, 'NAME' => $name, 'CODE' => $code);

        //echo 'static and not static methods';
        foreach(array('Code', 'ID', 'Name') as $key) {
            $currency = new $class($data);
            $method = 'Get'.$key;
            print_r($currency);
            print_r($data);
            $this->assertNotEquals($class::$method(), $currency->$method());
        }

        //echo 'save as object';
        $currency = new $class($data);
        $currency->Save();

        //echo 'is array';
        $this->assertTrue(CDataObject::is_array($currency));

        //echo 'load as array';
        $currency = $class::Get($id);
        $this->assertTrue(is_array($currency));
        $this->assertEquals($name, $currency['NAME']);
        $this->assertEquals($code, $currency['CODE']);
        $this->assertEquals($id, $currency['ID']);

        //echo 'remove as object';
        $currency = new $class(array('ID' => $id));
        $currency->Erase();

        //echo 'check as array';
        $this->assertFalse($class::Get($id));

        //echo 'save as array';
        $class::Insert($data);

        //echo 'load as object';
        $currency = new $class(array('ID' => $id));
        $currency->Load();
        foreach(array('Code', 'ID', 'Name') as $key) {
            $get = 'Get'.$key;
            $set = 'Set'.$key;
            $this->assertEquals($currency->$get(), $data[strtoupper($key)]);
            $currency->$set(null);
            $this->assertEquals($currency->$get(), null);
            $currency->$set('123');
            $this->assertEquals($currency->$get(), '123');
        }

        //echo 'remove as array';
        $class::Delete($id);

        //echo 'check as object';
        $currency = new $class(array('ID' => $id));
        $this->assertFalse($currency->Load());
    }
    /*
    public function testObject() {
        // create object
        $obj = new CDataObject();

        // check properties
        $this->assertEquals($obj->id, null);
        $this->assertEquals($obj->code, null);
        $this->assertEquals($obj->name, null);
        $this->assertEquals($obj->oneTime, null);

        // check getters
        $this->assertEquals($obj->GetID(), null);
        $this->assertEquals($obj->GetCode(), null);
        $this->assertEquals($obj->GetName(), null);
        $this->assertEquals($obj->GetOneTime(), null);

        // check init from array
        $obj->fromArray(array('ID' => 1));
        $this->assertEquals($obj->id, 1);
        $this->assertEquals($obj->code, null);
        $this->assertEquals($obj->name, null);
        $this->assertEquals($obj->one_time, null);

        //check setters
        $obj->setId(2);
        $this->assertEquals($obj->id, 2);
        $this->assertEquals($obj->GetID(), 2);

        $obj->setOneTime('Y');
        $this->assertEquals($obj->oneTime, 'Y');
        $this->assertEquals($obj->GetOneTime(), 'Y');
    }*/

}