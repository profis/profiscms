<?php

class Page_manager extends PC_base{
	
	protected $_nodes_data = array();
	
	/**
	 *
	 * @var array() | boolean
	 */
	protected $_remembered_nodes = false;
	
	protected $_root_node;
	
	protected $_site_id = null;
	
	protected $_page_tree_params = array();
	
	protected $_remembers_nodes_up_to_the_top = false;
	
	protected $_accessible_site_pages = false;
	
	/**
	 * 
	 * @param type $site_id
	 * @param type $node_id
	 * @param type $page_tree_params = array()
	 * @param type $node_is_accessible = false - If true, node is considered to be accessible - all children will be returned
	 * If false, node access will be checked before generating page tree
	 * @return type
	 */
	public function get_accessible_children($site_id, $node_id, $page_tree_params = array(), $node_is_accessible = false) {
		$this->debug("get_accessible_children(site $site_id, page $node_id)");
		$this->_site_id = $site_id;
		$this->_root_node = $node_id;
		$this->_page_tree_params = $page_tree_params;
		if (is_numeric($node_id) and $node_id == 0 and !empty($page_tree_params['additional'])) {
			if (!empty($page_tree_params['additional']['default_ref'])) {
				$default_ref = $page_tree_params['additional']['default_ref'];
				$new_node_id = $this->page->Get_page_id_by_reference($default_ref);
				if ($new_node_id) {
					$node_id = $new_node_id;
					$page_data = $this->page->Get_page_data($node_id);
					if ($page_data['controller']) {
						$this->_page_tree_params['plugin'] = $page_data['controller'];
					}
					$this->debug('node_id changed to ' . $node_id, 1);
				}
			}
			
			if (!empty($page_tree_params['additional']['default_controller'])) {
				$default_ctrl = $page_tree_params['additional']['default_controller'];
				$page_model = $this->core->Get_object('PC_page_model');
				$node_ids =$page_model->get_all(array(
					'where' => array('controller = ?'),
					'query_params' => array($default_ctrl),
				));
				if (!empty($node_ids) and count($node_ids) == 1) {
					$node_id = $node_ids[0]['id'];
					$this->_page_tree_params['plugin'] = $default_ctrl;
					$this->debug('node_id changed to ' . $node_id, 1);
				}
			}
			
			
		}
		
		if ($node_id == -1) {
			$this->debug("   pages for recycle bin");
			$this->_page_tree_params['check_page_children_access'] = true;
			$children = $this->get_page_node_children($node_id);
			$children = array_reverse($children); // show last deleted first
			return $children;
		}
		
		if (!$this->_accessible_site_pages) {
			$this->_accessible_site_pages = $this->auth->Get_accessible_site_pages($site_id);
		}
		
		$this->debug('Accessible nodes:', 1);
		$this->debug($this->_accessible_site_pages, 2);
		
		if ($node_is_accessible or $this->is_site_node_accessible($site_id, $node_id)) {
			$this->debug("node was specified as accessible or site node turned out to be accessible", 1);
			$this->debug("So, generating all children", 1);
			return $this->_get_node_children($node_id);
		}
		else {
			if (v($page_tree_params['search'])) {
				$this->debug("pages for search: setting accessible_page_sets", 1);
				$this->_page_tree_params['accessible_page_sets'] = $this->_get_accessible_page_sets();
				$children = $this->get_page_node_children($node_id);
				return $children;
			}
			else {
				return $this->_get_accessible_children_from_permissions();
			}
		}
	}
	
	protected function _clear_remembered_nodes() {
		$this->_nodes_data = array();
		$this->_remembers_nodes_up_to_the_top = false;
	}
	
	/**
	 * Method for checking if page node is accessible for user.
	 * Site_id will be detected automatically by node id
	 * @param type $node_id
	 * @return type
	 */
	public function is_node_accessible($node_id, $wanted_site_id = null) {
		if ($this->auth->Authorize('core', 'admin')) {
			$this->debug(':) superadmin');
			return true;
		}
		if (v($this->allow_access)) {
			//return true;
		}
		$site_id = null;
		if ($node_id == '0') {
			//We can't detect site id from page 0, so we use provided site_id
			$site_id = $wanted_site_id;
		}
		return $this->is_site_node_accessible($site_id, $node_id);
	}
	
