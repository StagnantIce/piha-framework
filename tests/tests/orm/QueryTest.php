<?php

require_once (__DIR__ . '/BaseTest.php');

use piha\modules\orm\classes\CMigration;
use piha\modules\orm\classes\CQuery;


class QueryTest extends BaseTest {

    public function testWhereConditions() {
        // simple condition
        $code = 'alala';
        CGroupModel::Delete(array('CODE' => $code));

        $id = CGroupModel::Insert(array('CODE' => $code));
        // 1
        $rs = CGroupModel::q()->where(array(
                'ID>' => $id - 1
            )
        );

        $row = $rs->one();
        $this->assertEquals($id, $row['ID']);
        // 2
        $rs = CGroupModel::q()->where(array(
                'CODE' => $code,
                'ID>=' => $id
            )
        );

        $row = $rs->one();
        $this->assertEquals($id, $row['ID']);
        // 3
        $rs = CGroupModel::q()->where(array(
                'CODE' => $code . '2',
                'ID<=' => $id
            )
        );

        $row = $rs->one();
        $this->assertEquals(false, $row);

        // 4
        $rs = CGroupModel::q()->where(array(
                'CODE' => $code,
                'ID' => $id,
                'NAME' => null
            )
        );

        $row = $rs->one();
        $this->assertEquals($id, $row['ID']);

        // complex condition
        $code2 = 'ababa';
        CGroupModel::Delete(array('CODE' => $code2));
        $id2 = CGroupModel::Insert(array('CODE' => $code2));

        $rs = CGroupModel::q()->where(array(
                'CODE' =>  $code2
            )
        );

        $row = $rs->one();
        // 1 ID = 1 OR ID IN (2, 999999)
        $rs = CGroupModel::q()->where(array(
                'OR' => array(
                    array(
                        'ID' => $id
                    ),
                    array(
                        'ID' => array($id2, 9999999)
                    )
                )
            ))->order(array('ID' => 'ASC'));

        $rows = $rs->all();
        $this->assertEquals($id, $rows[0]['ID']);
        $this->assertEquals($id2, $rows[1]['ID']);

        // 2 (CODE = 'alala' AND ID = 1) OR (CODE = 'ababa' AND ID = 2)
        $rs = CGroupModel::q()->where(array(
                'OR' => array(
                    array(
                        'CODE' => $code,
                        'ID' => $id
                    ),
                    array(
                        'CODE' => $code2,
                        'ID' => $id2
                    )
                )
            )
        )->order(array('ID' => 'ASC'));

        $rows = $rs->all();
        $this->assertEquals($id, $rows[0]['ID']);
        $this->assertEquals($id2, $rows[1]['ID']);

        // 3 CODE = 'alala' OR ID = 1 OR NAME IS NULL
        $rs = CGroupModel::q()->where(array(
                'OR' => array(
                    'CODE' => $code,
                    'ID' => $id2
                )
            )
        )->order(array('ID' => 'ASC'));

        $rows = $rs->all();
        $this->assertEquals($id, $rows[0]['ID']);
        $this->assertEquals($id2, $rows[1]['ID']);

        // 4 (CODE = 'alala' OR ID = 1) AND (CODE = 'ababa' OR ID = 2)
        $rs = CGroupModel::q()->where(array(
                array(
                    'OR' => array(
                        'CODE' => $code2 . '!!!',
                        'ID' => $id
                    )
                ),
                array(
                    'OR' => array(
                        'CODE' => $code,
                        'ID' => $id2 * 1000
                    )
                )
            )
        )->order(array('ID' => 'ASC'));

        $rows = $rs->all();
        $this->assertEquals($id, $rows[0]['ID']);
        $this->assertEquals(1, count($rows));

        /* 5
                (
                        (`CODE`="ababa!!!" OR `ID`=1)
                    AND
                        (`CODE`="alala" OR `ID`=1000)
                )
                    OR
                (
                        (`CODE`="ababa!!!" OR `ID`=2)
                    AND
                        (`CODE`="alala" OR `ID`=2000)
                )
        */

        $rs = CGroupModel::q()->where(array(
                'OR' => array(
                    array(
                        array(
                            'OR' => array(
                                'CODE' => $code2 . '!!!',
                                'ID' => $id
                            )
                        ),
                        array(
                            'OR' => array(
                                'CODE' => $code,
                                'ID' => $id2 * 1000
                            )
                        )
                    ),
                    array(
                        array(
                            'OR' => array(
                                'CODE' => $code2 . '!!!',
                                'ID' => $id2
                            )
                        ),
                        array(
                            'OR' => array(
                                'CODE' => $code,
                                'ID' => $id * 1000
                            )
                        )
                    )
                )
            )
        )->order(array('ID' => 'ASC'));

        $rows = $rs->all();
        $this->assertEquals($id, $rows[0]['ID']);
        $this->assertEquals(1, count($rows));
    }

