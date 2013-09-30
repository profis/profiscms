<?php

abstract class PC_model extends PC_base{
	protected $_id;
	protected $_table = '';
	protected $_table_id_col = 'id';
	protected $_table_parent_col = 'pid';
	
	protected $_table_position_col = 'position';
	
	protected $_content_table = '';
	protected $_content_table_relation_col = '';
	protected $_content_table_ln_col = 'ln'; 
	protected $_content_table_name_col = 'name';
	
	protected $_where = array();
	protected $_query_params = array();
	
	protected $_scope_where = array();
	protected $_scope_query_params = array();
	
	
	protected $_rules;
	protected $_filters;
	protected $_sanitize_filters;
	
	abstract protected function _set_tables();
	
	public function get_content_table() {
		return $this->_content_table;
	}
		
	public function get_table_position_col() {
		return $this->_table_position_col;
	}

	public function get_id_field() {
		return $this->_table_id_col;
	}
	
	protected function _set_rules() {
		$this->_rules = array();
	}
	
	protected function _set_filters() {
		$this->_filters = array();
	}
	
	protected function _set_sanitize_filters() {
		$this->_sanitize_filters = array();
	}
	
	protected function _set_base_scope() {
		
	}
	
	public function Init($id = 0) {
		$this->set_id($id);
		$this->_set_tables();
		$this->_set_rules();
		$this->_set_filters();
		$this->_set_sanitize_filters();
		$this->_set_base_scope();
		$this->clear_scope();
	}
	
	public function get_id() {
		return $this->_id;
	}

	public function set_id($id) {
		$this->_id = $id;
	}
	
	public function clear_scope() {
		$this->_where = $this->_scope_where;
		$this->_query_params = $this->_scope_query_params;
	}
	
	public static function create_scope() {
		return array(
			'where' => array(),
			'query_params' => array()
		);
	}
	
	public function get_scope() {
		return array(
			'where' => $this->_where,
			'query_params' => $this->_query_params
		);
	}
	
	public function set_scope(array $scope) {
		$this->_where = $scope['where'];
		$this->_query_params = v($scope['query_params'], array());
	}
	
	public static function absorb_scope_into_params(&$params, $scope = array()) {
		vv($params['where'], array());
		if (is_array($params['where']) and isset($scope['where'])) {
			$params['where'] = array_merge($params['where'], $scope['where']);
		}
		vv($params['query_params'], array());
		if (is_array($params['query_params']) and isset($scope['query_params'])) {
			$params['query_params'] = array_merge($params['query_params'], $scope['query_params']);
		}
	}
	
