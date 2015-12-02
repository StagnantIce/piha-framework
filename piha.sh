#!/usr/bin/env php
<?php

define("PIHA_CONSOLE", true);

require_once(__DIR__ . '/../index.php');

use piha\CAlias;
use piha\CException;

$args = array();

$script_name = array_shift($argv);

if (!$argv) {
	echo "\nYou need write command name\n\n";
	exit(0);
}

$name = array_shift($argv);
\Piha::execute($name, $argv);
