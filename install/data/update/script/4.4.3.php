<?php 
/**
 * Update to v4.4.3
 *
 * Hashes all admin area users' passwords in the database.
 *
 * @var array $cfg
 * @var PC_core $core
 * @var PC_site $site
 * @var PC_page $page
 * @var PC_gallery $gallery
 * @var PC_routes $routes
 * @var PC_auth $auth
 * @var PC_memstore $memstore
 * @var PC_cache $cache
 * @var PC_plugins $plugins
 * @var PC_database $db
 */

$auth_user_model = new PC_auth_user_model();

$users = $auth_user_model->get_all(array(
	'where' => array(
		"LENGTH(t.pass) <> 32"
	)
));

//print_pre($users);

foreach ($users as $user) {
	$new_pass = $auth->users->Encode_password($user['pass']);
	$auth_user_model->update(array('pass' => $new_pass), $user['id']);
}

