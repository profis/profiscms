<?php

abstract class PC_plugin_crud_admin_api extends PC_plugin_admin_api {
	
	/**
	 *
	 * @var PC_model
	 */
	protected $_model;
	
	protected $_valid_fields = array();
	
	abstract protected function _get_model();
		
	public function get_model() {
		return $this->_get_model();
	}
	
	protected function _adjust_order_params(&$params) {
		if (isset($_POST['sort'])) {
			$cols = $this->_get_available_order_columns();
			if (isset($cols[$_POST['sort']])) {
				$params['order'] = $cols[$_POST['sort']];
				if (isset($_POST['dir'])) {
					if ($_POST['dir'] == 'ASC') {
						$params['order_dir'] = 'ASC';
					}
					elseif($_POST['dir'] == 'DESC') {
						$params['order_dir'] = 'DESC';
					}
				}
			}
		}
		elseif(v($this->_default_order)) {
			$params['order'] = $this->_default_order;
		}
	}
	
	protected function _get_available_order_columns() {
		return array(
			'time_from' => 'time_from',
			'time_to' => 'time_to',
			'date_from' => 'time_from',
			'date_to' => 'time_to',
		);
	}
	
	protected function _get_available_filters() {
		return array('id' => 't.id');
	}
	
	protected function _adjust_search(&$params) {
		$this->debug('_adjust_search()');
		$available_filters = $this->_get_available_filters();
		if (!isset($_POST['filters']) or !is_array($_POST['filters'])) {
			return;
		}
		foreach ($_POST['filters'] as $filter => $value) {
			if (!isset($available_filters[$filter]) or empty($value)) {
				continue;
			}
			if (!is_array($available_filters[$filter])) {
				$params['where'][] = $available_filters[$filter] . ' = ?';
				$params['query_params'][] = $value;
			}
			else {
				if (isset($available_filters[$filter]['model_filters'])) {
					$this->_model->filter_value_by_filters($value, $available_filters[$filter]['model_filters']);
				}
				if (isset($available_filters[$filter]['callback'])) {
					call_user_func_array($available_filters[$filter]['callback'], array($value, &$params));
				}
				else {
					$field = v($available_filters[$filter]['field']);
					$op = v($available_filters[$filter]['op'], '=');
					$params['where'][] = "$field $op ?";
					$params['query_params'][] = $value;
				}
			}
		}
		$this->debug('_adjust_search end:', 1);
		$this->debug($params, 2);
		
	}
	
	public function get() {
		$this->debug('get()');
		$g_p = array_merge($_GET, $_POST);
		$start = (int) v($g_p['start']);
		$limit = (int) v($g_p['limit']);
		if ($start < 0)
			$start = 0;
		if ($limit < 1)
			$limit = v($items_per_page, 0);
		
		$paging = false;
		
		if ($limit != 0) {
			$paging = array(
				'perPage' => $limit,
				'start' => $start
			);
		}

		$where = array();
		$parameters = array();
		
		$this->_model = $model = $this->_get_model();
		
		$params = array(
			'paging' => &$paging,
			'where' => $where,
			'query_params' => $parameters,
		);
		if (v($this->_content_fields) and is_array($this->_content_fields)) {
			$content_fields = array();
			foreach ($this->_content_fields as $key => $value) {
				$content_fields[] = 'ct.' . $value;
			}
			$params['content'] = array(
				'select' => implode(',', $content_fields)
			);
		}
		
		$this->_adjust_search($params);
		
		$model->absorb_debug_settings($this);
		$this->_adjust_order_params($params);
		$this->_out['list'] = $model->get_all($params);
		
		$this->_after_get();
		
		if ($paging) {
			$this->_out['total'] = $paging->Get_total();
		}
		
		
		if (isset($_GET['callback'])) {
			echo $_GET['callback'] . "(" . json_encode($this->_out) . ")";
			exit;
		}
		
	}
	
	public function create() {
		$this->debug('create()');
		$this->_model = $this->_get_model();
		$this->_model->absorb_debug_settings($this);
		
		$data = json_decode(v($_POST['data'], '{}'), true);
		
		$this->debug($data);
		
		$content = array();
		
		foreach ($data['names'] as $ln => $name) {
			v($content[$ln], array());
			$content[$ln]['name'] = $name;
		}
		
		$this->_before_insert($data['other'], $content);
		
		if (!empty($this->_valid_fields)) {
			$data['other'] = PC_utils::filterArray($this->_valid_fields, $data['other']);
		}
		$this->debug('After filter valid fields:', 2);
		$this->debug($data, 3);
		
			
		$this->_model->filter($data['other']);
		$this->debug('After filter:', 2);
		$this->debug($data['other'], 3);
		
		
		$validation_data = array();
		$valid = $this->_model->validate($data['other'], $validation_data);
		if (!$valid) {
			$this->_out['success'] = false;
			$this->_out['error'] = $validation_data[0]['error'];
			$this->_out['error_data'] = $validation_data[0];
			return;
		}
		
		
		$id = $this->_model->insert($data['other'], $content);
			
		if ($id) {
			$this->_out['success'] = true;
			$this->_out['id'] = $id;
			$this->_out = array_merge($this->_out, $data['other']);
			$this->_out = array_merge($this->_out, $data);
			$this->_after_insert();
		}
	}
	
