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

$logger = new PC_debug();
$logger->debug = true;
$logger->set_instant_debug_to_file($cfg['path']['logs'] . 'plugins/ajax.plugins.html', false, 5);

$plugins->absorb_debug_settings($logger);

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
							$logger->debug('Activating', 3);
							$logger->debug($plugin_data, 4);
							$out['activated'][] = $plugin_data[0];
							$plugin_folder = $core->Get_path('plugins', '', $plugin_data[0]);
							$plugin_setup_file = $plugin_folder . 'PC_setup.php';
							
							$sql_files = array(
								'mysql'=> 'setup/mysql.sql',
								'pgsql'=> 'setup/pgsql.sql'
							);
							$driver = $core->sql_parser->Get_default_driver();
							if (isset($sql_files[$driver])) {
								$sql = @file_get_contents($core->plugins->Get_plugin_path($plugin_data[0]).$sql_files[$driver]);
								if ($sql) {
									$core->sql_parser->Replace_variables($sql);
									$queries = explode(';', $sql);
									foreach ($queries as $query) {
										if (!empty($query)) {
											$query = trim($query);
											if (!empty($query)) {
												$core->db->query($query);
											}
											
										}
									}
								}
							}
							
							if (file_exists($plugin_setup_file)) {
								require($plugin_setup_file);
								$plugin_install_function = $plugin_data[0].'_install';
								$logger->debug($plugin_install_function, 4);
								if (function_exists($plugin_install_function)) {
									$logger->debug('install function exists', 5);
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
								$plugin_uninstall_function = $plugin_data[0].'_uninstall';
								$logger->debug($plugin_uninstall_function, 4);
								if (function_exists($plugin_uninstall_function)) {
									$logger->debug('uninstall function exists', 5);
									call_user_func($plugin_uninstall_function, $plugin_data[0]);
								}
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
$_plugins = glob($cfg['path']['plugins'] . '*/dialog.php');
if (!is_array($_plugins)) {
	$_plugins = array();
}
$_core_plugins = glob(CORE_PLUGINS_ROOT . '*/dialog.php');

//$logger->debug('$_plugins:', 1);
//$logger->debug($_plugins, 1);
//
//$logger->debug('$_core_plugins:', 1);
//$logger->debug($_core_plugins, 1);

if ($_core_plugins) {
	$_plugins = array_merge($_plugins, $_core_plugins);
	//$_plugins = array_merge($_core_plugins, $_plugins);
}

$logger->debug('$_plugins:', 1);
$logger->debug($_plugins, 1);

$logger->debug('Active plugins:', 5);
$logger->debug($cfg['active_plugins'], 5);

$mods = array();
$adminAuthorized = $auth->Authorize('core', 'admin');
foreach ($_plugins as &$p) {
	preg_match('#(.+)[\/|\\\\](.+)\/#i', $p, $k);
	//preg_match('#(.+)\/(.+)\/#i', $p, $k);
	$logger->debug('Matches:', 14);
	$logger->debug($k, 15);
	$p = array(
		'type'=> $k[1],
		'name'=> $k[2],
		'path'=> $p
	);
	$logger->debug('<hr />', 5);
	$logger->debug($p, 5);
	//check if plugin is activated
	if (!$plugins->Is_active($p['name'])) {
		$logger->debug(":( Plugin {$p['name']} is not active");
		continue;
	};
	if (!$adminAuthorized) if (!$auth->Authorize('core', 'plugins', $p['name'])) {
		$logger->debug(":( Plugin {$p['name']} is not accessible (authorization failed)");
		continue;
	}
	//unset previously included plugin configuration & include new plugin
	unset($mod);
	$plugins->setCurrentlyParsing($p['name']);
	$plugin_dir = $cfg['path']['plugins'].$p['name'];
	$plugin_dir = $p['type'].'/'.$p['name'];
	$logger->debug("chdir($plugin_dir)", 2);
	chdir($plugin_dir);
	include $p['path'];
	chdir($cfg['path']['system']);
	$plugins->clearCurrentlyParsing();
	//check if plugin configuration is defined, if not - continue to the other plugin
	if (!isset($mod) || !is_array($mod)) {
		$logger->debug(":( Mod is not an array");
		continue;
	};
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
	$logger->debug('mod:', 2);
	$logger->debug($mod, 2);
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