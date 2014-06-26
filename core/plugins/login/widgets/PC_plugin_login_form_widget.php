<?php

class PC_plugin_login_form_widget extends PC_plugin_login_widget {
	
	public $plugin_name = 'login';
	
	protected $_template_group = 'form';
	
	protected function _get_default_config() {
		return array(
			'redirect_url' => '',
		);
	}
	
	protected function _get_errors(&$errors_html) {
		$errors = array(
			'input_login' => array(),
			'input_email' => array(),
			'input_password' => array(),
		);
		if(isset($_POST['login']) && v($_POST['login_checker']) === "") { //prisijungimas
			/*
			$inputEmail = filter_input(INPUT_POST, 'user_login', FILTER_SANITIZE_EMAIL);
			if($inputEmail==''){
				$errors['input_email']['string'] = str_replace('%',$this->Get_variable('email'),$this->Get_variable('error_empty'));
				$errors['input_email']['TEXT']='<span class="help-block"><small>'.$errors['input_email']['string'].'</small></span>';
				$errors['input_email']['CLASS']='has-error';
			}
			*/
			
			$inputLogin = filter_input(INPUT_POST, 'user_login');
			if($inputLogin==''){
				$errors['input_login']['string'] = str_replace('%',$this->Get_variable('username'),$this->Get_variable('error_empty'));
				$errors['input_login']['TEXT']='<span class="help-block"><small>'.$errors['input_login']['string'].'</small></span>';
				$errors['input_login']['CLASS']='has-error';
			}
			
			$inputPassword = filter_input(INPUT_POST, 'user_password', FILTER_SANITIZE_SPECIAL_CHARS);
			if($inputPassword==''){
				$errors['input_password']['string'] = str_replace('%',$this->Get_variable('password'),$this->Get_variable('error_empty'));
				$errors['input_password']['TEXT']='<span class="help-block"><small>'.$errors['input_password']['string'].'</small></span>';
				$errors['input_password']['CLASS']='has-error';
			}

			if(count($errors)==0){
				$site_users->Login();

				if($site_users->login_error){
					$errors_html.='<span class="text-danger">'.$localization['error_bad_login'].'</span>';
				}else if(!$site_users->login_error && isset($_SESSION['user_password']) && $_SESSION['user_password'] != '' ){		
					//$core->Redirect_local($site->Get_link($BUTTON['PC_SHOP']['route']));
					//exit();
				}
			}
		}
		
		return $errors;
	}
	
	public function get_data() {
		if ($this->site_users and $this->site_users->just_logged_in and !empty($this->_config['redirect_url'])) {
			$this->core->Redirect_local($this->_config['redirect_url']);
		}
		$register_link = $this->site->Get_link_by_controller('site_users_registration');
		$pass_change_link = $this->site->Get_link_by_controller('site_users_pass_change');
		
		$errors_html = '';
		$data = array(
			'register_link' => $register_link,
			'pass_change_link' => $pass_change_link,
			'errors' => $this->_get_errors($errors_html),
			'$errors_html' => $errors_html,
		);
		return $data;
	}
	
	
}