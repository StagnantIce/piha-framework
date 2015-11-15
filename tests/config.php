<?php

return array(
    'piha' => array(
        'modules' => array('core', 'orm', 'bootstrap', 'bootstrap3'),
        'autoload' => array(
            array('@demo', 'controllers'),
            array('@demo', 'models')
        )
    ),
    'core' => array(
        'viewPath' => array('@demo', 'views'),
        'layoutPath' => array('@demo', 'views', 'layouts'),
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