    public function testQueryObject() {
        $code = 'alala';

        CGroupModel::q()->delete()
                   ->where(array('CODE' => $code))
                   ->execute();

        $id = CGroupModel::q()->insert(array('CODE' => $code));

        $row = CGroupModel::q()->select('ID')->where(array('ID>' => $id - 1))->one();

        $this->assertEquals($id, $row['ID']);

        $id = CGroupModel::q()->select('ID')->where(array('ID>' => $id - 1))->one('ID');

        $this->assertEquals($id, $id);

        CGroupModel::q()->remove( $id );
    }
/*
    public function testSelect() {
        $data = CGroupModel::q()
            ->select(array('DISTINCT ID'))->one();
        $data = CGroupModel::q()
            ->select('SUM(ID)')->one();

        $data = CGroupModel::q()
            ->one();
    }
*/
    public function testJoin() {

        $num = 5;

        for($i = 0; $i < $num; $i++) {
        	$group_id = CGroupModel::Insert(array('NAME' => 'test', 'CODE' => 'test'));
        	$user_id = CUserModel::Insert(array('LOGIN' => 'login2', 'ID' => $i + 10));
            CUserGroupModel::Insert(array('GROUP_ID' => $group_id, 'USER_ID' => $user_id));
        }

        CUserGroupModel::q()->left(array(CUserModel::className() => array('#.ID' => '*.USER_ID')))->one();

        $data = CUserGroupModel::q()
            ->select(array(/*'*.ID' => 'MYID', */'ID', 't1.ID' => 'ID1', 't2.ID' => 'ID2'))
            ->join('GROUP_ID')
            ->join(array('GROUP_ID' => 't1'))
            ->left(array('t3' => 'GROUP_ID'))
            ->left(array(CUserModel::className() => array('#.ID' => '*.USER_ID')))
            ->inner(array('GROUP_ID' => 't4', 'USER_ID' => 't5'))
            ->right(array('t2' => CUserModel::tableName()), array('#.ID' => '*.USER_ID'))
            ->where(array('t1.ID<>' => CQuery::formula('t2.ID')))
            ->limit($num)
            ->all();

        $this->assertEquals(count($data), $num);
        $this->assertNotEquals($data[0]['ID1'], $data[0]['ID2']);
    }

    public function testFrom() {
        $query = new CQuery();
        $row = $query->select(array('t.ID' => 'USER_GROUP_ID', 'g.ID' => 'group_ID'))
            ->from(array('t' => CUserGroupModel::tableName(), 'g' => CGroupModel::tableName()))
            ->where(array('t.GROUP_ID' => 'g.ID'))
            ->one();
    }

