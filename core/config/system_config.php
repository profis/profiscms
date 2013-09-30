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
$cfg['db']['name'] = 'profis_cms';
$cfg['db']['prefix'] = 'pc_';
$cfg['db']['charset'] = 'utf8';
$cfg['db']['collation'] = 'utf8_general_ci';

//timezone
$cfg['timezone'] = 'Europe/Vilnius';
//development
$cfg['debug_mode'] = false;

$cfg['debug_output'] = false;
$cfg['debug_ip'] = false;


//languages
$cfg['languages'] = array(
	'lt'=> 'Lietuvių',
	'en'=> 'English',
	'ru'=> 'Русский'
);
$cfg['admin_ln'] = 'en';

$cfg['meta_author'] = 'Profis';

// caching
$cfg["cache"] = array(
	'class' => 'PC_file_cache',
	'defaultExpireTime' => 600, // 10 minutes
	'options' => array('folder' => 'cache'), // PC_file_cache specific options
);

$cfg['gallery_dir_chmod'] = 0777;
$cfg['gallery_file_chmod'] = 0777;

//core variables defaults
$cfg['core_defaults'] = array(
	'no_gzip' => false,
	'no_login_form' => false,
	'demo_mode' => false
);

$cfg['salt'] = '#%4#%F3456fsg34%#13as97$^g1';

$cfg['router'] = array(
	//'no_trailing_slash' => true,
);

$cfg['headers'] = array(
	'Last-modified' => '',
);


$cfg['seo'] = array(
	//Maximum length for page content fields:
	'max_description' => false,
	'max_keywords' => false,
);

$cfg['get_vars'] = array(
	'ppage_get_var' => 'ppage',
	'page_get_var' => 'page'
);


