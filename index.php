<?php

define('DS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '\\' : '/');
define('PIHA_BASE_PATH', __DIR__);
define('PIHA_CORE_PATH', __DIR__ . DS . 'piha');

require_once(PIHA_CORE_PATH . DS . 'AModule.php');

AModule::AddAll('core', 'orm');
AModule::Add('custom', __DIR__);
AModule::ConfigureAll('config.php');

CCore::app()->start();

