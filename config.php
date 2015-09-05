<?php

return array(
	CCoreModule::GetID() => array(
		'homeController' => 'home/index',
		'paths' => array(
			'controllers',
			'models'
		),
		'viewPath' => 'views',
		'layoutPath' => 'views' . DS . 'layouts'
	),
	COrmModule::GetID() =>  array(
		'className' => 'CMysqlConnection',
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
