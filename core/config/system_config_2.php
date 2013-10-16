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



//directories
$cfg['directories'] =  array(
	'cms' => 'cms',
	'admin' => 'admin',
	'themes' => 'themes',
	'classes' => CORE_DIR . DS . 'classes',
	'libs' => 'libs',
	'logs' => 'logs',
	'plugins' => 'plugins',
	'core_plugins' => CORE_DIR . DS . 'plugins',
	'core_plugins_www' => CORE_DIR . '/plugins',
	'gallery' => 'gallery',
	'media' => 'media',
	'backups' => 'backups',
	'uploads' => 'upload',
	'config' => CORE_DIR . DS . 'config'
);

//if custom value is not set then set this by its configured (default) value
foreach ($cfg['core_defaults'] as $key=>$value) {
	if (!isset($cfg['core'][$key])) $cfg['core'][$key] = $value;
}
unset($key, $value);

$cfg['trailing_slash'] = '/';
if (isset($cfg['router']) and isset($cfg['router']['no_trailing_slash']) and $cfg['router']['no_trailing_slash']) {
	$cfg['trailing_slash'] = '';
}

if (!isset($cfg['from_email'])) {
	$cfg['from_email'] = 'noreply@' . $_SERVER['HTTP_HOST'];
}

//static regexp patterns
$cfg['patterns'] = array(
	'plugin_name'=> "[A-Za-z][A-Za-z_]{0,48}[A-Za-z]",
	'email'=> '([a-zA-Z0-9]+([\.+_-][a-zA-Z0-9]+)*)@(([a-zA-Z0-9]+((\.|[-]{1,2})[a-zA-Z0-9]+)*)\.[a-zA-Z]{2,6})',
	
	//1st match must be for new route, 2nd match will be for $_GET['page']
	'page_get_var' => '((?:.)*\/?)'.$cfg['get_vars']['page_get_var'].'(\d+)\/?$',
	
	//1st match must be for new route, 2nd match will be for $_GET['ppage']
	'ppage_get_var' => '((?:.)*\/?)'.$cfg['get_vars']['ppage_get_var'].'(\d+)\/?$',
);

//don't change following settings if you're not sure what you're doing!
$cfg['core_plugins'] = array(
	'core','page', 'backup', 'domains', 'sites', 
	'variables', 'auth', 'config', 'forms'
);

$cfg['valid_page_fields'] = array(
	'name', 'custom_name', 'info', 'info2', 'info3', 'title', 'keywords', 
	'description', 'route', 'permalink', 'ln_redirect', 'text',
	'redirect', 'controller', 'route_lock', 'published', 'hot', 
	'nomenu', 'date_from', 'date_to', 'date', 'reference_id', 
	'source_id', 'target'
);

if (isset($cfg['debug_output']) and isset($cfg['debug_ip']) and $cfg['debug_ip'] == $_SERVER['REMOTE_ADDR']) {
	$pc_testing = true;
}
define('PC_TESTING', $pc_testing);
