<?php

function pc_site_users_hook_init_object() {
	global $core, $site_users, $cfg;
	$site_users = new PC_user;
}


$this->core->Register_hook('site_init', 'pc_site_users_hook_init_object');
