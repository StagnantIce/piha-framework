<?php

require_once('piha/Piha.php');

Piha::app(__DIR__)->setConfig(require(__DIR__ . '/config.php'))->start();