<?php

define("PIHA_INCLUDE", true);

require_once('piha/Piha.php');

$config = null;

if (file_exists(__DIR__ . '/../config.php')) {
	$config = require(__DIR__ . '/../config.php'); 
}

piha\CFile::Copy(__DIR__ . '/demo', __DIR__ . '/../');

\Piha::app(__DIR__ . '/../')->configure($config)->start();

\Piha::execute('migrate', array('up', '--alias=app'));
\Piha::execute('migrate', array('up', '--alias=permission'));

echo "Successfully installed!";
