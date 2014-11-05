<?php

use \Profis\Db\DbException;
use \Profis\Db\DbTransactionException;

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
		$args[0] =& $value;
		$r = call_user_func_array($params['validator']['callback'], $args);
		return $r;
	}
	public function Parse($table, &$data, &$params) {
		$insertData = array();
		$s = true;
		if (count($data)) foreach ($data as $field=>$value) {
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
		$this->query("SET SQL_BIG_SELECTS=1"); // needed since sometimes servers have low MAX_JOIN_SIZE which can make PC_gallery::Get_categories() malfunction.
	}
	
	
	public function get_flag_query_condition($flag, &$query_params = array(), $col = 'flags', $table = '', $op = '=') {
		$flag_number = $flag;
		if (strpos($flag, '0x') !== false) {
			$flag_number = substr($flag_number, 2);
		}
		if (!empty($table)) {
			$table .= '.';
		}
		$cond = "({$table}$col & ?) $op ?";
		$query_params[] = $flag_number;
		$query_params[] = $flag_number;
		return $cond;
	}

	/**
	 * Retrieves all available information on table from the database schema and returns it in an associative array.
	 *
	 * @param string $tableName name of the table to get information on. Must be without prefix.
	 * @return array An associative array containing information about the table.
	 * - 'TABLE_CATALOG'
	 * - 'TABLE_SCHEMA'
	 * - 'TABLE_NAME'
	 * - 'TABLE_TYPE'
	 * - 'ENGINE'
	 * - 'VERSION'
	 * - 'ROW_FORMAT'
	 * - 'TABLE_ROWS'
	 * - 'AVG_ROW_LENGTH'
	 * - 'DATA_LENGTH'
	 * - 'MAX_DATA_LENGTH'
	 * - 'INDEX_LENGTH'
	 * - 'DATA_FREE'
	 * - 'AUTO_INCREMENT'
	 * - 'CREATE_TIME'
	 * - 'UPDATE_TIME'
	 * - 'CHECK_TIME'
	 * - 'TABLE_COLLATION'
	 * - 'CHECKSUM'
	 * - 'CREATE_OPTIONS'
	 * - 'TABLE_COMMENT'
	 *
	 * @throws DbException
	 */
	function getTableInfo($tableName) {
		global $cfg, $core;
		$s = $this->prepare($q = "SELECT * FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = :db AND `TABLE_NAME` = :table");
		$p = array(
			'db' => $cfg['db']['name'],
			'table' => $core->db_prefix . $tableName,
		);
		if( !$s->execute($p) )
			throw new DbException($s->errorInfo(), $q, $p);
		return $s->fetch();
	}

	/**
	 * Retrieves all available information on column from the database schema and returns it in an associative array.
	 *
	 * @param string $tableName Name of the table without prefix.
	 * @param string $columnName Name of the column to get information on.
	 * @return array An associative array containing information about the column.
	 * - 'TABLE_CATALOG'
	 * - 'TABLE_SCHEMA'
	 * - 'TABLE_NAME'
	 * - 'COLUMN_NAME'
	 * - 'ORDINAL_POSITION'
	 * - 'COLUMN_DEFAULT'
	 * - 'IS_NULLABLE'
	 * - 'DATA_TYPE'
	 * - 'CHARACTER_MAXIMUM_LENGTH'
	 * - 'CHARACTER_OCTET_LENGTH'
	 * - 'NUMERIC_PRECISION'
	 * - 'NUMERIC_SCALE'
	 * - 'CHARACTER_SET_NAME'
	 * - 'COLLATION_NAME'
	 * - 'COLUMN_TYPE'
	 * - 'COLUMN_KEY'
	 * - 'EXTRA'
	 * - 'PRIVILEGES'
	 * - 'COLUMN_COMMENT'
	 *
	 * @throws DbException
	 */
	function getColumnInfo($tableName, $columnName) {
		global $cfg, $core;
		$s = $this->prepare($q = "SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = :db AND `TABLE_NAME` = :table AND `COLUMN_NAME` = :column");
		$p = array(
			'db' => $cfg['db']['name'],
			'table' => $core->db_prefix . $tableName,
			'column' => $columnName,
		);
		if( !$s->execute($p) )
			throw new DbException($s->errorInfo(), $q, $p);
		return $s->fetch();
	}

	private $transactionNestingLevel = 0;

	public function beginTransaction() {
		$q = null;
		if( $this->transactionNestingLevel == 0 )
			$result = parent::beginTransaction();
		else
			$result = $this->exec($q = "SAVEPOINT LEVEL{$this->transactionNestingLevel}");
		if( !$result )
			throw new DbTransactionException($this->errorInfo(), $q, null, "Could not begin a transaction");
		$this->transactionNestingLevel++;
	}

	public function commit() {
		if( $this->transactionNestingLevel == 0 )
			throw new DbTransactionException(null, null, null, "Inconsistent number of beginTransaction() and commit() / rollBack() calls. Please check your code so that only one commit() or rollBack() method is called per transaction that was started with beginTransaction().");
		$this->transactionNestingLevel--;
		$q = null;
		if( $this->transactionNestingLevel == 0 )
			$result = parent::commit();
		else
			$result = $this->exec($q = "RELEASE SAVEPOINT LEVEL{$this->transactionNestingLevel}");
		if( !$result )
			throw new DbTransactionException($this->errorInfo(), $q, null, "Could not commit a transaction");
	}

	public function rollBack() {
		if( $this->transactionNestingLevel == 0 )
			throw new DbTransactionException(null, null, null, "Inconsistent number of beginTransaction() and commit() / rollBack() calls. Please check your code so that only one commit() or rollBack() method is called per transaction that was started with beginTransaction().");
		$this->transactionNestingLevel--;
		$q = null;
		if ($this->transactionNestingLevel == 0)
			$result = parent::rollBack();
		else
			$result = $this->exec($q = "ROLLBACK TO SAVEPOINT LEVEL{$this->transactionNestingLevel}");
		if( !$result )
			throw new DbTransactionException($this->errorInfo(), $q, null, "Could not roll back a transaction");
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