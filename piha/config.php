<?php

return array(
    'piha' => array(
        'defaultModule' => 'core'
    ),
    'storage' => array(
        'prefix' => 'piha_'
    ),
    'user' => array(
        'authPage' => 'auth/login'
    ),
    'core' => array(
        'defaultController' => 'home/index',
        'configFile' => __FILE__,
        'viewPath' => array('@webroot', 'views'),
        'layoutPath' => array('@webroot', 'views', 'layouts'),
        'prettyUrl' => false,
        'smartUrl' => false,
        'autoload' => array(
            array('@webroot', 'controllers'),
            array('@webroot', 'models'),
            array('@webroot', 'classes')
        ),
    ),
    'orm' =>  array(
        'className' => 'piha\modules\orm\classes\CMysqlConnection',
        'migrationPaths' => array(
            'app'  => array('@webroot', 'migrations'),
            'permission' => array('@permission', 'migrations')
        ),
        'database' => array(
            'host' => '127.0.0.1',
            'login' => 'root',
            'password' => 'root',
            'name' => 'piha',
            'prefix' => 'piha_',
            'encode' => 'utf8'
        )
    ),
    'permission' => array(
    )
);
