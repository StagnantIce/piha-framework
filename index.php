<?php

require_once('piha/Piha.php');

Piha::app(array('core', 'orm', 'bootstrap'))->setConfig(require(__DIR__ . '/config.php'))->start();