<?php
chdir($cfg['cwd']);

$cfg += get_dir_url_info();

if (empty($cfg['db']['name'])) {
	header('Location: '.$cfg['url']['base'].'install/'); 
	exit();
}

//print_pre($_SERVER);
//print_pre($cfg);

session_name('PHPSESSID_' . md5($cfg['url']['base']));
if (!defined('PC_TEST_MODE') or !PC_TEST_MODE) {
	@session_start();
}