	protected function _before_insert(&$data, &$content) {
		
	}
	
	protected function _after_insert() {
		
	}
	
	protected function _after_get() {
		
	}
	
	protected function _before_update(&$data, &$content) {
		
	}
	
	public function edit() {
		$this->debug('edit()');
		
		$id = intval(v($_POST['id']));
		if ($id == 0) {
			$this->create();
			return;
		}
		
		$data = json_decode(v($_POST['data'], '{}'), true);
		
		$this->debug($data);
		
		$content = array();
		
		if (isset($data['names'])) {
			foreach ($data['names'] as $ln => $name) {
				v($content[$ln], array());
				$content[$ln]['name'] = $name;
			}
		}
		
		$new_data = $data['other'];
		
		$this->_before_update($new_data, $content);
		
		if (!empty($this->_valid_fields)) {
			$new_data = PC_utils::filterArray($this->_valid_fields, $new_data);
		}
		$this->debug('After filter valid fields:', 2);
		$this->debug($new_data, 3);
		
		$new_data['_content'] = $content;
		
		$this->_model = $this->_get_model();
		$this->_model->absorb_debug_settings($this);
		
		$this->_model->set_id(intval($_POST['id']));
		
		$params = array(
			'where' => array(
				'id' => intval($_POST['id'])
			)
		);
		
		$this->_model->filter($new_data);
		$this->debug('After filter:', 2);
		$this->debug($new_data, 3);
		$validation_data = array();
		$valid = $this->_model->validate($new_data, $validation_data);
		if ($valid) {
			$this->_out['success'] = $this->_model->update($new_data, $params);
		}
		else {
			$this->_out['success'] = false;
			$this->_out['error'] = $validation_data[0]['error'];
			$this->_out['error_data'] = $validation_data[0];
		}
		
		
	}
	
	
	public function delete() {
		$this->debug('delete()');
		$this->debug($_POST);
		$ids = json_decode(v($_POST['ids'], '{}'), true);
		$this->debug($ids);
		
		$this->_model = $this->_get_model();
		$this->_model->absorb_debug_settings($this);
		foreach ($ids as  $id) {
			$this->_model->delete($id);
		}
		$this->_out['success'] = true;
	}
	
	protected function _get_sync_fields() {
		return array();
	}
	
	public function sync() {
		$this->_model = $this->_get_model();
		$this->_model->absorb_debug_settings($this);
		
		$data = v($_POST['data'], '[]');
		if (!is_array($data)) {
			$data = json_decode($data, true);
		}
		
		$this->debug($data);
		
		$sync_fields = $this->_get_sync_fields();
		$sync_fields_flipped = array_flip($sync_fields);
		
		foreach ($data as $update_data) {
			$id_field = $this->_model->get_id_field();
			$id = v($update_data[$id_field]);
			if ($id) {
				unset($update_data[$id_field]);
				if (!empty($sync_fields)) {
					$update_data = array_intersect_key($update_data, $sync_fields_flipped);
				}
				$content = array();
		
				if (isset($update_data['names'])) {
					foreach ($data['names'] as $ln => $name) {
						v($content[$ln], array());
						$content[$ln]['name'] = $name;
					}
					unset($update_data['names']);
				}
				$update_data['_content'] = $content;
				$updated = $this->_model->update($update_data, $id);
				$this->debug('update result: ' . $updated, 5);
				if (!$updated) {
					unset($update_data['_content']);
					$update_data[$id_field] = $id;
					$this->_model->insert($update_data);
				}
			}
		}
		
		$this->_out['success'] = true;
	}
	
	public function get_for_combo() {
		$this->_model = $this->_get_model();
		$this->_model->absorb_debug_settings($this);
		
		$params = array(
			'select' => 't.id',
			'content' => array(
				'select' => 'ct.name'
			),
			'ln' => false
		);
		$content_table = $this->_model->get_content_table();
		if (empty($content_table)) {
			unset($params['content']);
			$params['select'] .= ',t.name';
		}
		
		$this->_out = $this->_model->get_all($params);
		if (isset($_GET['empty'])) {
			array_unshift($this->_out, array(
				'id' => 0,
				'name' => ''
			));	
		}
	}
	
	public function set_positions() {
		$this->debug('set_positions()');
		$this->debug($_POST);
		$ids = json_decode(v($_POST['positions'], '{}'), true);
		$this->debug($ids);
		
		if (is_array($ids)) {
			$position = 0;
			$this->_model = $this->_get_model();
			$this->_model->absorb_debug_settings($this);
			foreach ($ids as $id) {
				$this->_model->update(array($this->_model->get_table_position_col() => $position), $id);
				$position++;
			}
		}
		
		
	}
	
}

?>