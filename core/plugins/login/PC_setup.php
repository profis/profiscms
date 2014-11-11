<?php

function login_install($controller) {
	global $core;
	
	$plugin_name = 'login';
	
	
	$core->Set_variable_if('lt', 'username', 'Vartotojas', $plugin_name);
	$core->Set_variable_if('en', 'username', 'Username', $plugin_name);
	$core->Set_variable_if('ru', 'username', 'Логин', $plugin_name);
	
	$core->Set_variable_if('lt', 'email', 'El. paštas', $plugin_name);
	$core->Set_variable_if('en', 'email', 'Email', $plugin_name);
	$core->Set_variable_if('ru', 'email', 'E-mail', $plugin_name);
	
	$core->Set_variable_if('lt', 'password', 'Slaptažodis', $plugin_name);
	$core->Set_variable_if('en', 'password', 'Password', $plugin_name);
	$core->Set_variable_if('ru', 'password', 'Пароль', $plugin_name);
	
	$core->Set_variable_if('lt', 'register', 'Registruotis', $plugin_name);
	$core->Set_variable_if('en', 'register', 'Register', $plugin_name);
	$core->Set_variable_if('ru', 'register', 'Регистрироваться', $plugin_name);

	$core->Set_variable_if('lt', 'sign_in', 'Prisijungimas', $plugin_name);
	$core->Set_variable_if('en', 'sign_in', 'Sign-in', $plugin_name);
	$core->Set_variable_if('ru', 'sign_in', 'Вход', $plugin_name);

	$core->Set_variable_if('lt', 'btn_login', 'Prisijungti', $plugin_name);
	$core->Set_variable_if('en', 'btn_login', 'Login', $plugin_name);
	$core->Set_variable_if('ru', 'btn_login', 'Войти', $plugin_name);
	
	$core->Set_variable_if('lt', 'btn_logout', 'Atsijungti', $plugin_name);
	$core->Set_variable_if('en', 'btn_logout', 'Log out', $plugin_name);
	$core->Set_variable_if('ru', 'btn_logout', 'Выйти', $plugin_name);

	$core->Set_variable_if('lt', 'forgot_username', 'Pamiršote vartotojo vardą?', $plugin_name);
	$core->Set_variable_if('en', 'forgot_username', 'Forgot your username?', $plugin_name);
	$core->Set_variable_if('ru', 'forgot_username', 'Забыли логин?', $plugin_name);

	$core->Set_variable_if('lt', 'forgot_password', 'Pamiršote slaptažodį?', $plugin_name);
	$core->Set_variable_if('en', 'forgot_password', 'Forgot your password?', $plugin_name);
	$core->Set_variable_if('ru', 'forgot_password', 'Забыли пароль?', $plugin_name);

	$core->Set_variable_if('lt', 'error_bad_login', 'Neteisingai įvestas vartotojo vardas arba slaptažodis.', $plugin_name);
	$core->Set_variable_if('en', 'error_bad_login', 'Incorrect username or password.', $plugin_name);
	$core->Set_variable_if('ru', 'error_bad_login', 'Неправильный логин или пароль.', $plugin_name);
	
	//===================
	return true;
}