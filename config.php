<?php

return array(
	'core' => array(
		'homeController' => 'home/index',
		'paths' => array(
			array(__DIR__, 'controllers'),
			array(__DIR__, 'models')
		),
		'viewPath' => array(__DIR__, 'views'),
		'layoutPath' => array(__DIR__, 'views', 'layouts')
	),
	'orm' =>  array(
		'className' => 'piha\modules\orm\classes\CMysqlConnection',
		'migrationPaths' => array(
			'piha' => PIHA_MIGRATION_PATH,
			'app'  => PIHA_BASE_PATH . DS . 'migrations'
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
