<?php

    /*
     * Date: 22.11.2015 14:47:07
     * Please, write your migartion code to up() method
     * You can use CQuery and throw new Exception().
    */

    class m1448192827__user {

        public static function up() {
            CUserModel::schema()->createTable();
            CGroupModel::schema()->createTable();
            CUserGroupModel::schema()->createTable();
        }

        public static function down() {
            CUserGroupModel::schema()->dropTable();
            CUserModel::schema()->dropTable();
            CGroupModel::schema()->dropTable();
        }
    }
