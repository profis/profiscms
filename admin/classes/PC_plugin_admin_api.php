<?php

class PC_plugin_admin_api_access_exception extends Exception{}

class PC_plugin_admin_api_page_access_exception extends PC_plugin_admin_api_access_exception{}
class PC_plugin_admin_api_superadmin_access_exception extends PC_plugin_admin_api_access_exception{}
class PC_plugin_admin_api_plugin_access_exception extends PC_plugin_admin_api_access_exception{}

abstract class PC_plugin_admin_api extends PC_base{
	
	/**
	 *
	 * @var string
	 */
	protected $_plugin_name;
	
	/**
	 *
	 * @var Page_manager
	 */
	protected $_page_manager;
	
	/**
	 *
	 * @var array
	 */
	protected $_out;
	
	/**
	 *
	 * @var string
	 */
	protected $_method;
	
	
	abstract protected function _set_plugin_name();
	
	/**
	 * 
	 * @param Page_manager $page_manager
	 */
	function Init(Page_manager $page_manager) {
		$this->_page_manager = $page_manager;
		$this->_set_plugin_name();
	}

	/**
	 * 
	 * @return array
	 */
	public function get_output() {
		return $this->_out;
	}
	
	/**
	 * 
	 * @param type $page_id
	 * @throws PC_plugin_admin_api_page_access_exception
	 */
	protected function _check_page_access($page_id) {
		if (!$this->_page_manager->is_node_accessible($page_id)) {
			throw new PC_plugin_admin_api_page_access_exception('Access to page ' . $page_id . ' denied!');
		}
	}
	
	/**
	 * 
	 * @throws PC_plugin_admin_api_plugin_access_exception
	 */
	protected function _check_plugin_access() {
		if (!$this->auth->Authorize_access_to_plugin($this->_plugin_name)) {
			throw new PC_plugin_admin_api_plugin_access_exception('Access to plugin ' . $this->_plugin_name . ' denied!');
		}
	}
	
	
	/**
	 * 
	 * @throws PC_plugin_admin_api_superadmin_access_exception
	 */
	protected function _check_superadmin_access() {
		if (false) {
			throw new PC_plugin_admin_api_superadmin_access_exception('Uses has not superadmin access!');
		}
	}
	
	
	protected function _before_action() {
		
	}
	
	protected function _after_action() {
		
	}
	
	protected function _after_action_success() {
		
	}
	
	
	/**
	 * 
	 * @return boolean returns true if api request was processed
	 */
	public function process() {
		$method = v($this->routes->Get(2));
		if (empty($method)) {
			$method = 'default_action';
		}
		$method = str_replace('-', '_', $method);
		$this->debug('Method: ' . $method);
		$this->_method = $method;
		if (method_exists($this, $this->_method)) {
			try {
				$before_method = '_before_action';
				if (method_exists($this, $before_method)) {
					$this->$before_method();
				}
				//$this->$method();
				call_user_func_array(array($this, $method), func_get_args());
				$after_method = '_after_action';
				if (method_exists($this, $after_method)) {
					$this->$after_method();
				}
				$after_success_method = '_after_action_success';
				if (isset($this->_out['success']) and method_exists($this, $after_success_method)) {
					$this->$after_success_method();
				}
			}
			catch (Exception $e) {
				if ($e instanceof PC_plugin_admin_api_access_exception) {
					$this->_out = array(
						'error' => 'PC_plugin_admin_api_access_exception has been caught!',
						'message' => $e->getMessage()
					);
				}
				else {
					$this->_out = array(
						'error' => 'Unknown exception has been caught!',
						'message' => $e->getMessage()
					);
				}
			}
			return true;
		}
		
		return false;
	}
	
}

?>
