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
$_twd = getcwd();
chdir(dirname(__FILE__));
if (!class_exists('PC_base')) require_once '../base.php';
require_once 'auth.php';

if (!is_null($cache)) {
	$core->Register_hook('core/cache/clear', 'PC_clear_cache');
	$core->Register_hook_observer('create_page', 'core/cache/clear');
	$core->Register_hook_observer('move_page', 'core/cache/clear');
	$core->Register_hook_observer('after_page_save', 'core/cache/clear');
}

function PC_clear_cache() {
	global $cache;
	$cache->flush();
}

function get_plugin_icon($fn=false) {
	global $cfg;
	if (!$fn) {
		$dbt = debug_backtrace();
		$fn = basename(dirname($dbt[0]['file']));
		//file_put_contents('debug_backtrace.dump.txt', print_r($dbt, true));
		//$fn = preg_replace('#^.*[\\\\/]#', '', $dbt[0]['file']);
	}
	//$noext = preg_replace('#^(.*\.)[^.]+$#', '$1', $fn);
	$noext = $fn;
	$rv = $cfg['url']['base'].$cfg['directories']['admin'].'/images/plugin.default.png';
	foreach (array('jpg', 'gif', 'png') as $ext) {
		$path = $cfg['path']['plugins'].$noext.'/'.$noext.'.'.$ext;
		if (file_exists($path)) {
			$rv = $cfg['url']['base'].$cfg['directories']['plugins'].'/'.$noext.'/'.$noext.'.'.$ext;
		}
	}
	return $rv;
}
function Get_unique_route($route, $ln, $dont_look_at_cid=0) {
	global $db, $cfg;
	if (mb_strlen($route) > 255) $route = substr($route, 0, 255);
	$generated_route = $route;
	$route_exists = true;
	$attempt = 1;
	while ($route_exists) {
		//$r = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}content JOIN {$cfg['db']['prefix']}pages p ON p.id=pid WHERE site=? and ln=? and route=?");
		$r = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}content WHERE route=? and ln=?"
		.($dont_look_at_cid>0?' and id!=?':''));
		$params = array($generated_route, $ln);
		if ($dont_look_at_cid > 0) $params[] = $dont_look_at_cid;
		$s = $r->execute($params);
		if (!$s) return false;
		if ($r->rowCount() > 0) {
			$generated_route = substr($route, 0, 255-strlen((string)$attempt)+1).'-'.$attempt;
			$attempt++;
		}
		else $route_exists = false;
	}
	return $generated_route;
}
function hmac($key, $data) {
	$b = 64; // byte length for md5
	if (strlen($key) > $b)
		$key = pack("H*", md5($key));
	$key  = str_pad($key, $b, chr(0x00));
	$ipad = str_pad('', $b, chr(0x36));
	$opad = str_pad('', $b, chr(0x5c));
	$k_ipad = $key ^ $ipad;
	$k_opad = $key ^ $opad;
	return md5($k_opad . pack("H*", md5($k_ipad . $data)));
}
function hex_hmac_md5($k, $d) {
	if (function_exists('mhash'))
		return bin2hex(mhash(MHASH_MD5, $d, $k));
	else return hmac($k, $d);
}

chdir($_twd);