    public function testGroup() {

        $model1 = CGroupModel::GetOrCreate(array('NAME' => 'test1', 'CODE' => 'test11'));
        $model2 = CGroupModel::GetOrCreate(array('NAME' => 'test1', 'CODE' => 'test22'));
        $model3 = CGroupModel::GetOrCreate(array('NAME' => 'test3', 'CODE' => 'test33'));

        list($id1, $id2, $id3) = array($model1->id, $model2->id, $model3->id);

        $model4 = CGroupModel::GetOrCreate(array('NAME' => 'test1', 'CODE' => 'test22'));
        $id4 = $model4->id;

        //flat = true
        $arr = CGroupModel::q()->where(array('ID' => array($id1, $id2, $id3)))->all('ID', array('NAME', 'CODE'), true);
        $this->assertEquals($arr, array(
            'test1' => array('test11' => $id1, 'test22' => $id2),
            'test3' => array('test33' => $id3)
        ));

        $this->assertEquals(CGroupModel::q()->where( $id1 )->one('CODE'), 'test11');
        $this->assertEquals(CGroupModel::q()->where( $id1 )->one(array('CODE', 'NAME')), array('CODE' => 'test11', 'NAME' => 'test1'));
        $this->assertEquals(CGroupModel::q()->where( $id1 )->one('CODE', 'NAME'), array('test1' => 'test11'));

        $this->assertEquals(CGroupModel::q()->where(array('ID' => array($id1, $id2, $id3)))->all('NAME'), array('test1', 'test1', 'test3'));
        $arr = CGroupModel::q()->where(array('ID' => array( $id1,  $id2,  $id3)))->all('CODE', 'NAME');
        $this->assertEquals($arr, array('test1' => array('test11', 'test22'), 'test3' => 'test33'));

        $id4 = CGroupModel::Insert(array('NAME' => 'test1', 'CODE' => 'test22'));
        $arr = CGroupModel::q()->where(array('ID' => array($id1, $id2, $id3, $id4)))->all('ID', array('NAME', 'CODE'));

        $this->assertEquals($arr, array(
            'test1' => array('test11' => $id1, 'test22' => array($id2, $id4)),
            'test3' => array('test33' => $id3)
        ));

        //flat = false
        $arr = CGroupModel::q()->where(array('ID' => array($id1, $id2, $id3, $id4)))->all('ID', array('NAME', 'CODE'), false);

        $this->assertEquals($arr, array(
            'test1' => array('test11' => array($id1), 'test22' => array($id2, $id4)),
            'test3' => array('test33' => array($id3))
        ));

        $arr = CGroupModel::q()->where(array('ID' => array($id1, $id2, $id3, $id4)))->all(array('ID', 'NAME'), array('NAME', 'CODE'));

        $this->assertEquals($arr, array(
            'test1' => array(
                'test11' => array('ID' => $id1, 'NAME' => 'test1'),
                'test22' => array(
                    array('ID' => $id2, 'NAME' => 'test1'),
                    array('ID' => $id4, 'NAME' => 'test1')
                ),
            ),
            'test3' => array('test33' => array('ID' => $id3, 'NAME' => 'test3'))
        ));

        $arr = CGroupModel::q()->where(array('ID' => array($id1, $id2, $id3, $id4)))->all(array('ID', 'NAME'), 'CODE');

        $this->assertEquals($arr, array(
            'test11' => array('ID' => $id1, 'NAME' => 'test1'),
            'test22' => array(
                array('ID' => $id2, 'NAME' => 'test1'),
                array('ID' => $id4, 'NAME' => 'test1')
            ),
            'test33' => array('ID' => $id3, 'NAME' => 'test3'))
        );

        $arr = CGroupModel::q()->where(array('ID' => array($id1, $id2, $id3, $id4)))->all(false, 'CODE');
    	$this->assertEquals($arr, CGroupModel::GetArray('CODE'));
    }


    public function testPrepare() {
        $q = new CMigration('test_prepare', array(
            'ID' => 'pk',
            'STRING' => 'string',
            'TEXT' => 'text',
            'INT18' => 'int18',
            'INT' => 'int',
            'FILE' => 'file',
            'IMAGE' => 'image',
            'INTEGER' => 'integer',
            'FLOAT' => 'float',
            'DECIMAL' =>'decimal',
            'DATETIME' => 'datetime',
            'TIMESTAMP' =>'timestamp',
            'TIME' =>'time',
            'DATE' => 'date',
            'BINARY' => 'binary',
            'BOOLEAN' => 'boolean',
            'MONEY' => 'money',
            'CHAR' => 'char'
        ));

        $q->createTable();
        $q = new CQuery('test_prepare');

        $d = date('Y-m-d H:i:s');
        $time = date('H:i:s');
        $data = array(
                'ID' => '8',
                'STRING' => 222,
                'TEXT' => CQuery::formula('CONCAT("text", "new", ID)'),
                'INT18' => false,
                'INT' => 'NULL',
                'FILE' => '123123',
                'IMAGE' => true,
                'INTEGER' => 88,
                'FLOAT' => 12.3,
                'DECIMAL' => '23.4',
                'DATETIME' => '2012-01-01 00:00:00',
                'TIMESTAMP' => $d,
                'TIME' => $time,
                'DATE' => '2012-01-01',
                'BINARY' => 'NULL',
                'BOOLEAN' => false,
                'MONEY' => '123.33',
                'CHAR' => 'Y'
            );

        $newdata = array(
                'ID' => '8',
                'STRING' => '222',
                'TEXT' => "textnew8",
                'INT18' => '0',
                'INT' => null,
                'FILE' => '123123',
                'IMAGE' => '1',
                'INTEGER' => '88',
                'FLOAT' => '12.3',
                'DECIMAL' => '23',
                'DATETIME' => '2012-01-01 00:00:00',
                'TIMESTAMP' => $d,
                'TIME' => $time,
                'DATE' => '2012-01-01',
                'BINARY' => NULL,
                'BOOLEAN' => '0',
                'MONEY' => '123.3300',
                'CHAR' => 'Y'
            );

        $q->insert($data);
        $data =  $q->one();
        $q->delete()->execute();
        $this->assertEquals( $data, $newdata);
    }


