<?php

return array(
    'piha' => array(
        'modules' => array('core', 'orm', 'bootstrap', 'bootstrap3'),
        'autoload' => array(
            array('@webroot', 'controllers'),
            array('@webroot', 'models')
        )
    ),
    'core' => array(
        'homeController' => 'home/index',
        'configFile' => __FILE__,
        'viewPath' => array('@webroot', 'views'),
        'layoutPath' => array('@webroot', 'views', 'layouts'),
    ),
    'orm' =>  array(
        'className' => 'piha\modules\orm\classes\CMysqlConnection',
        'migrationPaths' => array(
            'piha' => array('root', 'piha', 'deploy', 'migrations'),
            'app'  => array('root', 'migrations')
        ),
        'database' => array(
            'host' => '127.0.0.1',
            'login' => 'root',
            'password' => 'root',
            'name' => 'piha',
            'prefix' => 'piha_',
            'encode' => 'utf8'
        )
    )
);
