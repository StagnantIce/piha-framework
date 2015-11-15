<?php

require_once (__DIR__ . '/BaseTest.php');

use piha\modules\orm\classes\CDataObject;
use piha\modules\orm\classes\CQuery;


class ModelTest extends BaseTest {

    public function testModel() {

        $id = 999999999;
        //echo "START\n";
        CGroupModel::Delete($id);
        $name = 'unique name';
        $code = 'unique code';
        CGroupModel::Insert(array('ID' => $id.'1', 'NAME' => $name.'1', 'CODE' => $code.'1'));
        $data = array('ID' => $id, 'NAME' => $name, 'CODE' => $code);

        //echo 'static and not static methods';
        foreach(array('Code', 'Id', 'Name') as $key) {
            $group = new CGroupModel($data);
            $method = 'Get'.$key;
            $this->assertNotEquals(CGroupModel::$method(), $group->$method());
        }

        //echo 'save as object';
        $group = new CGroupModel($data);
        $group->Save();

        //echo 'load as array';
        $group = CGroupModel::Get($id);
        $this->assertEquals($name, $group->name);
        $this->assertEquals($code, $group->code);
        $this->assertEquals($id, $group->id);

        //echo 'remove as object';
        $group = new CGroupModel(array('ID' => $id));
        $group->Remove();

        //echo 'check as array';
        $this->assertNull(CGroupModel::Get($id));

        //echo 'save as array';
        CGroupModel::Insert($data);

        //echo 'load as object';
        $group = new CGroupModel(array('ID' => $id));
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

        CGroupModel::Update(array('CODE' => 'code123'), $id);
        $group->Load();
        $this->assertEquals($group->code, 'code123');

        CGroupModel::Set($id, array('CODE' => 'code1234'));
        $group->Load();
        $this->assertEquals($group->code, 'code1234');

        $group->id = 555;
        $this->assertEquals($group->Save(), 555);
        $group->code = 'tttt';
        $group->Save();
        $this->assertEquals(CGroupModel::GetCode(555), 'tttt');
        //echo 'remove as array';
        CGroupModel::Delete($id);

        //echo 'check as object';
        $group = new CGroupModel(array('ID' => $id));

        $this->assertEquals(CGroupModel::tableName(), '`piha_group`');
        $this->assertEquals(CGroupModel::label(), 'Группы');
    }
   /**
     * @expectedException piha\CException
     */
    public function testRemove()
    {
        $group = new CGroupModel();
        $group->Remove();
    }
   /**
     * @expectedException piha\CException
     */
    public function testLoad1()
    {
        $group = new CGroupModel(array('ID' => '123'));
        $group->Load();
    }
   /**
     * @expectedException piha\CException
     */
    public function testLoad2()
    {
        $group = new CGroupModel();
        $group->Load();
    }

    public function testStatic()
    {
        $data1 = array('ID' => 2, 'CODE' => 'code2', 'NAME' => 'name2');
        CGroupModel::Insert($data1);
        $data2 = array('ID' => 3, 'CODE' => 'code3', 'NAME' => 'name3');
        CGroupModel::Insert($data2);
        $this->assertEquals(CGroupModel::GetCodeID('code2'), 2);
        $this->assertEquals(CGroupModel::GetCodeID('code1111'), false);
        $this->assertEquals(CGroupModel::GetCodeName('code2'), 'name2');
        $this->assertEquals(CGroupModel::GetCodeName('code2222'), false);
        $this->assertEquals(CGroupModel::GetByName('name2'), new CGroupModel($data1));
        $this->assertEquals(CGroupModel::GetByCode('code3'), new CGroupModel($data2));

        $this->assertEquals(CGroupModel::GetArrayCode(2), array(2 => 'code2'));
        $this->assertEquals(CGroupModel::GetArray(), array(2 => $data1, 3 => $data2));
        $this->assertEquals(CGroupModel::GetArrayName(3), array(3 => 'name3'));
        $this->assertEquals(CGroupModel::GetIDs(), array(2,3));

        $data3 = array('ID' => 4, 'CODE' => 'code4', 'NAME' => 'name4');
        $this->assertEquals(CGroupModel::GetOrCreate($data3), new CGroupModel($data3));
        $this->assertEquals(CGroupModel::GetOrCreate($data3), new CGroupModel($data3));
    }

    public function testRelations() {
        CUserModel::Insert(array("ID" => 1, 'LOGIN' => 'test1'));
        CUserModel::Insert(array("ID" => 2, 'LOGIN' => 'test2'));
        CGroupModel::Insert(array('ID' => 1, 'NAME' => 'group1'));
        CGroupModel::Insert(array('ID' => 2, 'NAME' => 'group2'));
        CUserGroupModel::Insert(array('ID' => 1, 'USER_ID' => 1, 'GROUP_ID' => 1));
        CUserGroupModel::Insert(array('ID' => 2, 'USER_ID' => 1, 'GROUP_ID' => 2));
        CUserGroupModel::Insert(array('ID' => 3, 'USER_ID' => 2, 'GROUP_ID' => 1));
        $user = CUserModel::Get(1);
        $this->assertEquals($user->groups, CGroupModel::GetAll());
        $userGroup = CUserGroupModel::Get(2);
        $this->assertEquals($userGroup->group, CGroupModel::Get(2));
        $this->assertEquals($userGroup->group->name, 'group2');
    }
}