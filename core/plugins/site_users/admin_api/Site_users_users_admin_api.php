<?php

class Site_users_users_admin_api extends PC_plugin_crud_admin_api {

	/**
	 *
	 * @var int
	 */
	public $product_id;
	
	protected $_valid_fields = array('email', 'login', 'name', 'password', 'banned');
	
	/**
	 * 
	 */
	protected function _set_plugin_name() {
		$this->_plugin_name = 'site_users';
	}
	
	protected function _get_model() {
		return $this->core->Get_object('PC_site_user_model');
	}
	
	/**
	 * Plugin access is being checked
	 */
	protected function _before_action() {
		$this->_check_plugin_access();
	}

	protected function _before_update(&$data, &$content) {
		v($data['banned'], '');
	}
	
	
	protected function _before_insert(&$data, &$content) {
		$now = time();
				
		$data['date_registered'] = $now;
		$data['last_seen'] = 0;
		$data['confirmation'] = '';
		$data['flags'] = PC_user::PC_UF_DEFAULT;
	}
	
	
	public function test() {
		$this->_out['test'] = 'Martynas';
	}

	
}

?>
