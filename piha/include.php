<?

require 'CAlias.php';
require 'IModule.php';
require 'AModule.php';


//namespace piha;
use piha\CAlias;

CAlias::path('piha', __DIR__);
CAlias::path('modules', array('piha', 'modules'));