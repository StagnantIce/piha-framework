<?php

define("PIHA_INCLUDE", true);

require_once('piha/Piha.php');

$config = null;

if (file_exists(__DIR__ . '/../config.php')) {
	$config = require_once(__DIR__ . '/../config.php'); 
}

\Piha::app(__DIR__ . '/../')->configure($config)->start();

$args = array();

if (isset($_GET['argv'])) {
	$argv = $_GET['argv'];
} else {
	throw new piha\CException("No argument params. Use argv param with GET request.");
}

$script_name = array_shift($argv);

if (!$argv) {
	echo "\nYou need write command name\n\n";
	exit(0);
}

$name = array_shift($argv);
\Piha::execute($name, $argv);
