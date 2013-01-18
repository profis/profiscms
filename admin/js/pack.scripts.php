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
require('../admin.php');
//files to pack
$files = array();
$files[] = 'swfupload.js';
$files[] = 'swfupload.swfobject.js';
$files[] = 'ZeroClipboard.js';
$files[] = 'BigInt.js';
$files[] = 'jsaes.js';
$files[] = 'PC_utils.js';
$localizeAt = count($files);
$files = array_merge($files, glob('Ext.ux.*.js'), glob('PC.ux.*.js'));
$files = array_merge($files, glob('PC.*.js'));
//$files = array_merge($files, glob('Ext.ux.*.js'), glob('ProfisCMS.*.js'), glob('PC.*.js'));

$files = array_merge($files, glob('dialog.*.js'));

$pluginsStart = count($files);

//load custom plugins js
foreach ($plugins->loaded_plugins as $plugin) {
	$plugin_file = $core->Get_path('plugins', 'PC_plugin.js', $plugin);
	if (is_file($plugin_file)) $files[] = $plugin_file;
}

$pluginsEnd = count($files);

$files[] = 'admin_core.js';
//array_pop($files); $files[] = 'admin_mock.js'; //used to run only one component (dev mode)

//identify last modification time and use highest value
$last_mod = 0;
foreach ($files as $f) {
	$last_mod = max($last_mod, filemtime($f));
}
header('Content-Type: application/javascript');
header('Last-Modified: '.date('D, d M Y H:i:s O', $last_mod));
header('True-Last-Modified: '.date('D, d M Y H:i:s O', $last_mod));

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	$cached_time = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
	if ($last_mod == $cached_time) {
		header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified (pack scripts)');
		exit;
	}
}
$files = array_unique($files);
//print_pre($files);
//echo "i >= $pluginsStart && i < $pluginsEnd";
foreach ($files as $i=>$file) {
	if ($i == $localizeAt) echo "\n\nPC.utils.localize();\n\n";
	//if ($i - $localizeAt > 15) break;
	if ($i >= $pluginsStart && $i < $pluginsEnd) {
		$plugin_name = preg_match("#/([^/]+)/PC_plugin.js$#i", $file, $m);
		//print_pre($m);
		echo "\nvar CurrentlyParsing = '".$m[1]."';\n";
		//before opening plugins
	}
	if (v($cfg['debug_mode'])) echo "\n\n/***** $file *****/\n\n";
	else echo "\n\n";
	readfile($file);
}