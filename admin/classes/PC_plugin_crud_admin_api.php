<?php

abstract class PC_plugin_crud_admin_api extends PC_plugin_admin_api {
	
	/**
	 *
	 * @var PC_model
	 */
	protected $_model;
	
	protected $_valid_fields = array();
	
	abstract protected function _get_model();
		
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
	}
	
	protected function _get_available_order_columns() {
		return array(
			'time_from' => 'time_from',
			'time_to' => 'time_to',
			'date_from' => 'time_from',
			'date_to' => 'time_to',
		);
	}
	
	protected function _adjust_search(&$params) {
		
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
		
		$model = $this->_get_model();
		
		$params = array(
			'paging' => &$paging,
			'where' => $where,
			'query_params' => $parameters,
		);
		
		$this->_adjust_search($params);
		
		$model->absorb_debug_settings($this);
		$this->_adjust_order_params($params);
		$this->_out['list'] = $model->get_all($params);
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
		
		if (!empty($this->_valid_fields)) {
			$data['other'] = PC_utils::filterArray($this->_valid_fields, $data['other']);
		}
		$this->_before_insert($data['other'], $content);
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
	
	protected function _before_update(&$data, &$content) {
		
	}
	
	public function edit() {
		$this->debug('edit()');
		
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
		
		$new_data['_content'] = $content;
		
		$this->_model = $this->_get_model();
		$this->_model->absorb_debug_settings($this);
		
		$params = array(
			'where' => array(
				'id' => intval($_POST['id'])
			)
		);
		
		$this->_out['success'] = $this->_model->update($new_data, $params);
		
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
	
}

?>