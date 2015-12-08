<?php

use \piha\modules\storage\classes\CSessionStorage;

return array(
    'admin' => array(
        'path' => array('@demo', 'modules'),
        'route' => 'admin',
        'autoload' => array(
            array('@admin', 'controllers'),
            array('@admin', 'models')
        ),
        'viewPath' => array('@admin', 'views'),
        'layoutPath' => array('@admin', 'views', 'layouts')
    ),
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
    ),
    'user' => array(
        'modelClass' => 'CUserModel',
        'storageClass' => CSessionStorage::className()
    ),
);