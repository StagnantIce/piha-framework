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

foreach ($argv as $arg) {
	$arg = trim($arg);
    if (ereg('^--([^=]+)=(.*)$',$arg,$reg)) {
        $args[$reg[1]] = $reg[2];
    } elseif(ereg('^-([a-zA-Z0-9])$',$arg,$reg)) {
        $args[$reg[1]] = true;
    } else {
    	echo "\nError params format\n\n";
    	exit(0);
    }
}

\Piha::execute($name, $args);
