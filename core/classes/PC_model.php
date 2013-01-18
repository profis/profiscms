<?php

abstract class PC_model extends PC_base{
	protected $_table = '';
	protected $_table_id_col = 'id';
	protected $_table_parent_col = 'pid';
	protected $_content_table = '';
	protected $_content_table_relation_col = '';
	protected $_content_table_ln_col = 'ln'; 
	protected $_content_table_name_col = 'name';
	
	abstract protected function _set_tables();
	
	
	public function Init() {
		$this->_set_tables();
	}
	
	
	public function get_id_from_content($name, $value, $ln = '', $limit = 1) {
		$join = '';
		$limit_s = '';
		if ($limit > 0) {
			$limit_s = ' LIMIT ' . $limit;
		}
		if (empty($ln)) {
			$ln = $this->site->ln;
		}
		$query = "SELECT attribute_id FROM {$this->db_prefix}{$this->_content_table} ct $join 
			WHERE ct.$name = ? AND ct.$this->_content_table_ln_col = ?
			" . $limit_s;
		$r_category = $this->prepare($query);
		
		$queryParams[] = $value;
		$queryParams[] = $ln;

		$this->debug_query($query, $queryParams, 1);
		
		$s = $r_category->execute($queryParams);
		if (!$s) {
			$this->debug(':(', 2);
			return false;
		}

		if ($d = $r_category->fetchColumn()) {
			$this->debug(':)', 2);
			return $d;
		}
		return false;
	}
	
	public function get_id_from_name($value, $ln = '', $limit = 1) {
		return $this->get_id_from_content($this->_content_table_name_col, $value, $ln, $limit);
	}
	
	public function get_id_from_field($name, $value, $limit = 1) {
		$this->debug("get_id_from_field($name, $value)");
		$memstore_group = 'get_id_from_field: ' . $this->_table;
		$memstore_key =  $name . $value . $limit;
		$stored =& $this->memstore->Get($memstore_group, $memstore_key);
		if ($stored) {
			$this->debug('value found in memstore', 1);
			return $stored;
		}
		
		$join = '';
		$query = "SELECT t.$this->_table_id_col FROM {$this->db_prefix}{$this->_table} t $join 
			WHERE t.$name = ? LIMIT 1";
		$r_category = $this->prepare($query);
		
		$queryParams[] = $value;

		$this->debug_query($query, $queryParams, 1);
		
		$s = $r_category->execute($queryParams);
		if (!$s) return false;

		if ($d = $r_category->fetchColumn()) {
			$this->memstore->Cache(array($memstore_group, $memstore_key), $d);
			return $d;
		}
		return false;
	}
	
	public function get_all($params = array()) {
		return $this->get_data(null, $params);
	}
	
	public function get_data($id = null, $params = array(), $limit = 0) {
		$select = 't.*';
		$join_cc = '';
		$select_cc = '';
		
		$query_params = array();
		
		if (v($params['select'])) {
			$select = $params['select'];
		}
		
		if (v($params['content'])) {
			$join_cc = " LEFT JOIN {$this->db_prefix}{$this->_content_table} ct ON ct.{$this->_content_table_relation_col}=t.id and ct.{$this->_content_table_ln_col}=? ";
			$select_cc = ', ct.*';
			if (is_array($params['content']) and v($params['content']['select'])) {
				$select_cc = ', ' . $params['content']['select'];
			}
			$ln = $this->site->ln;
			if (isset($params['ln'])) {
				$ln = $params['ln'];
			}
			$query_params[] = $ln;
		}
		
		$where_s = '';
		
		if (!is_null($id)) {
			if (!is_array($id)) {
			$where_s .= ' t.id = ? ';
				$query_params[] = $id;
				$limit = 1;
			}
			else {
				$where_s .= ' t.id ' . $this->sql_parser->in($id);
				$query_params = array_merge($query_params, $id);
			}
		}
		
		if (v($params['query_params']) and is_array($params['query_params'])) {
			$query_params = array_merge($query_params, $params['query_params']);
		}
		
		if (isset($params['where'])) {
			$additional_where = '';
			if (is_array($params['where'])) {
				foreach ($params['where'] as $key => $value) {
					
				}
				$additional_where = implode(' AND ', $params['where']);
			}
			else {
				$additional_where = $params['where'];
			}
			if (!empty($additional_where)) {
				if (!empty($where_s)) {
					$where_s .= ' AND ';
				}
				$where_s .= $additional_where;
			}
		}
		
		$limit_s = '';
		if (v($params['limit'])) {
			$limit = $params['limit'];
		}
		if ($limit > 0) {
			$limit_s = " LIMIT $limit";
		}
		
		$join = '';
		if (isset($params['join'])) {
			if (is_array($params['join'])) {
				$join = implode(' ', $params['join']);
			}
			else {
				$join = $params['join'];
			}
		}
		$group_s = '';
		if (isset($params['group'])) {
			$group_s = 'GROUP BY ' . $params['group'];
		}
		
		$order_s = '';
		if (isset($params['order'])) {
			$order_s = 'ORDER BY ' . $params['order'];
		}
		
		if (!empty($where_s)) {
			$where_s = ' WHERE ' . $where_s;
		}
		
		$query = "SELECT {$select}{$select_cc} FROM {$this->db_prefix}{$this->_table} t $join_cc $join
			$where_s $group_s $order_s $limit_s";
		$r_categories = $this->prepare($query);

		$this->debug_query($query, $query_params, 1);
		
		//echo "\n";
		//echo $this->get_debug_query_string($query, $query_params);
		
		$s = $r_categories->execute($query_params);
		if (!$s) return false;

		$items = array();
		while ($d = $r_categories->fetch()) {
			$items[] = $d;
		}
		if ($limit == 1 and count($items) == 1) {
			return $items[0];
		}
		return $items;
	}
	
	public function get_parent_id($id) {
		$params = array();
		$data = $this->get_data($id, $params, 1);
		if ($data and isset($data[$this->_table_parent_col])) {
			return $data[$this->_table_parent_col];
		}
		return false;
	}
	
}

?>
