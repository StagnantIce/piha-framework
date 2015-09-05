<?php

define('DS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '\\' : '/');
define('BASE_PATH', __DIR__);
define('CORE_PATH', __DIR__ . DS . 'piha');

require_once(CORE_PATH . DS . 'AModule.php');

AModule::AddAll('core', 'orm');
AModule::Add('custom', __DIR__);
AModule::ConfigureAll('config.php');

CCore::app()->start();

