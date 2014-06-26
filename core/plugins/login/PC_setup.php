<?php

function login_install($controller) {
	global $core;
	
	$plugin_name = 'login';
	
	
	$core->Set_variable_if('lt', 'username', 'Vartotojas', $plugin_name);
	$core->Set_variable_if('en', 'username', 'Username', $plugin_name);
	$core->Set_variable_if('ru', 'username', 'Имя пользователя', $plugin_name);
	
	$core->Set_variable_if('lt', 'email', 'E. paštas', $plugin_name);
	$core->Set_variable_if('en', 'email', 'Email', $plugin_name);
	$core->Set_variable_if('ru', 'email', 'E-mail', $plugin_name);
	
	$core->Set_variable_if('lt', 'password', 'Slaptažodis', $plugin_name);
	$core->Set_variable_if('en', 'password', 'Password', $plugin_name);
	$core->Set_variable_if('ru', 'password', 'Пароль', $plugin_name);
	
	$core->Set_variable_if('lt', 'register', 'Registruotis', $plugin_name);
	$core->Set_variable_if('en', 'register', 'Register', $plugin_name);
	$core->Set_variable_if('ru', 'register', 'Регистрироваться', $plugin_name);
	
	$core->Set_variable_if('lt', 'btn_login', 'Prisijungti', $plugin_name);
	$core->Set_variable_if('en', 'btn_login', 'Login', $plugin_name);
	$core->Set_variable_if('ru', 'btn_login', 'Войти', $plugin_name);
	
	$core->Set_variable_if('lt', 'btn_logout', 'Atsijungti', $plugin_name);
	$core->Set_variable_if('en', 'btn_logout', 'Log out', $plugin_name);
	$core->Set_variable_if('ru', 'btn_logout', 'Выйти', $plugin_name);
	
	$core->Set_variable_if('lt', 'forgot_password', 'Pamiršote slaptažodį?', $plugin_name);
	$core->Set_variable_if('en', 'forgot_password', 'Forgot your password?', $plugin_name);
	$core->Set_variable_if('ru', 'forgot_password', 'Забыли пароль?', $plugin_name);
	
	$core->Set_variable_if('lt', 'error_empty', 'Laukas "%" turi būti įrašytas.', $plugin_name);
	$core->Set_variable_if('en', 'error_empty', 'Field &quot;%&quot; must be entered.', $plugin_name);
	$core->Set_variable_if('ru', 'error_empty', 'Поле "%" должено быть введенно.', $plugin_name);
	
	$core->Set_variable_if('lt', 'error_bad_login', 'Neteisingai įvestas el paštas arba slaptažodis.', $plugin_name);
	$core->Set_variable_if('en', 'error_bad_login', 'Wrong e-mail or password.', $plugin_name);
	$core->Set_variable_if('ru', 'error_bad_login', 'Неправильный адрес электронной почты или пароль.', $plugin_name);
	
	//===================
	return true;
}