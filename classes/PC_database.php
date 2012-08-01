<?php
class PC_database_fields {
	private $_fields = array();
	public function Register($table, $field, $params=array()) {
		if (!isset($this->_fields[$table]) || !is_array($this->_fields[$table])) $this->_fields[$table] = array();
		$this->_fields[$table][$field] = $params;
		return true;
	}
	public function Register_list($table, $fields) {
		if (!is_array($this->_fields[$table])) $this->_fields[$table] = array();
		foreach ($fields as $field=>$params) {
			$this->_fields[$table][$field] = $params;
		}
		return true;
	}
	public function Exists($table, $field) {
		if (!is_array($this->_fields[$table])) return false;
		return isset($this->_fields[$table][$field]);
	}
	public function Validate($table, $field, &$value) {
		if (!$this->Exists($table, $field)) return false;
		$params = &$this->_fields[$table][$field];
		//if there is no custom validator for an existing field then always validate it positively
		if (!is_array($params['validator'])) return true;
		if (!is_callable($params['validator']['callback'])) return false;
		$args = array();
		if (is_array($params['validator']['args'])) {
			$args = $params['validator']['args'];
		}
		else if (isset($params['validator']['args'])) {
			$args = array($params['validator']['args']);
		}
		array_unshift($args, null);
		//replace that null element by reference to value
		$args[1] =& $value;
		$r = call_user_func_array($params['validator']['callback'], $args);
		return $r;
	}
	public function Parse($table, &$data, &$params) {
		$insertData = array();
		$s = true;
		foreach ($data as $field=>$value) {
			$r = $this->Validate($table, $field, $value);
			if ($r === true) {
				$insertData[$field] = $value;
			}
			else if ($this->Exists($table, $field)) {
				//egzistuoja bet ne validuoja - error!
				$params->errors->Add($field, $r);
				$s = false;
			}
		}
		return ($s?$insertData:false);
	}
}
final class PC_database extends PDO {
	public function __construct() {
		$args = func_get_args();
		call_user_func_array(array('parent', '__construct'), $args);
		$this->fields = new PC_database_fields;
	}
	/* public function Select() {}
	public function Insert() {}
	public function Delete() {}
	public function Update() {} */
}
/* class PC_database_statement {
	public $statement;
	protected $constructed;
	private $_joins = array();
	public function Join($table, $side=null) {
		$this->_joins[] = array(
		);
		return true;
	}
	public function __construct($from, $join, $where, $order, $group, $limit) {
		global $core;
		$this->db = $core->db;
	}
	public function where($expr) {}
	public function order($expr) {}
	public function group($expr) {}
	public function limit($expr) {}
	public function construct($expr) {
		$this->statement = $this->db->prepare("");
	}
	public function execute($params=array()) {
		if (!$this->constructed) $this->construct();
		return $this->statement->execute($params);
	}
}*/