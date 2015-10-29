<?php

require_once('piha/Piha.php');

Piha::app(array('core', 'orm'), require(__DIR__ . '/config.php'));