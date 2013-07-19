<?php

class PC_plugin_api extends PC_base {
	
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
	
	
	/**
	 * 
	 * @param Page_manager $page_manager
	 */
	function Init() {
		$this->_out = array();
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
	 * @return boolean returns true if api request was processed
	 */
	public function process() {
		$method = $this->routes->Get(2);
		if (empty($method)) {
			$method = 'default_action';
		}
		$method = str_replace('-', '_', $method);
		$this->debug('Method: ' . $method);
		$this->_method = $method;
		if (method_exists($this, $this->_method)) {
			try {
				$args = func_get_args();
				call_user_func_array(array($this, $method), $args);
			}	
			catch (Exception $e) {
				$this->_out = array(
					'error' => 'Unknown exception has been caught!',
					'message' => $e->getMessage()
				);
			}
			return true;
		}
		return false;
	}
	
}