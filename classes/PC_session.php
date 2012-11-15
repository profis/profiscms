<?php

class PC_session {
	
	protected $_key_prefix;

	/**
	 * 
	 * @param string $key_prefix
	 */
	public function __construct($key_prefix = '') {
		$this->_key_prefix = $key_prefix;
	}

	/**
	 * 
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value) {
		$_SESSION[$this->_key_prefix . $key] = $value;
	}
	
	/**
	 * 
	 * @param string $group
	 * @param string $key
	 * @param type $value
	 */
	public function set_in_group($group, $key, $value) {
		$_SESSION[$this->_key_prefix . $group][$key] = $value;
	}
	
	/**
	 * 
	 * @param string $key
	 * @return string
	 */
	public function get($key) {
		return $_SESSION[$this->_key_prefix . $key];
	}
	
	/**
	 * 
	 * @param string $group
	 * @param string $key
	 * @return type
	 */
	public function get_from_group($group, $key) {
		return $_SESSION[$this->_key_prefix . $group][$key];
	}
	
	/**
	 * 
	 * @param string $key
	 */
	public function delete($key) {
		unset($_SESSION[$this->_key_prefix . $key]);
	}
	
	/**
	 * 
	 * @param string $group
	 * @param string $key
	 */
	public function delete_from_group($group, $key) {
		unset($_SESSION[$this->_key_prefix . $group][$ey]);
	}
	
}

?>
