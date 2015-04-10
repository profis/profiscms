<?php
use \Profis\CMS\App;

include 'core/autoload.php';

// for now we need global $cfg variable since DbException still depends on it
global $cfg;
$cfg = array(
	'debug_output' => true,
);

$config = include('app/config/local.php');
$app = new App($config);
$app->run();
