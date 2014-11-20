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

	protected function getMetaFields() {
		global $cfg;
		return isset($cfg['site_users']['admin_editable_meta']) ? (is_array($cfg['site_users']['admin_editable_meta']) ? $cfg['site_users']['admin_editable_meta'] : array_map('trim', explode(',', $cfg['site_users']['admin_editable_meta']))) : array();
	}
	
	public function test() {
		$this->_out['test'] = 'Martynas';
	}


	public function create() {
		global $core;

		$data = json_decode(v($_POST['data'], '{}'), true);

		parent::create();

		if( $this->_out['success'] ) {
			$metaFields = $this->getMetaFields();
			if( !empty($metaFields) ) {
				$userId = $this->_out['id'];
				$metaData = array();
				foreach( $metaFields as $fieldName ) {
					if( isset($data['other'][$k = 'meta_' . $fieldName]) ) {
						$metaData[$fieldName] = $this->_out[$k] = $data['other'][$k];
					}
				}
				if( !empty($metaData) ) {
					/** @var PC_user $users */
					$users = $core->Get_object('PC_user');
					$users->Set_meta_data($metaData, $userId);
				}
			}
		}
	}

	public function get() {
		global $core;
		$callback = null;
		if( isset($GET['callback']) ) {
			$callback = $GET['callback'];
			unset($GET['callback']);
		}
		parent::get();

		$metaFields = $this->getMetaFields();
		if( !empty($metaFields) ) {
			/** @var PC_user $users */
			$users = $core->Get_object('PC_user');
			foreach( $this->_out['list'] as &$record ) {
				$meta = $users->Get_meta_data($metaFields, $record['id']);
				foreach( $meta as $k => $v ) {
					$record['meta_' . $k] = $v;
				}
			}
		}

		if( $callback ) {
			echo $callback . "(" . json_encode($this->_out) . ")";
			exit;
		}
	}

	public function edit() {
		global $core;

		$data = json_decode(v($_POST['data'], '{}'), true);
		// remove password from saved data if it's empty
		if( isset($data['other']['password'], $data['other']['password2']) && $data['other']['password'] == '' && $data['other']['password2'] == '' ) {
			unset($data['other']['password'], $data['other']['password2']);
			$_POST['data'] = json_encode($data);
		}

		parent::edit();

		if( $this->_out['success'] ) {
			$metaFields = $this->getMetaFields();
			if( !empty($metaFields) ) {
				$userId = intval($_POST['id']);
				$metaData = array();
				foreach( $metaFields as $fieldName ) {
					if( isset($data['other'][$k = 'meta_' . $fieldName]) )
						$metaData[$fieldName] = $data['other'][$k];
				}
				if( !empty($metaData) ) {
					/** @var PC_user $users */
					$users = $core->Get_object('PC_user');
					$users->Set_meta_data($metaData, $userId);
				}
			}
		}
	}
}

?>
