<?php
global $cfg;

//play with working directories
$cfg['path']['cwd'] = str_replace('\\', '/', getcwd()).'/';
$cfg['cwd'] =& $cfg['path']['cwd'];
chdir(dirname(__FILE__));

$config_files = array(
	CORE_ROOT . 'config/system_config.php',
	CMS_ROOT . 'config.php',
	CORE_ROOT . 'config/system_config_2.php',
	CMS_ROOT . 'config_2.php'
);

foreach ($config_files as $key => $filename) {
	if (file_exists($filename)) {
		@require($filename);
	}
}

if (defined('PC_TEST_MODE')) {
	//Do not use production db in test mode:
	$cfg['db']['host'] = '';
	$cfg['db']['user'] = '';
	$cfg['db']['pass'] = '';
	$cfg['db']['name'] = '';
	$test_config_file = CMS_ROOT . 'config_test.php';
	if (file_exists($test_config_file)) {
		@require($test_config_file);
	}
}

//define paths
$cfg['path']['public'] = CMS_ROOT;
$cfg['path']['base'] = str_replace('\\', '/', getcwd()).'/';
$cfg['path']['system'] =& $cfg['path']['base'];
foreach ($cfg['directories'] as $k=>$d) {
	$cfg['path'][$k] = CMS_ROOT . $d;
	if (!empty($d)) {
		$cfg['path'][$k] .= '/';
	}
}
require_once('functions.php');