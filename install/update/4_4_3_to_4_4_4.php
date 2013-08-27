<?php 


require dirname(__FILE__) . '/../../core/path_constants.php';
require CORE_ROOT . 'base.php';

$sql_files = array(
	'mysql'=> CMS_ROOT . 'install/update/mysql/update_4.4.3_to_4_4_4.sql'
);
db_file_import($sql_files);

$core->Set_config_if('recaptcha_public_key', '', 'forms');
$core->Set_config_if('recaptcha_private_key', '', 'forms');

