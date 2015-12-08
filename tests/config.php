<?php

return array(
    'piha' => array(
    ),
    'core' => array(
        'viewPath' => array('@demo', 'views'),
        'layoutPath' => array('@demo', 'views', 'layouts'),
        'autoload' => array(
            array('@demo', 'controllers'),
            array('@demo', 'models')
        )
    ),
    'orm' =>  array(
        'database' => array(
            'host' => '127.0.0.1',
            'login' => 'root',
            'password' => 'root',
            'name' => 'piha_test',
            'prefix' => 'piha_',
            'encode' => 'utf8'
        )
    )
);