<?php

return array(
    'piha' => array(
        'autoload' => array(
            array(__DIR__, 'controllers'),
            array(__DIR__, 'models')
        )
    ),
    'core' => array(
        'homeController' => 'home/index',
        'configFile' => __FILE__,
        'viewPath' => array(__DIR__, 'views'),
        'layoutPath' => array(__DIR__, 'views', 'layouts'),
        'cleanView' => true,
        'prettyUrl' => true,
        'smartUrl' => true
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
