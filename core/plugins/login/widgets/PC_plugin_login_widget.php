<?php

abstract class PC_plugin_login_widget extends PC_widget {
	
	public $plugin_name = 'login';
	
	public $site_users;
	
	public function Init($config = array()) {
		parent::Init($config);
		if (strpos($this->_template_group, ':_plugin/') === false) {
			$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
		}
		global $site_users;
		$this->site_users = $site_users;
	}
	
	public function Get_variable($var) {
		return $this->core->Get_plugin_variable($var, $this->plugin_name);
	}
	
}