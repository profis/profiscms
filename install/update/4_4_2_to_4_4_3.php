<?php 


require dirname(__FILE__) . '/../../core/path_constants.php';
require CORE_ROOT . 'base.php';

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

