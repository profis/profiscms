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
$cfg['core']['no_login_form'] = true;
require_once 'admin.php';

$action = isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:'');
if ($action == 'update') {
	if (!$auth->Authorize('core', 'admin')) {
		die('No access');
	}
	$out = array();
	$_plugins = json_decode($_POST['plugins']);
	if (!$_plugins) {
		$out['success'] = true;
		$out['plugins'] = $plugins->Get_for_output();
		$out['controllers'] = $plugins->Get_controllers_for_output();
	}
	else {
		$errs = false;
		$out['activated'] = $out['deactivated'] = array();
		foreach ($_plugins as $plugin_data) {
			if ($plugins->Exists($plugin_data[0])) {
				$is_activated = $plugins->Is_active($plugin_data[0]);
				if ($plugin_data[1] != $is_activated) {
					if ($plugin_data[1]) {
						if ($plugins->Activate($plugin_data[0])) {
							$out['activated'][] = $plugin_data[0];
							$plugin_setup_file = $cfg['path']['plugins'].$plugin_data[0].'/PC_setup.php';
							if (file_exists($plugin_setup_file)) {
								require($plugin_setup_file);
								$plugin_install_function = $plugin_data[0].'_install';
								if (function_exists($plugin_install_function)) {
									call_user_func($plugin_install_function, $plugin_data[0]);
								}
							}
						}
						else {
							$errs = true;
						}
					}
					else {
						if ($plugins->Deactivate($plugin_data[0])) {
							$out['deactivated'][] = $plugin_data[0];
							$plugin_setup_file = $cfg['path']['plugins'].$plugin_data[0].'/PC_setup.php';
							if (file_exists($plugin_setup_file)) {
								require($plugin_setup_file);
								//---
							}
						}
						else {
							$errs = true;
						}
					}
				}
			}
		}
		$core->Load_config();
		$out['plugins'] = $plugins->Get_for_output();
		$out['controllers'] = $plugins->Get_controllers_for_output();
		if ($errs) {
			$out['success'] = '?';
		}
		else {
			$out['success'] = true;
		}
	}
	echo json_encode($out);
	return;
}

chdir($cfg['path']['system']);
$_plugins = glob('plugins/*/dialog.php');
$mods = array();
$adminAuthorized = $auth->Authorize('core', 'admin');
foreach ($_plugins as &$p) {
	preg_match('#(.+)\/(.+)\/#i', $p, $k);
	$p = array(
		'type'=> $k[1],
		'name'=> $k[2],
		'path'=> $p
	);
	//check if plugin is activated
	if (!$plugins->Is_active($p['name'])) continue;
	if (!$adminAuthorized) if (!$auth->Authorize('core', 'plugins', $p['name'])) continue;
	//unset previously included plugin configuration & include new plugin
	unset($mod);
	$plugins->setCurrentlyParsing($p['name']);
	chdir($cfg['path']['plugins'].$p['name']);
	include $cfg['path']['system'].$p['path'];
	chdir($cfg['path']['system']);
	$plugins->clearCurrentlyParsing();
	//check if plugin configuration is defined, if not - continue to the other plugin
	if (!isset($mod) || !is_array($mod)) continue;
	//default plugin name
	if (!isset($mod['name'])) $mod['name'] = $p['name'];
	// find plugin icon
	do {
		if (isset($mod['icon'])) {
			if (file_exists($mod['icon'])) break;
			if (file_exists($p['type'].'/'.$mod['icon'])) {
				$mod['icon'] = $cfg['url']['base'].$p['type'].'/'.$mod['icon'];
				break;
			}
		}
		if (file_exists($p['type']."/{$p['name']}/{$p['name']}.png")) {
			$mod['icon'] = $cfg['url']['base'].$p['type']."/{$p['name']}/{$p['name']}.png";
			break;
		}
		if (file_exists($p['type']."/{$p['name']}/{$p['name']}.gif")) {
			$mod['icon'] = $cfg['url']['base'].$p['type']."/{$p['name']}/{$p['name']}.gif";
			break;
		}
		$mod['icon'] = 'images/plugin.default.png';
	} while(0);
	//assign plugin
	$mods[$p['name']] = $mod;
}

/* Should be sorted while including files!
//sort plugins in order that is defined in its priority
//comparison function
function mods_order($a, $b) {
	if ($a['priority'] == $b['priority']) {
		return 0;
	}
    return ($a['priority'] < $b['priority']) ? 1 : -1;
}
uasort($mods, 'mods_order');*/

//echo '<pre>'.print_r($mods, true).'</pre>';

foreach ($mods as $k=>$mod) {
	echo '<div class="mod_icon">';
	echo '<a onclick="'.htmlspecialchars(@$mod['onclick']).'">';
	echo '<img src="'.$mod['icon'].'" alt="" /><br />';
	echo $mod['name'];
	echo '</a>';
	echo '</div>';
}