<?php
$this->core->Register_hook('site_init', function(){
	global $core, $site_users;
	$site_users = new PC_user;
});
