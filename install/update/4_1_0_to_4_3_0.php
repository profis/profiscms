<?php 

require dirname(__FILE__) . '/../../core/path_constants.php';
require CORE_ROOT . 'base.php';

$sql_files = array(
	'mysql'=> CMS_ROOT . 'install/update/mysql/update_4.1.0_to_4_3_0.sql'
);
db_file_import($sql_files);



