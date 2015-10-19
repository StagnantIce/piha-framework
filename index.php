<?php

define('DS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '\\' : '/');
define('PIHA_BASE_PATH', __DIR__);
define('PIHA_CORE_PATH', __DIR__ . DS . 'piha');
define('PIHA_MODULE_PATH', PIHA_CORE_PATH . DS . 'modules');
define('PIHA_DEPLOY_PATH', PIHA_CORE_PATH . DS . 'deploy');
define('PIHA_MIGRATION_PATH', PIHA_DEPLOY_PATH . DS . 'migrations');

defined('PIHA_CONSOLE') or define('PIHA_CONSOLE', false);

require_once(PIHA_CORE_PATH . DS . 'AModule.php');

AModule::AddAll('core', 'orm');
//AModule::Add('custom', __DIR__);
AModule::ConfigureAll(__DIR__ . '/config.php');

CCore::app()->start();

