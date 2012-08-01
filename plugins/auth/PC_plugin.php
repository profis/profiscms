<?php
function auth_core_access_handler($data, $plugin_name) {
	$r = array_search($plugin_name, $data['access']);
	if ($r === false) return false;
	return true;
}
$this->auth->permissions->Register('core', 'plugins', 'auth_core_access_handler');