	public static function change_scope($scope, $replace = array()) {
		if (isset($scope['where']) and is_array($scope['where'])) {
			$patterns = array();
			$replacements = array();
			foreach ($replace as $old_table => $new_table) {
				$patterns[] = '/(?<!\w)'.$old_table.'./ui';
				$replacements[] = $new_table . '.';
			}
			foreach ($scope['where'] as $key => $value) {
				$value = preg_replace($patterns, $replacements, $value);
				$scope['where'][$key] = $value;
			}
			
		}
		
		return $scope;
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
	
	public function get_one($params = array(), $params_2 = array()) {
		if (!is_array($params)) {
			$id = $params;
			$params = $params_2;
			$params['limit'] = 1;
			return $this->get_data($id, $params);
		}
		else {
			$params['limit'] = 1;
			return $this->get_all($params);
		}
		
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
			if (is_array($params['content']) and isset($params['content']['select'])) {
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
					$alias = '';
					$table = $c_field;
					if (strpos($c_field, '.')) {
						list ($table, $alias) = explode('.', $c_field);
					}
					if (empty($alias)) {
						$alias = $c_field;
					}
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
		if (!isset($params['where'])) {
			$params['where'] = array();
		}
		if (!is_array($params['where'])) {
			$params['where'] = array($params['where']);
		}
		//$this->debug($params['where']);
		if (!is_null($id)) {
			$params['where'] = array_merge(array('t.id' => $id), $params['where']);
			if (!is_array($id)) {
				$params['limit'] = 1;
			}
			
		}
		
		$params['where'] = array_merge($this->_where, $params['where']);
				
		if (v($params['query_params']) and is_array($params['query_params'])) {
			$query_params = array_merge($query_params, $params['query_params']);
		}
		
		
		/*
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
		*/
		
		//print_r($params['where']);
		//print_r($query_params);
		
		
		$where_s = $this->_get_where_clause($params['where'], $query_params);
		
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
			if (isset($params['join_params'])) {
				$query_params = array_merge($query_params, $params['join_params']);
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
		
		$paging = false;
		if (isset($params['paging']) and is_array($params['paging']) and isset($params['paging']['perPage'])) {
			$paging = true;
			$params['paging'] = new PC_paging(v($params['paging']['page'], null), v($params['paging']['perPage'], 20), v($params['paging']['start'], null));
			$limit_s = " LIMIT {$params['paging']->Get_offset()},{$params['paging']->Get_limit()}";			
		}
		
		$query = "SELECT " . ($paging?'SQL_CALC_FOUND_ROWS ':'') . "{$select}{$select_cc} FROM {$this->db_prefix}{$this->_table} t $join_cc $join
			$where_s $group_s $order_s $limit_s";
		$r_categories = $this->prepare($query);

		$this->debug_query($query, $query_params, 1);
		
		if (isset($params['query_only']) and $params['query_only']) {
			if (isset($params['get_query_params'])) {
				$params['get_query_params'] = $query_params;
				return $query;
			}
			return $this->get_debug_query_string($query, $query_params);
		}
		
		//echo "\n";
		//echo $this->get_debug_query_string($query, $query_params);
		
		$s = $r_categories->execute($query_params);
		if (!$s) {
			$this->debug($query, 2);
			$this->debug($query_params, 2);
			$this->debug('Query failed', 2);
			return false;
		}

		if ($paging) {
			$rTotal = $this->query("SELECT FOUND_ROWS()");
			if ($rTotal) $params['paging']->Set_total($total = $rTotal->fetchColumn());
		}
		
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
			if (isset($params['formatter'])) {
				//call_user_func_array(array($this, $params['formatter']), array($d));
				$this->$params['formatter']($d);
			}
			$custom_key = false;
			if (isset($params['key']) and isset($d[$params['key']]) and !empty($d[$params['key']])) {
				$custom_key = $d[$params['key']];
			}
			if (isset($params['value']) and isset($d[$params['value']])) {
				$d = $d[$params['value']];
			}
			
			if ($custom_key) {
				$items[$custom_key] = $d;
			} else {
				$items[] = $d;
			}
					
		}
		
		if ($limit == 1) {
			$count = count($items);
			if ($count == 1) {
				return $items[0];
			}
			if ($count == 0) {
				return false;
			}
		}
		return $items;
	}
	
	protected function _get_where_clause($where, &$query_params) {
		$where_clause = '';
		if (is_array($where)) {
			$where_strings = array();
			//$this->debug($where);
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
					if (is_string($key)) {
						$where_strings[] = " $key " . $this->sql_parser->in($value);
						$query_params = array_merge($query_params, $value);
					}
					else {
						$field = v($value['field'], '');
						$op = v($value['op'], '=');
						if (isset($value['value'])) {
							$value = $value['value'];
							if (!is_array($value)) {
								$query_params[] = $value;
							}
							else{
								$query_params = array_merge($query_params, $value);
							}
						}
						if (!empty($field)) {
							$where_strings[] = " $field $op ?";
						}
						
					}
				}
			}
			$where_clause = implode(' AND ', $where_strings);
		}
		else {
			$where_clause = $where;
		}
		return $where_clause;
	}
	
	public static function boolean_full_text_filter($string) {
		return str_replace(array('+', '-', '"', '~', '<', '>'), ' ', $string);
	}
	
	/**
	 * Add special operators to every word of the search string to make all words compulsory in the search.
	 * @param string $string
	 * @return string
	 */
	public static function add_all_words_fulltext_operators($string) {
		$words = PC_utils::str_word_count_utf8($string, 1, true);
		foreach ($words as $word) {
			$prepared_search_string .= '+'.$word.' ';
		}
		return $prepared_search_string;
	}
	
	public static function get_full_text_clause($field, $type) {
		if (is_array($field)) {
			$field = implode(', ', $field);
		}
		return "MATCH($field)
			AGAINST (? IN BOOLEAN MODE)";
	}
	
	public function get_parent_id($id) {
		$params = array();
		$data = $this->get_data($id, $params, 1);
		if ($data and isset($data[$this->_table_parent_col])) {
			return $data[$this->_table_parent_col];
		}
		return false;
	}
	
