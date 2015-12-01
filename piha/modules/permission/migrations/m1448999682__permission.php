<?php

    use piha\modules\permission\models\CPermissionModel;
    use piha\modules\permission\models\CPermissionUserModel;
    /*
     * Date: 01.12.2015 22:54:42
     * Please, write your migartion code to up() method
     * You can use CQuery and throw new Exception().
    */

    class m1448999682__permission {

        public static function up() {
            CPermissionModel::schema()->createTable();
            CPermissionUserModel::schema()->createTable(true);
        }

        public static function down() {
            CPermissionModel::schema()->dropTable();
            CPermissionUserModel::schema()->dropTable();
        }
    }
