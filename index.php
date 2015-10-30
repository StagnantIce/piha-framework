<?php

require_once('piha/Piha.php');

Piha::app(array('core', 'orm', 'bootstrap'), require(__DIR__ . '/config.php'));