	/**
	 * 
	 * @param integer | null $site_id - if null, site_id will be retrieved automatically
	 * @param integer | string $node_id
	 * @return boolean
	 */
	public function is_site_node_accessible($site_id, $node_id) {
		$this->debug("is_site_node_accessible(site $site_id, node $node_id)");
		
		if (is_null($site_id) or $site_id != $this->_site_id) {
			$this->_accessible_site_pages = false;
			$this->_site_id = $site_id;
		}
		
		$this->_clear_remembered_nodes();
		$this->_remembers_nodes_up_to_the_top = true;
		$this->_remember_node($node_id, null, false);
		$node_and_its_parents = array_keys($this->_nodes_data);
		$this->debug("nodes data after remembering node $node_id:", 1);
		$this->debug($this->_nodes_data, 1);
		$this->_clear_remembered_nodes();
		
		if (!$this->_site_id) {
			$this->debug(":( Site id could not be detected", 4);
			return false;
		}
		
		$this->debug('Node and its parents:', 2);
		$this->debug($node_and_its_parents, 2);

		if ($this->_site_id and !$this->_accessible_site_pages) {
			$this->_accessible_site_pages = $this->auth->Get_accessible_site_pages($this->_site_id);
		}
		
		if (!is_array($this->_accessible_site_pages) or empty($this->_accessible_site_pages) or in_array($node_id, $this->_accessible_site_pages)) {
			$this->debug(":) persmissions are empty or node_id is in accessible_site_pages", 4);
			return true;
		}
		
		$accessible_parents = array_intersect($node_and_its_parents, $this->_accessible_site_pages);
		$this->debug("Intersection with accessible site pages:", 3);
		$this->debug($accessible_parents, 3);
		if (!empty($accessible_parents)) {
			$this->debug(":) node $node_id has accessible (grand)*parent", 4);
			return true;
		}
		$this->debug(":(", 4);
		return false;
	}
	
	protected function _get_accessible_children_from_permissions() {
		$this->debug("_get_accessible_children_from_permissions()");
		
		if (!$this->_remembered_nodes) {
			foreach ($this->_accessible_site_pages as $key => $node_id) {
				$this->_remember_node($node_id, null, true);
				$this->_remembered_nodes = $this->_nodes_data;
			}
		}
		else {
			 $this->_nodes_data = $this->_remembered_nodes;
		}
		
		$this->debug('Remembered nodes:', 1);
		$this->debug($this->_nodes_data, 2);
		
		$children = array();
		$this->_generate_children($this->_root_node, $children);
		return $children;
	}
	
	/**
	 * Insert node mini data into $this->_nodes_children
	 * @param type $node_id
	 * @param boolean $accessible
	 */
	protected function _remember_node($node_id, $children = array(), $accessible = false) {
		if (!is_array($children)) {
			$children = array();
		}
		if (isset($this->_nodes_data[$node_id])) {
			if (!$this->_nodes_data[$node_id]['accessible']) {
				$this->_nodes_data[$node_id]['accessible'] = $accessible;
			}
			if (!empty($children)) {
				$this->_nodes_data[$node_id]['children'] = array_merge($this->_nodes_data[$node_id]['children'], $children);
			}
		}
		else {
			$this->_nodes_data[$node_id] = array(
				'children' => $children,
				'accessible' => $accessible
			);
			if ($this->_remembers_nodes_up_to_the_top or $this->_root_node != $node_id) {
				$parent_node = $this->_get_node_parent($node_id);
				if ($parent_node !== false) {
					$this->_remember_node($parent_node, array($node_id));
				}
			}
		}
	}
	
	/**
	 * Returns accessible pages (without controllers nodes)
	 * @return array with keys 'id' and 'pid'
	 */
	protected function _get_accessible_page_sets() {
		$page_ids = array();
		$controller_nodes = array();
		foreach ($this->_accessible_site_pages as $key => $node) {
			$controller_data = $this->_get_controller_data_from_id($node);
			if ($controller_data) {
				v($controller_nodes[$controller_data['plugin']], array());
				$controller_nodes[$controller_data['plugin']][] = $controller_data['id'];
			}
			else {
				$page_ids[] = $node;
			}
		}
		
		$this->accessible_page_sets = array(
			'id' => array(),
			'idp' => array()
		);
		
		$this->page_children_ids = array();
		
		foreach ($page_ids as $page_id) {
			$this->_collect_accessible_page_sets($page_id, true);
		}
		
		$this->accessible_page_sets['id'] = array_unique($this->accessible_page_sets['id']);
		$this->accessible_page_sets['idp'] = array_unique($this->accessible_page_sets['idp']);
		$this->accessible_page_sets['controller_nodes'] = $controller_nodes;
		return $this->accessible_page_sets;
	}
	
