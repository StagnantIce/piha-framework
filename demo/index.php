<?php

require_once('piha/piha/Piha.php');

Piha::app(__DIR__)
	->configure(require(__DIR__ . '/config.php'))
	->start();