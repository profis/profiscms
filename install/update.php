<?php
/**
 * Script detects current CMS database version and updates it to the version suited for current CMS version.
 * This script may get executed either by direct browser request or by include statement inside install.php.
 *
 * @var array $cfg
 * @var PC_core $core
 * @var PC_site $site
 * @var PC_page $page
 * @var PC_gallery $gallery
 * @var PC_routes $routes
 * @var PC_auth $auth
 * @var PC_memstore $memstore
 * @var PC_cache $cache
 * @var PC_plugins $plugins
 * @var PC_database $db
 */

if( !defined('CORE_ROOT') )
	require dirname(__FILE__) . '/../core/path_constants.php';

if( !class_exists('PC_core') )
	require CORE_ROOT . 'base.php';

if( !isset($cfg['db']['name']) || empty($cfg['db']['name']) )
	echo 'Please run install script before trying to update.';

$path = dirname(__FILE__) . '/data/update';

function update_filter_version_numbers($filePath) {
	return preg_replace('#^.*/(.*)\\.[^\\.]*$#s', '$1', $filePath);
}

function update_get_available_updates($path) {
	$versions = array();
	foreach( glob($path . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir )
		$versions = array_merge($versions, array_map('update_filter_version_numbers', glob($dir . '/*', GLOB_NOSORT)));
	$versions = array_unique($versions);
	usort($versions, 'version_compare');
	return $versions;
}

$versions = update_get_available_updates($path);

function update_get_table_info($tableName) {
	global $cfg, $db, $core;
	$s = $db->prepare($q = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table");
	$p = array(
		'db' => $cfg['db']['name'],
		'table' => $core->db_prefix . $tableName,
	);
	if( !$s->execute($p) )
		throw new DbException($s->errorInfo(), $q, $p);
	return $s->fetch();
}

function update_get_column_info($tableName, $columnName) {
	global $cfg, $db, $core;
	$s = $db->prepare($q = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table AND COLUMN_NAME = :column");
	$p = array(
		'db' => $cfg['db']['name'],
		'table' => $core->db_prefix . $tableName,
		'column' => $columnName,
	);
	if( !$s->execute($p) )
		throw new DbException($s->errorInfo(), $q, $p);
	return $s->fetch();
}

function update_detect_current_schema_version() {
	global $cfg, $db, $core;

	if( update_get_table_info('db_version') ) {
		$s = $db->prepare($q = "SELECT `version` FROM `{$core->db_prefix}db_version` WHERE `plugin` = ''");
		if( !$s->execute() )
			throw new DbException($s->errorInfo(), $q);
		if( $f = $s->fetch() )
			return $f['version'];
		else
			throw new Exception('Although `db_version` table exists it does not contain a record with current framework database version.');
	}

	if( update_get_table_info('site_users_external') ) {
		return '4.4.17';
	}

	if( update_get_column_info('pages', 'target') ) {
		return '4.4.5';
	}

	if( update_get_column_info('pages', 'source_id') ) {
		return '4.4.4';
	}

	// if all passwords are md5 hashes, then it was updated by script to version 4.4.3
	$s = $db->prepare($q = "SELECT 1 FROM `{$core->db_prefix}auth_users` WHERE `pass` NOT REGEXP '^[0-9a-f]{32}\$'  LIMIT 1");
	if( !$s->execute() )
		throw new DbException($s->errorInfo(), $q);
	if( !$s->fetch() )
		return '4.4.3';

	if( update_get_column_info('config', 'site') )
		return '4.4.1';

	if( update_get_column_info('content', 'custom_name') )
		return '4.4.0';

	if( update_get_column_info('sites', 'active') )
		return '4.3.0';

	if( update_get_table_info('auth_permissions') )
		return '4.2.0';

	return '4.0.0';
}

$dbVersion = update_detect_current_schema_version();

$r = $db->prepare($q = "UPDATE `{$core->db_prefix}db_version` SET `version` = :version WHERE `plugin` = ''");

echo '<pre>';
$dbType = v($cfg['db']['type'], 'mysql');
echo "Database type: {$dbType}\n";
echo "Current framework schema version: {$dbVersion}\n";

foreach( $versions as $version ) {
	if( version_compare($version, $dbVersion) <= 0 ) {
		echo "Skipping update to {$version}\n";
		continue;
	}
	if( version_compare($version, PC_VERSION) > 0 ) {
		echo "Stopping because next update version ({$version}) is greater than current CMS version (" . PC_VERSION . ")\n";
		break;
	}
	echo "Updating to {$version}\n";

	if( is_file($f = $path . '/' . $dbType . '/' . $version . '.sql') ) {
		echo "  Importing SQL file {$f}\n";
		db_file_import(array($dbType => $f));
	}
	if( is_file($f = $path . '/script/' . $version . '.php') ) {
		echo "  Executing PHP script {$f}\n";
		include $f;
	}

	if( !$r->execute($p = array('version' => $version)) )
		throw new DbException($s->errorInfo(), $q, $p);
}
echo "DONE\n";
echo '</pre>';
