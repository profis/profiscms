<?php

if (!defined('PC_INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}
$config = $_POST['config'];
$cfg = $installer->get_config();

$new_config_lines = array();

if ($cfg['db']['type'] != $config['db_driver']) {
	$new_config_lines[] = "\$cfg['db']['type'] = '{$config['db_driver']}';";
}
if ($cfg['db']['host'] != $config['db_host']) {
	$new_config_lines[] = "\$cfg['db']['host'] = '{$config['db_host']}';";
}
if ($cfg['db']['port'] != $config['db_port']) {
	$new_config_lines[] = "\$cfg['db']['port'] = '{$config['db_port']}';";
}
if ($cfg['db']['user'] != $config['db_user']) {
	$new_config_lines[] = "\$cfg['db']['user'] = '{$config['db_user']}';";
}
if ($cfg['db']['pass'] != $config['db_pass']) {
	$new_config_lines[] = "\$cfg['db']['pass'] = '{$config['db_pass']}';";
}
if ($cfg['db']['name'] != $config['db_name']) {
	$new_config_lines[] = "\$cfg['db']['name'] = '{$config['db_name']}';";
}
if ($cfg['db']['prefix'] != $config['table_prefix']) {
	$new_config_lines[] = "\$cfg['db']['prefix'] = '{$config['table_prefix']}';";
}

$cfg_content = file_get_contents(PC_CONFIG_FILE);
$cfg_content = rtrim($cfg_content);
$php_end = '?>';
$php_end_len = strlen($php_end);
if (mb_substr($cfg_content, - $php_end_len) == $php_end) {
	$cfg_content = mb_substr($cfg_content, 0, - $php_end_len);
}


$cfg_content .= "\n" . implode("\n", $new_config_lines);

$error = false;

try {
	$port = '';
	if (!empty($config['db_port'])) {
		$port = 'port='.$config['db_port'].';';
	}
	$db = new PDO("mysql:host=".$config['db_host'] . ";" . $port . "dbname=".$config['db_name'], $config['db_user'], $config['db_pass']);
	$db->query("SET NAMES '".$cfg['db']['charset']."'");
} catch (PDOException $e) {
	$error = '<strong>' . $t['install_failed_db'] . '</strong><br />' . $t['install_error'] . ': <p><strong>'. $e->getMessage() ."</strong></p>";
}
	
if (!$error) {
	$replacements = array(
		'{prefix}' =>  trim($db->quote($config['table_prefix']), "'"),
		'{admin_username}' => trim($db->quote($config['admin_username']), "'"),
		'{admin_password}' => trim($db->quote($config['admin_password']), "'"),
	);
	if (!$installer->import_sql_file(PC_INSTALL_DIR . 'mysql/mysql.sql', $replacements)) {
		//$error = "Database could not be imported!";
	}
}

if (!$error) {
	if (!file_put_contents(PC_CONFIG_FILE, $cfg_content)) {
		 $error = $t['config_write_error'];
	}
}

include('finish.php');