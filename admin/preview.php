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
if ($id < 1 || empty($ln)) {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('You should specify both page ID and language.');
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
if ($r->rowCount() != 1) {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	die('Page with specified ID and language combination was not found');
}
$page = $r->fetch();

$location = $cfg['url']['base'].($page['front']?'':($ln==$page['ln']?'':$ln.'/').$page['route'].'/');
header('Location: '.$location);