	protected function _collect_accessible_page_sets($node_id, $include_id = false) {
		$this->debug("_collect_accessible_page_sets($node_id)");
		$children_ids = $this->_get_page_children_ids($node_id);
		
		if ($include_id) {
			$this->accessible_page_sets['id'][] = $node_id;
		}
		
		if (!empty($children_ids)) {
			$this->accessible_page_sets['idp'][] = $node_id;
			foreach ($children_ids as $key => $id) {
				$this->_collect_accessible_page_sets($id);
			}
		}
		
	}
	
	protected function _get_page_children_ids($node_id) {
		if (isset($this->page_children_ids[$node_id])) {
			return $this->page_children_ids[$node_id];
		}
		else {
			$children_ids = $this->page->Get_pages_data('id', 'idp = ?', array($node_id));
			if ($children_ids) {
				$this->page_children_ids[$node_id] = $children_ids;
				return $children_ids;
			}
		}
	}
	
	protected function _get_node_parent($node_id) {
		$controller_data = $this->_get_controller_data_from_id($node_id);
		if ($controller_data) {
			return $this->_get_controller_node_parent($controller_data['id'], $controller_data['plugin']);
		}
		return $this->_get_page_node_parent($node_id);
	}
	
	/**
	 * Method automatically sets $this->_site_id if it is null and site_id was detected
	 * @param integer $node_id
	 * @return integer
	 */
	protected function _get_page_node_parent($node_id) {
		$parent_id = $this->page->Get_page_parent_id($node_id);
		if (!$this->_site_id or is_null($this->_site_id)) {
			$this->_site_id = $this->page->Get_page_site_id($node_id);
			$this->debug('Page node site id was deteted: ' . $this->_site_id, 3);
		}
		return $parent_id;
	}
	
	protected function _get_controller_node_parent($node_id, $plugin) {
		$this->debug("_get_controller_node_parent($node_id, $plugin)");
		if (!empty($plugin)) {
			if ($this->core->Count_hooks('core/tree/get-parent-id/'.$plugin) >= 1) {
				//init renderer hooks to generate output results
				$parent_id;
				$this->core->Init_hooks('core/tree/get-parent-id/'.$plugin, array(
					'id'=> $node_id,
					'data'=> &$parent_id
				));
				$this->debug("   parent: $parent_id");
				return $parent_id;
			}
		}
		return false;
	}
	
	protected function _generate_children($node_id, &$children) {
		$this->debug("_generate_children($node_id)");
		
		$this->debug("_remembered_nodes:", 5);
		$this->debug($this->_remembered_nodes, 6);
		
		if (!isset($this->_remembered_nodes[$node_id])) {
			$this->debug("_remembered_nodes[$node_id] is not set");
			$this->debug($this->_remembered_nodes, 1);
			return;
		}
		
		$node_children = $this->_get_node_children($node_id);
		
		$this->debug("_remembered_nodes after:", 5);
		$this->debug($this->_remembered_nodes, 6);
		
		foreach ($node_children as $key => $child) {
			if (v($this->_remembered_nodes[$node_id]['accessible'])) {
				$this->_remembered_nodes[$child['id']]['accessible'] = true;
			}
			else {
				if (!in_array($child['id'], $this->_remembered_nodes[$node_id]['children'])) {
					unset($node_children[$key]);
					$this->debug("   :( continue page {$child['id']}, coz child id is not in array of available $node_id children " . implode(',', $this->_remembered_nodes[$node_id]['children']), 2);
					continue;
				}

				if (isset($this->_remembered_nodes[$child['id']])) {
					if (!v($this->_remembered_nodes[$child['id']]['accessible'])) {
						$child['disabled'] = true;
						$child['denied'] = true;
					}
				}
			}
			$children[] = $child;
		}
		
		if ($node_id == 0 and (strlen($node_id)) == 1) {
			$this->debug("  (1) node ($node_id) is zero: add pages");
			$this->_add_additional_pages_to_root_children($children);
		}
		
		//$children = $node_children;
	}
	
