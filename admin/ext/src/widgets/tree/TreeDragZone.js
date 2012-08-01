<?php

if ($_SERVER['SERVER_PORT'] != '82') {
	if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE && function_exists('ob_gzhandler')) {
		@header('Content-Encoding: gzip');
		$supp = ob_start('ob_gzhandler');
	}
}
session_start();
if (isset($is_from_old)) { chdir("start_page"); }
include '../tasks/inc.config.php';
include 'inc.class.mysql.php';
include 'inc.functions.php';
//$_SERVER['REMOTE_ADDR'] = '192.168.1.84';
include 'lib.start_page.php';

$ln = isset($ln) ? $ln : 'lt';

$site_addr = 'http://'.$_SERVER['HTTP_HOST'].'/profis/start_page/';
$root_addr = 'http://'.$_SERVER['HTTP_HOST'].'/profis/';
$root_addr_82 = 'http://192.168.1.11:82/profis/';
$path = "\\\\192.168.1.10\\Profis\\";
error_reporting(E_ALL);
// ------- CUKRUS table -----------------
$tmp_db = $db; $tmp_pconnection = $pconnection; $pconnection = false;
$db['name'] = 'cukruspro'; $db['conn'] = false;
$t_cukrus = new mysql("texts_cukruspro");
$db = $tmp_db; $db['conn'] = false; $pconnection = $tmp_pconnection;
$tmp_pconnection = null; $tmp_db = null;
// --------------------------------------
$t_days 		= new mysql("days");
$t_taskai 		= new mysql("taskai");
$t_komentarai 	= new mysql("komentarai");
$t_pranesimai 	= new mysql("pranesimai");
$t_busenos 		= new mysql("busenos");

for ($i = -10; $i <= 15; $i++) $style[$i] = 'TEMP';
$style[-7]	= '<span style="color: #5080C2">TEMP</span>';
$style[0]	= '<span style="color: #A0A0A0">TEMP</span>';
$style[1]	= '<u>TEMP</u>';
$style['1a']= '<b>TEMP</b>';
$style[9]	= '<span style="color: #0000B0">TEMP</span>';
$style[12]	= '<span style="color: #AA6000">TEMP</span>';
$style[13]	= '<span style="color: #008000">TEMP</span>';

$settings = update_settings(); // saves if needed and loads

$blocks = array(
	'block.cukrus' => 'Dokumentai',
	'block.links' => 'Nuorodos',
	'block.my_projects' => 'Mano taskai',
	'block.statuses' => 'Statusai',
	'block.birthdays' => 'Gimtadieniai',
	'block.project_list' => 'Projektų sąrašas',
	'block.projects_right' => 'Projektai',
	'block.send_msg' => 'Pranešimai',
);

$php_version_url = Array(
	"4" => "http://192.168.1.11:81",
	"5" => "http://192.168.1.10",
	"6" => "http://192.168.1.10",
);

if ($_SERVER['REMOTE_ADDR'] == '192.168.1.210') $curr_user['is_manager'] = true;

	if (!isset($settings['view'])) $settings['view'] = 'manager';
	$dienu_rodyti = isset($settings['days']) ? $settings['days'] : 10;
	$days_arr = array (0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 'all')