	public function validate(array $data, &$validation_data = array()) {
		$this->debug('validate()');
		$valid = true;
		foreach ($this->_rules as $rule_data) {
			$this_valid = true;
			if (!isset($data[$rule_data['field']])) {
				$this->debug(':) not is set', 2);
				continue;
			}
			$value = $data[$rule_data['field']];
			if (!is_array($value)) {
				if (v($rule_data['empty_allowed']) and empty($value)) {
					$this->debug(':) empty allowed', 2);
					continue;
				}
			}
			$this->debug($rule_data, 1);
			$this->debug('value is: ' . $value, 2);
			$general_validation = Validate($rule_data['rule'], $value, v($rule_data['extra'], false), v($rule_data['params'], array()));
			$this->debug("general_validation: " . $general_validation, 3);
			if ($general_validation !== 0) {
				$this->debug('validated by general function', 4);
				$this_valid = $general_validation;
			}
			else {
				switch ($rule_data['rule']) {
					case 'required': 
						$this_valid = !empty($value);
						break;
					case 'unique':
						$unique_params = array(
							'select' => 't.' . $this->_table_id_col,
							'where' => array("{$rule_data['field']} = ?"),
							'query_params' => array($value),
							'limit' => 1
						);
						if ($this->_id != 0) {
							$unique_params['where'][] = 't.' . $this->_table_id_col . ' <> ?';
							$unique_params['query_params'][] = $this->_id;
						}
						$this->debug_level_offset += 4;
						$duplicate_data = $this->get_all($unique_params);
						$this->debug_level_offset -= 4;
						if ($duplicate_data) {
							$this_valid = false;
						}
						break;

					default:
						break;
				}
			}
			
			if (!$this_valid) {
				$validation_data[] = array(
					'field' => $rule_data['field'],
					'error' => $rule_data['rule']
				);
				$valid = $this_valid;
			}
			
		}
		return $valid;
	}
	
	public function filter_value_by_filters(&$value, &$filters) {
		foreach ($filters as $filter) {
			$this->filter_value_by_filter($value, $filter);
		}
	}
	
	public function filter_value_by_filter(&$value, $filter) {
		$this->debug("filter_value_by_filter()", 8);
		$this->debug($value, 9);
		$this->debug($filter, 9);
		if (is_array($filter)) {
			$filter_name = $filter['filter'];
		}
		else {
			$filter_name = $filter;
		}
		if (is_callable($filter_name)) {
			$value = $filter_name($value);
			return;
		}
		switch ($filter_name) {
			case 'trim':
				$value = trim($value);
				break;

			case 'md5':
				$value = md5($value);
				break;

			case 'sha1':
				$value= sha1($value);
				break;

			case 'strtotime':
				$value = strtotime($value);
				break;

			default:
				$this->debug('General Sanitize', 10);
				$value = Sanitize($filter_name, $value,  v($filter['extra'], null));
				break;
		}
	}
	
	public function filter_array(&$data, &$filters) {
		if (!is_array($filters)) {
			return;
		}
		foreach ($filters as $filter) {
			if (!isset($data[$filter['field']])) {
				continue;
			}
			
			if (!isset($data[$filter['field']])) {
				continue;
			}
			
			if ($filter['filter'] == 'remove_empty') {
				if (empty($data[$filter['field']])) {
					unset($data[$filter['field']]);
				}
				continue;
			}
			$this->filter_value_by_filter($data[$filter['field']], $filter);
		}
	}
	
	public function filter(&$data, $sanitize = false) {
		$filters = ($sanitize?$this->_sanitize_filters:$this->_filters);
		$this->filter_array($data, $filters);
	}
	
	public function sanitize(&$data) {
		$this->filter($data, true);
	}
	