	protected function _get_node_children($node_id) {
		$this->debug("_get_node_children($node_id)");
		$controller_data = $this->_get_controller_data_from_id($node_id);
		if ($controller_data) {
			$children = $this->_get_controller_node_children($controller_data['id'], $controller_data['plugin'], $this->_page_tree_params);
			//if (!empty($children)) {
				return $children;
			//}
		}
		return $this->get_page_node_children($node_id);
	}
	
	/**
	 * Method returns children of specified page without global permission checking.
	 * Uses $this->_page_tree_params
	 * @param type $node_id
	 * @return type
	 */
	public function get_page_node_children($node_id) {
		$this->debug("get_page_node_children($node_id)");
		$plugin = v($this->_page_tree_params['plugin']);
		$children = array();
		if (!empty($plugin)) {
			$children = $this->_get_controller_page_children($node_id, $plugin, $this->_page_tree_params);
			if (is_array($children) and $this->_plugin_was_not_empty or $children) {
				$allow_pages = false;
				$this->core->Init_hooks('core/tree/get-childs/allow_pages/'.$plugin, array(
					'result'=> &$allow_pages
				));
				$this->_plugin_was_not_empty = true;
				if (!$allow_pages) {
					return $children;
				}
			}
		}
		if (!is_array($children)) {
			$children = array();
		}
		
		$children = array_merge(Get_tree_childs($node_id, $this->_site_id, v($this->_page_tree_params['deleted']), v($this->_page_tree_params['search']), v($this->_page_tree_params['date']), v($this->_page_tree_params['additional']), $this->_page_tree_params, $this), $children);
		$this->debug("  number of children got by Get_tree_childs() - " . count($children));
		
		if ($node_id == 0 and (strlen($node_id)) == 1 and !v($this->_page_tree_params['search'])) {
			$this->debug("  (2) node ($node_id) is zero: add pages");
			$this->_add_additional_pages_to_root_children($children);
		}
		
		return $children;
	}
	
	protected function _get_controller_data_from_id($node_id) {
		return $this->page->get_controller_data_from_id($node_id);
	}
	
	public function _get_controller_page_children($node_id, $plugin, $page_tree_params = array()) {
		$this->debug("_get_controller_page_children($node_id, $plugin)");
		$controller_data = $this->_get_controller_data_from_id($node_id);
		if ($controller_data) {
			if (empty($plugin)) {
				$plugin = $controller_data['plugin'];
			}
			$node_id = $controller_data['data'];
		}
		$this->_plugin_was_not_empty = false;
		if (!empty($plugin)) {
			if ($this->core->Count_hooks('core/tree/get-childs/'.$plugin) >= 1) {
				//init renderer hooks to generate output results
				$this->core->Init_hooks('core/tree/get-childs/'.$plugin, array(
					'id'=> $node_id,
					'additional'=> &$page_tree_params['additional'],
					'data'=> &$children
				));
				$this->_plugin_was_not_empty = true;
				return $children;
			}
		}
		return false;
	}
	
	protected function _get_controller_node_children($node_id, $plugin, $page_tree_params = array()) {
		$this->debug("_get_controller_node_children($node_id, $plugin)");
		if (!empty($plugin)) {
			if ($this->core->Count_hooks('core/tree/get-childs/'.$plugin) >= 1) {
				//init renderer hooks to generate output results
				$this->core->Init_hooks('core/tree/get-childs/'.$plugin, array(
					'id'=> $node_id,
					'additional'=> &$page_tree_params['additional'],
					'data'=> &$children
				));
				return $children;
			}
		}
		return false;
	}
	
	protected function _prepend_frontpage(&$children) {
		
	}
	
	protected function _add_additional_pages_to_root_children (&$out) {
		$this->debug("_add_additional_pages_to_root_children()");
		//"Create new page" node
		$i = array(
			'id' => 'create',
			'cls' => 'cms-tree-node-add',
			'draggable' => false,
			'_nosel' => 1,
			'leaf' => true
		);
		$out[] = $i;
		//recycle bin
		$i = array(
			'id' => -1,
			'cls' => 'cms-tree-node-trash',
			'draggable' => false,
			'_nosel' => 1
		);
		$r = $this->db->prepare("SELECT count(*) FROM {$this->cfg['db']['prefix']}pages WHERE deleted=1 and site=?");
		$success = $r->execute(array($this->_site_id));
		if ($success) {
			$count = $r->fetchColumn();
			if ($count == 0) {
				$i['_empty'] = 1;
				$i['expandable'] = false;
			}
		}
		$out[] = $i;
	}
	
}

?>
