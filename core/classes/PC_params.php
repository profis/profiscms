<?php
class PC_params_errors {
	public $_list = array();
	public function Parse($errors) {
		if (!is_array($errors)) return false;
		if (count($errors)) {
			foreach ($errors as $k=>&$v) {
				$this->Add($k, $v);
			}
		}
		return true;
	}
	public function Add($error, $value) {
		$this->_list[$error] = $value;
		return true;
	}
	public function Get($index=null) {
		if (is_null($index)) return next($this->_list);
		if (isset($this->_list[$index])) return $this->_list[$index];
	}
	
	public function Get_all() {
		return $this->_list;
	}
	
	public function Has() {
		$args = func_get_args();
		if (count($args))
		foreach ($args as $err) if (in_array($err, $this->_list)) return true;
		return false;
	}
	public function Has_all() {
		$args = func_get_args();
		if (count($args)) {
			foreach ($args as $err) if (!in_array($err, $this->_list)) return false;
			return true;
		}
		else return false;
	}
	public function Count() {
		return count($this->_list);
	}
}

class PC_params {
	/** @var \PC_params_errors */
	public $errors = null;

	/** @var \PC_paging  */
	public $paging = null;

	public function __construct($params) {
		$this->errors = new PC_params_errors;
		if (is_array($params)) foreach ($params as $p=>&$v) {
			switch ($p) {
				case 'errors':
					$this->errors->Parse($params['errors']);
					break;
				case 'paging':
					if ($params['paging'] instanceof PC_paging) $this->paging =& $params['paging'];
					else {
						$params['paging'] = new PC_paging(v($params['paging']['page'], null), v($params['paging']['perPage'], 20), v($params['paging']['start'], null));
						$this->paging =& $params['paging'];
					}
					break;
				default: $this->{$p} = $v;
			}
		}
	}
	public function Set($key, $value) {
		$this->{$key} = $value;
	}
	public function Get($key) {
		if (!isset($this->{$key})) return false;
		return $this->{$key};
	}
	public function Has_paging() {
		if (!isset($this->paging)) return false;
		return ($this->paging instanceof PC_paging);
	}
}