<?php
# ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http:#www.gnu.org/licenses/>.
 
//init sql parser
global $sql_parser;
$sql_parser = new PC_sql_parser;
//pdo fallback class if native extension is not enabled
if (!class_exists('PDO')) require_once(CORE_ROOT . "classes/pdo/PDO.class.php");
//connect to the database
try {
	$port = '';
	if (!empty($cfg['db']['port'])) {
		$port = 'port='.$cfg['db']['port'].';';
	}
	switch ($cfg['db']['type']) {
		case 'mssql':
			$db = new PC_database("mssql:host=".$cfg['db']['host'].";".$port."dbname=".$cfg['db']['name'], $cfg['db']['user'], $cfg['db']['pass']);
			break;
		case 'pgsql':
			$db = new PC_database("pgsql:host=".$cfg['db']['host'].";".$port."dbname=".$cfg['db']['name'], $cfg['db']['user'], $cfg['db']['pass']);
			$db->query("SET search_path TO ".$cfg['db']['name']);
			$db->query("SET NAMES '".$cfg['db']['charset']."'");
			break;
		case 'sqlite3':
		case 'mysqli':
		default:
			$db = new PC_database("mysql:host=".$cfg['db']['host'].";".$port."dbname=".$cfg['db']['name'], $cfg['db']['user'], $cfg['db']['pass'], array(
				PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
			));
			
			$db->query("SET NAMES '".$cfg['db']['charset']."'");
			$db->query("SET group_concat_max_len=10000");
	}
	//set database defaults
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
	die($e->getMessage());
}

//we wont need database password in the following code, so we could unset it for the security reasons
$cfg['db']['pass'] = '';