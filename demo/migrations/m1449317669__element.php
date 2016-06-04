<?php

    /*
     * Date: 05.12.2015 15:14:29
     * Please, write your migartion code to up() method
     * You can use CQuery and throw new Exception().
    */

    class m1449317669__element {

        public static function up() {
            self::down();
            CCategoryModel::schema()->createTable();
            CElementModel::schema()->createTable();
            CElementCategoryModel::schema()->createTable(true);
            CElementTypeModel::schema()->createTable(true);
        }

        public static function down() {
            CElementTypeModel::schema()->dropTable();
            CElementCategoryModel::schema()->dropTable();
            CElementModel::schema()->dropTable();
            CCategoryModel::schema()->dropTable();
        }
    }
