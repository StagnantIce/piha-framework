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
        $group->id = $id;

        $class::Update(array('CODE' => 'code123'), $id);
        $group->Load();
        $this->assertEquals($group->code, 'code123');

        $class::Set($id, array('CODE' => 'code1234'));
        $group->Load();
        $this->assertEquals($group->code, 'code1234');

        $group->id = 555;
        $this->assertEquals($group->Save(), 555);
        $group->code = 'tttt';
        $group->Save();
        $this->assertEquals($class::GetCode(555), 'tttt');
        //echo 'remove as array';
        $class::Delete($id);

        //echo 'check as object';
        $group = new $class(array('ID' => $id));

        $this->assertEquals($class::tableName(), '{{group}}');
        $this->assertEquals($class::label(), 'Группы');
    }
   /**
     * @expectedException piha\CException
     */
    public function testRemove()
    {
        $class = 'piha\modules\user\models\CGroupModel';
        $group = new $class();
        $group->Remove();
    }
   /**
     * @expectedException piha\CException
     */
    public function testLoad1()
    {
        $class = 'piha\modules\user\models\CGroupModel';
        $group = new $class(array('ID' => '123'));
        $group->Load();
    }
   /**
     * @expectedException piha\CException
     */
    public function testLoad2()
    {
        $class = 'piha\modules\user\models\CGroupModel';
        $group = new $class();
        $group->Load();
    }

    public function testStatic()
    {
        $class = 'piha\modules\user\models\CGroupModel';
        $data1 = array('ID' => 2, 'CODE' => 'code2', 'NAME' => 'name2');
        $class::Insert($data1);
        $data2 = array('ID' => 3, 'CODE' => 'code3', 'NAME' => 'name3');
        $class::Insert($data2);
        $this->assertEquals($class::GetCodeID('code2'), 2);
        $this->assertEquals($class::GetCodeID('code1111'), false);
        $this->assertEquals($class::GetCodeName('code2'), 'name2');
        $this->assertEquals($class::GetCodeName('code2222'), false);
        $this->assertEquals($class::GetByName('name2'), new $class($data1));
        $this->assertEquals($class::GetByCode('code3'), new $class($data2));

        $this->assertEquals($class::GetArrayCode(2), array(2 => 'code2'));
        $this->assertEquals($class::GetArray(), array(2 => $data1, 3 => $data2));
        $this->assertEquals($class::GetArrayName(3), array(3 => 'name3'));
        $this->assertEquals($class::GetIDs(), array(2,3));

        $data3 = array('ID' => 4, 'CODE' => 'code4', 'NAME' => 'name4');
        $this->assertEquals($class::GetOrCreate($data3), new $class($data3));
        $this->assertEquals($class::GetOrCreate($data3), new $class($data3));
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