	public function update(array $data, $params = array()) {
		$this->debug('update()');
		$this->debug($data, 1);
		$this->debug($params, 1);
		$entity_id = false;
		if (!is_array($params)) {
			$entity_id = $params;
			$params = array(
				'where' => array(
					$this->_table_id_col => $entity_id
				)
			);
		}
		elseif(isset($params['where']) and isset($params['where'][$this->_table_id_col]) and !is_array($params['where'][$this->_table_id_col])) {
			$entity_id = $params['where'][$this->_table_id_col];
		}
		
		$content = array();
		if (isset($data['_content'])) {
			$content = $data['_content'];
			unset($data['_content']);
		}
		$this->sanitize($data);
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
		$edited_count = $r->rowCount();
		
		$this->debug('id -', 9);$this->debug($entity_id, 10);
		$this->debug('edited -', 9);$this->debug($edited, 10);
		//$this->debug('-', 9);$this->debug(!empty($content), 10);
		//$this->debug('-', 9);$this->debug(is_array($content), 10);
		//$this->debug('-', 9);$this->debug(!empty($this->_content_table), 10);
		
		if ($entity_id and $edited and !empty($content) and is_array($content) and !empty($this->_content_table)) {
			foreach ($content as $ln => $ln_values) {
				$sets = array();
				foreach ($ln_values as $key => $value) {
					$sets[] = "$key = ?";
				}
				if (empty($sets)) {
					continue;
				}
				
				$query_select = "SELECT * FROM {$this->db_prefix}{$this->_content_table} WHERE $this->_content_table_relation_col = ? AND $this->_content_table_ln_col = ?";
				$r_select = $this->prepare($query_select);
				$params_select = array($entity_id, $ln);
				$s = $r_select->execute($params_select);

				if ($s and $r_select->fetch()) {
					$sets_s = implode(',', $sets);
					$query = "UPDATE {$this->db_prefix}{$this->_content_table} SET $sets_s WHERE $this->_content_table_relation_col = ? AND $this->_content_table_ln_col = ?";
					$query_params = array_merge(array_values($ln_values), array($entity_id, $ln));
					$r = $this->prepare($query);
					$this->debug_query($query, $query_params, 2);
					$s = $r->execute($query_params);
				}
				else {
					$insert_fields = array_merge(array_keys($ln_values), array($this->_content_table_relation_col, $this->_content_table_ln_col));
					$insert_values = array_fill(0, count($insert_fields), '?');
					$insert_fields = implode(',' , $insert_fields);
					$insert_values = implode(',' , $insert_values);
					$query = "INSERT INTO {$this->db_prefix}{$this->_content_table} ($insert_fields) values ($insert_values)";
					$query_params = array_merge(array_values($ln_values), array($entity_id, $ln));
					$r = $this->prepare($query);
					$this->debug_query($query, $query_params, 2);
					$s = $r->execute($query_params);
				}
				
			}
		}
		return $edited_count;
	}
	
	public function insert(array $data, array $content = array(), $params = array()) {
		$this->debug('insert()');
		$this->debug($data);
		$count = count($data);
		if (!$count) {
			return false;
		}
		$this->sanitize($data);
		$fields = implode(',', array_keys($data));
		$values = implode(',', array_fill(0, $count, '?'));
		$data = array_values($data);
		$ignore = '';
		if (v($params['ignore'])) {
			$ignore = ' IGNORE ';
		}
		$query = "INSERT $ignore INTO {$this->db_prefix}{$this->_table} ($fields) VALUES ($values)";
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
				$query = "INSERT IGNORE INTO {$this->db_prefix}{$this->_content_table} ($this->_content_table_relation_col, $this->_content_table_ln_col, $fields) VALUES ($values)";
				$query_params = array_merge(array($id, $ln), array_values($ln_values));
				$r = $this->prepare($query);
				$this->debug_query($query, $query_params, 2);
				$s = $r->execute($query_params);
			}
		}
				
		return $id;
		
	}
	
	public function delete($params = array()) {
		if (!is_array($params) and $params) {
			$params = array('where' => array(
				$this->_table_id_col => $params
			));
		}
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
		
		
		if (!empty($this->_content_table)) {
			$content_where = array();
			if (isset($params['where']) and isset($params['where'][$this->_table_id_col])) {
				$content_where[$this->_content_table_relation_col] = $params['where'][$this->_table_id_col];
			}
			else {
				$params['value'] = $this->_table_id_col;
				$ids = $this->get_all($params);
				$content_where[$this->_content_table_relation_col] = $ids;
			}
			
			$content_query_params = array();
			$content_where_s = $this->_get_where_clause($content_where, $content_query_params);
			if (!empty($content_where_s)) {
				$content_where_s = ' WHERE ' . $content_where_s;
			}
			$query = "DELETE FROM {$this->db_prefix}{$this->_content_table} $content_where_s";
			$r = $this->prepare($query);
			$this->debug_query($query, $content_query_params, 2);
			$s = $r->execute($content_query_params);
		}
		
		
		$query = "DELETE FROM {$this->db_prefix}{$this->_table} $where_s $limit_s";
		$r = $this->prepare($query);
		$this->debug_query($query, $query_params, 1);
		
		$deleted = $s = $r->execute($query_params);
				
		return $deleted;
	}
	
	public function get_unique_content_field($ln, $field, $value, $scope = array()) {
		$this->debug("get_unique_content_field($field, $value)");
		$orig_value = $value;
		$params = array(
			'select' => 't.id',
			'content' => true,
			'ln' => $ln,
			'limit' => 1,
		);
		$this->absorb_scope_into_params($params, $scope);
		$params['where'][$field] = $value;
		
		$item = $this->get_all($params);
		$i = 0;
		while ($item) {
			$i++;
			$value =  $orig_value . '-' . $i;
			$params['where'][$field] = $value;
			$item = $this->get_all($params);
		}
		$this->debug("unique_content_field: $value", 1);
		return $value;
	}
	
}

?>
