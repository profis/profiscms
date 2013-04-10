<?php

function site_users_install($controller) {
	global $core;
	
	$core->Set_variable_if('lt', 'email_subject_pass_change_confirm_code', 'Slaptažodžio keitimas', 'site_users');
	$core->Set_variable_if('en', 'email_subject_pass_change_confirm_code', 'Password change', 'site_users');
	$core->Set_variable_if('ru', 'email_subject_pass_change_confirm_code', 'Изменение пароля', 'site_users');
	
	$core->Set_config_if('email_sender_name', '', 'site_users');
	$core->Set_config_if('email_sender_email', '', 'site_users');
	
	$core->Set_config_if('no_confirmation', '', 'site_users');
	$core->Set_config_if('email_as_login', '', 'site_users');
	
	return true;
}