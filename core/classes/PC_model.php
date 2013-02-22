<?php

abstract class PC_model extends PC_base{
	protected $_table = '';
	protected $_table_id_col = 'id';
	protected $_table_parent_col = 'pid';
	protected $_content_table = '';
	protected $_content_table_relation_col = '';
	protected $_content_table_ln_col = 'ln'; 
	protected $_content_table_name_col = 'name';
	
	protected $_where = array();
	protected $_query_params = array();
	
	abstract protected function _set_tables();
	
	
	public function Init() {
		$this->_set_tables();
	}
	
	public function clear_scope() {
		$this->_where = array();
		$this->_query_params = array();
	}
	
	public function get_scope() {
		return array(
			'where' => $this->_where,
			'query_params' => $this->_query_params
		);
	}
	
	public function set_scope(array $scope) {
		$this->_where = $scope['where'];
		$this->_query_params = $scope['query_params'];
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
		$this->debug('get_all()');
		$this->debug($params, 1);
		return $this->get_data(null, $params);
	}
	
	public function get_data($id = null, $params = array(), $limit = 0) {
		$this->debug('get_data()');
		$this->debug($params, 1);
		$select = 't.*';
		$join_cc = '';
		$select_cc = '';
		
		$query_params = array();
		
		if (v($params['select'])) {
			$select = $params['select'];
		}
		
		$explode_fields = array();
		
		if (v($params['content'])) {
			$join_cc_ln = '';
			$select_cc = 'ct.*';
			if (is_array($params['content']) and v($params['content']['select'])) {
				$select_cc = $params['content']['select'];
			}
			$ln = $this->site->ln;
			if (isset($params['ln'])) {
				$ln = $params['ln'];
			}
			if ($ln) {
				$query_params[] = $ln;
				$join_cc_ln = " and ct.{$this->_content_table_ln_col}=? ";
			}
			else {
				if (v($params['group'])) {
					$params['group'] = ', ' . $params['group'];
				}
				$params['group'] = 't.id' . $params['group'];
				$select_cc_array = $select_cc;
				$select_cc = '';
				if (!is_array($select_cc_array)) {
					$select_cc_array = explode(',', $select_cc_array);
				}
				foreach ($select_cc_array as $key => $c_field) {
					list ($table, $alias) = explode('.', $c_field);
					$alias .= 's';
					$explode_fields[] = $alias;
					$select_cc_array[$key] = $this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'ct.ln', $c_field), array('separator'=>'▓', 'distinct'=> true))." " . $alias;
				}
				$select_cc = implode(', ', $select_cc_array);
			}
			if (!empty($select_cc)) {
				$select_cc = ', ' . $select_cc;
			}
			$join_cc = " LEFT JOIN {$this->db_prefix}{$this->_content_table} ct ON ct.{$this->_content_table_relation_col}=t.id " . $join_cc_ln;
		}
		
		$query_params = array_merge($query_params, $this->_query_params);
		
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
		
		if (!isset($params['where'])) {
			$params['where'] = array();
		}
		if (isset($params['where'])) {
			$additional_where = '';
			if (!is_array($params['where'])) {
				$params['where'] = array($params['where']);
			}
			$params['where'] = array_merge($this->_where, $params['where']);
			if (is_array($params['where'])) {
				$where_strings = array();
				foreach ($params['where'] as $key => $value) {
					if (!is_array($value)) {
						$where_strings[] = $value;
					}
					else {
						$where_strings[] = " $key " . $this->sql_parser->in($value);
						$query_params = array_merge($query_params, $value);
					}
				}
				$additional_where = implode(' AND ', $where_strings);
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
			if (isset($params['order_dir'])) {
				$order_s .= ' ' . $params['order_dir'];
			}
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
		$first_key = false;
		while ($d = $r_categories->fetch()) {
			if (!$first_key) {
				$first_key = 0;
			}
			
			if (!empty($explode_fields)) {
				foreach ($explode_fields as $key => $value) {
					$this->core->Parse_data_str($d[$value], '▓', '░');
				}
			}
			if (isset($params['key']) and isset($d[$params['key']]) and !empty($d[$params['key']])) {
				$items[$d[$params['key']]] = $d;
			}
			else {
				$items[] = $d;
			}
			
		}
		
		if ($limit == 1 and count($items) == 1) {
			return $items[0];
		}
		return $items;
	}
	
	protected function _get_where_clause($where, &$query_params) {
		$where_clause = '';
		if (is_array($where)) {
			$where_strings = array();
			foreach ($where as $key => $value) {
				if (!is_array($value)) {
					if (is_string($key)) {
						$where_strings[] = "$key = ? ";
						$query_params[] = $value;
					}
					else {
						$where_strings[] = $value;
					}
					
				}
				else {
					$where_strings[] = " $key " . $this->sql_parser->in($value);
					$query_params = array_merge($query_params, $value);
				}
			}
			$where_clause = implode(' AND ', $where_strings);
		}
		else {
			$where_clause = $where;
		}
		return $where_clause;
	}
	
	public function get_parent_id($id) {
		$params = array();
		$data = $this->get_data($id, $params, 1);
		if ($data and isset($data[$this->_table_parent_col])) {
			return $data[$this->_table_parent_col];
		}
		return false;
	}
	
	public function update(array $data, $params = array()) {
		$this->debug('update()');
		$this->debug($data, 1);
		$this->debug($params, 1);
		$entity_id = false;
		if (!is_array($params)) {
			$entity_id = $params;
			$params = array();
		}
		elseif(isset($params['where']) and isset($params['where']['id']) and !is_array($params['where']['id'])) {
			$entity_id = $params['where']['id'];
		}
		
		$content = array();
		if (isset($data['_content'])) {
			$content = $data['_content'];
			unset($data['_content']);
		}
		$query_params = array_values($data);
		$sets = array();
		foreach ($data as $key => $value) {
			$sets[] = "$key = ?";
		}
				
		$limit_s = '';
		if (v($params['limit'])) {
			$limit_s = ' LIMIT ' . $params['limit'];
		}
		
		$where_s = '';
		if (v($params['where'])) {
			if (v($params['query_params']) and is_array($params['query_params'])) {
				$query_params = array_merge($query_params, $params['query_params']);
			}
			$where_s = $this->_get_where_clause($params['where'], $query_params);
		}
		
		if (!empty($where_s)) {
			$where_s = ' WHERE ' . $where_s;
		}
		$sets_s = implode(',', $sets);
		$query = "UPDATE {$this->db_prefix}{$this->_table} SET $sets_s
			$where_s $limit_s";
		$r = $this->prepare($query);

		$this->debug_query($query, $query_params, 1);
		
		$edited = $r->execute($query_params);
		
		
		$this->debug('-', 9);$this->debug($entity_id, 10);
		$this->debug('-', 9);$this->debug($edited, 10);
		$this->debug('-', 9);$this->debug(!empty($content), 10);
		$this->debug('-', 9);$this->debug(is_array($content), 10);
		$this->debug('-', 9);$this->debug(!empty($this->_content_table), 10);
		
		if ($entity_id and $edited and !empty($content) and is_array($content) and !empty($this->_content_table)) {
			foreach ($content as $ln => $ln_values) {
				$sets = array();
				foreach ($ln_values as $key => $value) {
					$sets[] = "$key = ?";
				}
				$sets_s = implode(',', $sets);
				$query = "UPDATE {$this->db_prefix}{$this->_content_table} SET $sets_s WHERE $this->_content_table_relation_col = ? AND $this->_content_table_ln_col = ?";
				$query_params = array_merge(array_values($ln_values), array($entity_id, $ln));
				$r = $this->prepare($query);
				$this->debug_query($query, $query_params, 2);
				$s = $r->execute($query_params);
			}
		}
		return $edited;
	}
	
	public function insert(array $data, array $content = array()) {
		$this->debug('insert()');
		$this->debug($data);
		$count = count($data);
		if (!$count) {
			return false;
		}
		$fields = implode(',', array_keys($data));
		$values = implode(',', array_fill(0, $count, '?'));
		$data = array_values($data);
		$query = "INSERT INTO {$this->db_prefix}{$this->_table} ($fields) VALUES ($values)";
		$r = $this->prepare($query);
		$this->debug_query($query, $data, 1);
		
		$s = $r->execute($data);
		if (!$s) {
			return false;
		}
		$id = $this->db->lastInsertId($this->sql_parser->Get_sequence($this->_table));
		
		if ($id and !empty($content)) {
			foreach ($content as $ln => $ln_values) {
				$count = count($ln_values);
				$fields = implode(',', array_keys($ln_values));
				$values = implode(',', array_fill(0, $count + 2, '?'));
				$query = "INSERT INTO {$this->db_prefix}{$this->_content_table} ($this->_content_table_relation_col, $this->_content_table_ln_col, $fields) VALUES ($values)";
				$query_params = array_merge(array($id, $ln), array_values($ln_values));
				$r = $this->prepare($query);
				$this->debug_query($query, $query_params, 2);
				$s = $r->execute($query_params);
			}
		}
				
		return $id;
		
	}
	
	public function delete($params = array()) {
		$query_params = array();
		
		$limit_s = '';
		if (v($params['limit'])) {
			$limit_s = ' LIMIT ' . $params['limit'];
		}
		
		$where_s = '';
		if (v($params['where'])) {
			if (v($params['query_params']) and is_array($params['query_params'])) {
				$query_params = array_merge($query_params, $params['query_params']);
			}
			$where_s = $this->_get_where_clause($params['where'], $query_params);
		}
		
		if (!empty($where_s)) {
			$where_s = ' WHERE ' . $where_s;
		}
		
		$query = "DELETE FROM {$this->db_prefix}{$this->_table} $where_s $limit_s";
		$r = $this->prepare($query);
		$this->debug_query($query, $query_params, 1);
		
		$deleted = $s = $r->execute($query_params);
		
		if ($s and !empty($this->_content_table) and isset($params['where']) and isset($params['where']['id'])) {
			$where = array(
				$this->_content_table_relation_col => $params['where']['id']
			);
			$query_params = array();
			$where_s = $this->_get_where_clause($where, $query_params);
			if (!empty($where_s)) {
				$where_s = ' WHERE ' . $where_s;
			}
			$query = "DELETE FROM {$this->db_prefix}{$this->_content_table} $where_s";
			$r = $this->prepare($query);
			$this->debug_query($query, $query_params, 2);
			$s = $r->execute($query_params);
		}
		return $deleted;
	}
}

?>
