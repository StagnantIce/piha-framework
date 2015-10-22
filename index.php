<?php

//define('PIHA_BASE_PATH', __DIR__);
//define('PIHA_CORE_PATH', __DIR__ . DS . 'piha');
//define('PIHA_MODULE_PATH', PIHA_CORE_PATH . DS . 'modules');
//define('PIHA_DEPLOY_PATH', PIHA_CORE_PATH . DS . 'deploy');
//define('PIHA_MIGRATION_PATH', PIHA_DEPLOY_PATH . DS . 'migrations');

defined('PIHA_CONSOLE') or define('PIHA_CONSOLE', false);



require_once('piha/include.php');

use piha\CAlias;
use piha\AModule;
use piha\modules\core\classes\CCore;

//CAlias::register('root', __DIR__);

AModule::AddAll('core', 'orm');
//AModule::Add('custom', __DIR__);
AModule::ConfigureAll(__DIR__ . '/config.php');

CCore::app()->start();

