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

global $cfg, $db, $sql_parser;

if (!defined('PC_VERSION')) {
	define('PC_VERSION', '4.4.5b');

	class PC_app {
		static $cfg;
		static $content_type = "";
		//static $content_type = "text/html; charset=utf-8";
	}
	//error handling
	require('error_handling.php');
}







//other settings
mb_internal_encoding('UTF-8');

//session for site/admin users
//force system to use submitted session id
if (isset($_POST['phpsessid'])) session_id($_POST['phpsessid']);

include 'base_config.php';

//print_pre($cfg);exit;
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


global $class_autoload;
$class_autoload = array(
	strtolower('PhpThumbFactory') => $cfg['path']['classes'].'phpthumb'.'/'.'ThumbLib.inc.php',
	strtolower('PHPMailer') => $cfg['path']['classes'].'class.phpmailer.php'
);
/**
* Class autoload function.
* Use Register_class_autoloader($class, $path) to extend this list.
* @param mixed $cls.
*/

PC_app::$cfg = $cfg;

if (!function_exists('PC_autoload')) {
	function PC_autoload($cls) {
		global $class_autoload;
		$cls_to_lower = strtolower($cls);
		/*
		if ($cls == 'Class name to debug') {
			echo 'class name: ' . $cls_to_lower;
			print_pre($class_autoload);
			echo isset($class_autoload[$cls_to_lower]);
		}
		*/
		if (!isset($class_autoload[$cls_to_lower])) {
			if (preg_match("#^PC_[a-zA_Z0-9_]+$#i", $cls)) {
				global $cfg;
				$sub_folder = '';
				if ($cls != 'PC_model' and substr($cls, -6) == '_model') {
					$sub_folder = 'models/';
				}
				if ($cls != 'PC_widget' and substr($cls, -7) == '_widget') {
					$sub_folder = 'widgets/';
				}
				$path = PC_app::$cfg['path']['classes'].$sub_folder.$cls.'.php';
			}
			else return false;
		}
		else $path =& $class_autoload[$cls_to_lower];
		if (!is_file($path) and $cls_to_lower == 'pc_utils') {
			$path = str_replace('PC_Utils.php', 'PC_utils.php', $path);
		}
		if (!is_file($path)) {
			return false;
		}
		require_once($path);
	}

	spl_autoload_register('PC_autoload');
}





include 'base_session.php';


$HTTPS = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS'])!='off';
$PROTOCOL = $HTTPS ? 'https://' : 'http://';



if (!defined('PC_TEST_MODE') or !PC_TEST_MODE) {
	require("database.php");
	include 'components.php';
}
