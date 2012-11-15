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

//database
$cfg['db']['type'] = 'mysql';
$cfg['db']['host'] = '';
$cfg['db']['user'] = '';
$cfg['db']['pass'] = '';
$cfg['db']['name'] = '';
$cfg['db']['prefix'] = 'pc_';
$cfg['db']['charset'] = 'utf8';
$cfg['db']['collation'] = 'utf8_general_ci';
//timezone
$cfg['timezone'] = 'Europe/Vilnius';
//development
$cfg['debug_mode'] = false;
//languages
$cfg['languages'] = array(
	'lt'=> 'Lietuvių',
	'en'=> 'English',
	'ru'=> 'Русский'
);
$cfg['admin_ln'] = 'en';
//directories
$cfg['directories'] =  array(
	'admin' => 'admin',
	'themes' => 'themes',
	'classes' => 'classes',
	'plugins' => 'plugins',
	'gallery' => 'gallery',
	'config' => 'config'
);
//core variables defaults
$cfg['core_defaults'] = array(
	'no_gzip' => false,
	'no_login_form' => false,
	'demo_mode' => false
);
//if custom value is not set then set this by its configured (default) value
foreach ($cfg['core_defaults'] as $key=>$value) {
	if (!isset($cfg['core'][$key])) $cfg['core'][$key] = $value;
}
unset($key, $value);

//static regexp patterns
$cfg['patterns'] = array(
	'plugin_name'=> "[A-Za-z][A-Za-z_]{0,48}[A-Za-z]",
	'email'=> '([a-zA-Z0-9]+([\.+_-][a-zA-Z0-9]+)*)@(([a-zA-Z0-9]+((\.|[-]{1,2})[a-zA-Z0-9]+)*)\.[a-zA-Z]{2,6})'
);

//don't change following settings if you're not sure what you're doing!
$cfg['core_plugins'] = array('core','page', 'backup', 'domains', 'sites', 'variables', 'auth');
$cfg['salt'] = '#%4#%F3456fsg34%#13as97$^g1';

$cfg['valid_page_fields'] = array(
	'name', 'info', 'info2', 'info3', 'title', 'keywords', 'description', 'route', 'permalink', 'text',
	'redirect', 'controller', 'route_lock', 'published', 'hot', 'nomenu', 'date_from', 'date_to', 'date', 'reference_id'
);