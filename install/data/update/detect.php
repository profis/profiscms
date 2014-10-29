<?php
/**
 * This script tries to detect current version of the framework database.
 * 
 * @var array $cfg
 * @var PC_database $db
 * @var PC_core $core
 */
if( $db->getTableInfo('site_users_external') ) {
	return '4.4.17';
}

if( $db->getColumnInfo('content', 'info_mobile') ) {
	return '4.4.6b';
}

if( $db->getColumnInfo('pages', 'target') ) {
	return '4.4.5';
}

if( $db->getColumnInfo('pages', 'source_id') ) {
	return '4.4.4';
}

// if all passwords are md5 hashes, then it was updated by script to version 4.4.3
$s = $db->prepare($q = "SELECT 1 FROM `{$core->db_prefix}auth_users` WHERE `pass` NOT REGEXP '^[0-9a-f]{32}\$'  LIMIT 1");
if( !$s->execute() )
	throw new DbException($s->errorInfo(), $q);
if( !$s->fetch() )
	return '4.4.3';

if( $db->getColumnInfo('config', 'site') )
	return '4.4.1';

if( $db->getColumnInfo('content', 'custom_name') )
	return '4.4.0';

if( $db->getColumnInfo('sites', 'active') )
	return '4.3.0';

if( $db->getTableInfo('auth_permissions') )
	return '4.2.0';

return '4.0.0';