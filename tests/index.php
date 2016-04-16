<?php

define('PIHA_CONSOLE', true);
require_once(__DIR__ .'/../piha/Piha.php');

Piha::app(__DIR__ . '/../demo')
	->configure(require(__DIR__ . '/config.php'))
	->start();