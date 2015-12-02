<?php

use \piha\modules\storage\classes\CSessionStorage;

return array(
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