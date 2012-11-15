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
 
if (phpversion() < 5.2) die('ProfisCMS requires at least PHP 5.2 version.');
define('PC_VERSION', '4.4.0b');

//error handling
require('error_handling.php');

//other settings
mb_internal_encoding('UTF-8');

//session for site/admin users
//force system to use submitted session id
if (isset($_POST['phpsessid'])) session_id($_POST['phpsessid']);

//play with working directories
$cfg['path']['cwd'] = str_replace('\\', '/', getcwd()).'/';
$cfg['cwd'] =& $cfg['path']['cwd'];
chdir(dirname(__FILE__));

require_once('config.php');
require_once('functions.php');

//date_default_timezone_set(v($cfg['timezone'], "Europe/Vilnius"));
date_default_timezone_set('UTC');

//enable gzip, if not specified differently
if (substr_count(v($_SERVER['HTTP_ACCEPT_ENCODING'],''), 'gzip')) if (!core_get('no_gzip')) if (!ini_get('zlib.output_compression')) @ob_start('ob_gzhandler');

//magic quotes handling
ini_set('magic_quotes_runtime', 0);
if (get_magic_quotes_gpc()) {
    function stripslashes_gpc(&$value) {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
}

//define paths
$cfg['path']['base'] = str_replace('\\', '/', getcwd()).'/';
$cfg['path']['system'] =& $cfg['path']['base'];
foreach ($cfg['directories'] as $k=>$d) {
	$cfg['path'][$k] = $cfg['path']['base'].$d.'/';
}

$class_autoload = array(
	'PhpThumbFactory'=> $cfg['path']['classes'].'phpthumb'.'/'.'ThumbLib.inc.php',
	'PHPMailer'=> $cfg['path']['classes'].'class.phpmailer.php'
);
/**
* Class autoload function.
* Use Register_class_autoloader($class, $path) to extend this list.
* @param mixed $cls.
*/
function PC_autoload($cls) {
	global $class_autoload;
	if (!isset($class_autoload[$cls])) {
		if (preg_match("#^PC_[a-z0-9_]+$#i", $cls)) {
			global $cfg;
			$path = $cfg['path']['classes'].$cls.'.php';
		}
		else return false;
	}
	else $path =& $class_autoload[$cls];
	if (!is_file($path)) return false;
	require_once($path);
}
spl_autoload_register('PC_autoload');

require("database.php");

chdir($cfg['cwd']);

$cfg += get_dir_url_info();
session_name('PHPSESSID_' . md5($cfg['url']['base']));
session_start();

$HTTPS = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS'])!='off';
$PROTOCOL = $HTTPS ? 'https://' : 'http://';

$memstore = new PC_memstore; // used only to store values temporarily within process memory (previously was $cache = new PC_cache;)
$cache = isset($cfg["cache"]["class"]) ? new {$cfg["cache"]["class"]} : new PC_cache;

$core = new PC_core;

$auth = new PC_auth;

$plugins = new PC_plugins;
$plugins->Scan();
$routes = new PC_routes;

$site = new PC_site;
$page = new PC_page;
$gallery = new PC_gallery;

