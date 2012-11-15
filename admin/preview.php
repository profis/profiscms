<?php
/** ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
error_reporting(0); //ensure PHP won't output any error data and won't destroy JSON structure
$cfg['core']['no_login_form'] = true; //don't output login form if there's no active session
require_once('admin.php'); //ensure the user is authorized, otherwise stop executing this script

$id = $_GET['id'];
$ln = $_GET['ln'];

$controller_data = $page->get_controller_data_from_id($id);
			
$url = '';
$is_permalink = false;
if ($controller_data and $core->Count_hooks('core/page/parse-page-url/'.$controller_data['plugin'])) {
	$core->Init_hooks('core/page/parse-page-url/'.$controller_data['plugin'], array(
		'url'=> &$url,
		'page_id'=> &$id,
		'is_permalink'=> &$is_permalink,
		'get_page_id' => true,
		'id' => $controller_data['id'],
		'ln' => $ln
	));
	if (false and !empty($url)) {
		echo $url;
		echo '<hr />';
		echo $id;
		echo '<hr />';
		//$this->core->Redirect_local($url, 301);
	}
}


if ($id < 1 || empty($ln)) {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die($id . ' - You should specify both page ID and language.');
}
$r = $db->prepare("SELECT d.ln,d.mask,p.front,p.site, route"
." FROM {$cfg['db']['prefix']}pages p"
." JOIN {$cfg['db']['prefix']}content c ON pid=p.id and ln=:ln"
." JOIN {$cfg['db']['prefix']}domains d ON d.site=p.site"
." WHERE p.id=:id");
$success = $r->execute(array(
	'id'=> $id,
	'ln'=> $ln
));
if (!$success) {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('Database error');
}
if ($r->rowCount() < 1) {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('Page with specified ID and language combination was not found');
}
$page = $r->fetch();

//print_pre($page);

if (empty($url)) {
	$url = ($page['front']?'':($ln==$page['ln']?'':$ln.'/').$page['route'].'/');
}
else {
	if (!$is_permalink) {
		$url = ($ln==$page['ln']?'':$ln.'/').$page['route'].'/' . $url;
	}
}

$url = $cfg['url']['base'] . $url;

if ($page['ln'] == $ln and strpos($url, $cfg['url']['base'] . $ln.'/') !== false) {
	$url = str_replace($cfg['url']['base'] . $ln.'/', $cfg['url']['base'], $url);
}

$location = $url;
header('Location: '.$location);