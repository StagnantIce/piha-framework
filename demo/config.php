<?php

use \piha\modules\storage\classes\CSessionStorage;

return array(
    'admin' => array(
        'controllerNamespace' => 'admin',
        'path' => array('@webroot', 'modules'),
        'route' => 'admin',
        'autoload' => array(
            array('@admin', 'controllers'),
            array('@admin', 'models')
        ),
        'viewPath' => array('@admin', 'views'),
        'layoutPath' => array('@admin', 'views', 'layouts')
    ),
    'orm' =>  array(
        'database' => array(
            'host' => '127.0.0.1',
            'login' => 'root',
            'password' => 'root',
            'name' => 'piha',
            'prefix' => 'piha_',
            'encode' => 'utf8'
        )
    ),
    'user' => array(
        'modelClass' => 'CUserModel',
        'storageClass' => CSessionStorage::className()
    ),
    'core' => array(
        'prettyUrl' => true,
        'smartUrl' => true,
    ),
    'permission' => array(
        'modelClass' => 'CUserModel'
    )
);