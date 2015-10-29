<?php

return array(
    'core' => array(
        'homeController' => 'home/index',
        'paths' => array(
            array(__DIR__, 'controllers'),
            array(__DIR__, 'models')
        ),
        'configFile' => __FILE__,
        'viewPath' => array(__DIR__, 'views'),
        'layoutPath' => array(__DIR__, 'views', 'layouts')
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
