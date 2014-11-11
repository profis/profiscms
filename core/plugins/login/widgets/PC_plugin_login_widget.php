<?php

abstract class PC_plugin_login_widget extends PC_widget {
	
	public $plugin_name = 'login';

	/** @var PC_user $site_users */
	public $site_users = null;
	
	public function Init($config = array()) {
		parent::Init($config);
		if (strpos($this->_template_group, ':_plugin/') === false) {
			$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
		}
		$this->site_users = $this->core->Get_object('PC_user');
	}
	
	public function Get_variable($var) {
		return $this->core->Get_plugin_variable($var, $this->plugin_name);
	}
	
}