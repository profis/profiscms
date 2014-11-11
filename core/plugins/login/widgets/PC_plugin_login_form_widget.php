<?php

class PC_plugin_login_form_widget extends PC_plugin_login_widget {
	
	public $plugin_name = 'login';
	
	protected $_template_group = 'form';
	
	protected function _get_default_config() {
		return array(
			'redirect_url' => '',
		);
	}
	
	public function get_data() {
		if ($this->site_users->just_logged_in and !empty($this->_config['redirect_url']))
			$this->core->Redirect_local($this->_config['redirect_url']);

		$p = $this->page->Get_page('registration', false, true);
		$register_link = $p ? $this->page->Get_page_link_from_data($p) : null;

		$p = $this->page->Get_page('change_password', false, true);
		$pass_change_link = $p ? $this->page->Get_page_link_from_data($p) : null;

		$p = $this->page->Get_page('remind_username', false, true);
		$remind_username_link = $p ? $this->page->Get_page_link_from_data($p) : null;

		$errors_html = empty($this->site_users->login_error) ? '' : qlang('error_bad_login');

		$data = array(
			'register_link' => $register_link,
			'pass_change_link' => $pass_change_link,
			'remind_username_link' => $remind_username_link,
			'errors_html' => $errors_html,
		);
		return $data;
	}
	
	
}