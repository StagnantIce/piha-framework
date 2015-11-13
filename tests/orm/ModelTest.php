<?php

require_once (__DIR__ . '/BaseTest.php');

use piha\modules\orm\classes\CDataObject;
use piha\modules\orm\classes\CQuery;

class ModelTest extends BaseTest {

    public function testModel() {

        $id = 999999999;
        $class = 'piha\modules\user\models\CGroupModel';
        //echo "START\n";
        $class::Delete($id);
        $name = 'unique name';
        $code = 'unique code';
        $class::Insert(array('ID' => $id.'1', 'NAME' => $name.'1', 'CODE' => $code.'1'));
        $data = array('ID' => $id, 'NAME' => $name, 'CODE' => $code);

        //echo 'static and not static methods';
        foreach(array('Code', 'Id', 'Name') as $key) {
            $group = new $class($data);
            $method = 'Get'.$key;
            $this->assertNotEquals($class::$method(), $group->$method());
        }

        //echo 'save as object';
        $group = new $class($data);
        $group->Save();

        //echo 'load as array';
        $group = $class::Get($id);
        $this->assertEquals($name, $group->name);
        $this->assertEquals($code, $group->code);
        $this->assertEquals($id, $group->id);

        //echo 'remove as object';
        $group = new $class(array('ID' => $id));
        $group->Remove();

        //echo 'check as array';
        $this->assertNull($class::Get($id));

        //echo 'save as array';
        $class::Insert($data);

        //echo 'load as object';
        $group = new $class(array('ID' => $id));
        $group->Load();
        foreach(array('Code', 'Id', 'Name') as $key) {
            $get = 'Get'.$key;
            $set = 'Set'.$key;
            $this->assertEquals($group->$get(), $data[strtoupper($key)]);
            $group->$set(null);
            $this->assertEquals($group->$get(), null);
            $group->$set('123');
            $this->assertEquals($group->$get(), '123');
        }

        //echo 'remove as array';
        $class::Delete($id);

        //echo 'check as object';
        $group = new $class(array('ID' => $id));
        $this->assertFalse($group->Load());
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