    public function testRelations() {
        $group = CGroupModel::GetOrCreate(array('NAME' => 'testRelations', 'CODE' => 'test11'));
        $group2 = CGroupModel::GetOrCreate(array('NAME' => 'testRelations2', 'CODE' => 'test13'));
        $user = CUserModel::GetOrCreate(array('LOGIN' => 'test5'));
        $user2 = CUserModel::GetOrCreate(array('LOGIN' => 'test6'));

        $data = array('GROUP_ID' => $group->id, 'USER_ID' => $user->id);
        $data2 = array('GROUP_ID' => $group2->id, 'USER_ID' => $user2->id);

        CUserGroupModel::GetOrCreate($data);
        CUserGroupModel::GetOrCreate($data2);
        $groupName = CUserModel::q()
            ->select(array('groups.NAME'))
            ->left('groups')
            ->one('NAME');

        $this->assertEquals( $groupName, 'testRelations');

        $userGroup = CUserGroupModel::q()
            ->select(array('user.LOGIN', 'group.CODE'))
            ->left(array('user', 'group'))
            ->one();

        $this->assertEquals( $userGroup, array('LOGIN' => 'test5', 'CODE' => 'test11'));
    }


    public function testSubquery() {
        $group1 = CGroupModel::GetOrCreate(array('NAME' => 'testSubquery', 'CODE' => 'test11'));
        $group2 = CGroupModel::GetOrCreate(array('NAME' => 'testSubquery2', 'CODE' => 'test12'));
        $group3 = CGroupModel::GetOrCreate(array('NAME' => 'testSubquery3', 'CODE' => 'test13'));
        $user = CUserModel::GetOrCreate(array('LOGIN' => 'test5'));

        $data = array('GROUP_ID' => $group3->id, 'USER_ID' => $user->id);
        CUserGroupModel::Insert($data);

        $data = array('GROUP_ID' => $group1->id, 'USER_ID' => $user->id);
        CUserGroupModel::Insert($data);

        $data = array('GROUP_ID' => $group3->id, 'USER_ID' => $user->id);
        CUserGroupModel::Insert($data);

        $subquery1 = CUserGroupModel::q()
                ->where( array('USER_ID' => $user->id))
                ->order(array('ID' => 'ASC'));

        $subquery2 = CUserGroupModel::q()
                ->where( array('USER_ID' => $user->id))
                ->order(array('ID' => 'DESC'));

        $this->assertNotEquals(
            CUserGroupModel::q()->select('s.*')->from(array('s' => $subquery1))->group('GROUP_ID')->all(),
            CUserGroupModel::q()->select('s.*')->from(array('s' => $subquery2))->group('GROUP_ID')->all()
        );
    }

    public function testObject() {
        $group = CGroupModel::GetOrCreate(array('NAME' => 'test221', 'CODE' => 'test1441'));
        $obj = CGroupModel::q()->object($group->id);
        $this->assertEquals($obj->name, $group->name);
        $this->assertEquals($obj->code, $group->code);
    }
/*
    public function testWhereFormula() {
        $group = CGroupModel::GetOrCreate(array('NAME' => 'testWhereFormula', 'CODE' => 'test11'));
        $user = CUserModel::GetOrCreate(array('LOGIN' => 'test5'));

        $data = array('GROUP_ID' => $group->id, 'USER_ID' => $user->id, 'VALUE' => 5);
        $gr2 = CUserGroupModel::Insert($data);

        $data = array('GROUP_ID' => $group->id, 'USER_ID' => $user->id, 'VALUE' => 1);
        $gr1 = CUserGroupModel::Insert($data);

        $all = CUserGroupModel::q()->
            where(array('>=ROUND(*.VALUE/*.VALUE) + *.VALUE' => 3, 'USER_ID' => $user->id))->all('ID');

        $this->assertEquals($all, array($gr2->id));
    }
*/
    public function testNull() {
        $group = CGroupModel::GetOrCreate(array('NAME' => 'testNull', 'CODE' => null));
        $this->assertEquals($group->code, null);
        $this->assertEquals('testNull', CGroupModel::q()->where(array('CODE' =>null, 'NAME' => 'testNull'))->one('NAME'));
        $this->assertEquals(false, CGroupModel::q()->where(array('CODE<>' =>null, 'NAME' => 'testNull'))->one('NAME'));

        $group2 = CGroupModel::GetOrCreate(array('NAME' => 'testNull2', 'CODE' => 'uniqnull'));
        $c = CGroupModel::q()->where(array('OR' => array(array('CODE' => 'uniqnull'), array('CODE' => null))))->count();
        $this->assertEquals(2, $c);
    }

}