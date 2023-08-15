<?php

    use piha\modules\permission\models\CPermissionModel;
    use piha\modules\permission\models\CPermissionUserModel;
    use \piha\modules\permission\classes\CPermission;
    /*
     * Date: 01.12.2015 22:54:42
     * Please, write your migartion code to up() method
     * You can use CQuery and throw new Exception().
    */

    class m1448999682__permission {

        public static function up() {
            CPermissionModel::schema()->createTable();
            CPermissionUserModel::schema()->createTable(true);
            CPermission::addRole('admin');
            CPermission::addPermission('admin');
        }

        public static function down() {
            CPermissionUserModel::schema()->dropTable();
            CPermissionModel::schema()->dropTable();
        }
    }
