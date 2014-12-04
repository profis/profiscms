<?php

class PC_menu_widget extends PC_widget {
	
	protected $_template_group = 'menu';
	protected $_template = 'tpl';
	
	protected function _get_default_config() {
		return array(
			'menu' => false,
			'max_levels' => 0,
			'root' => false,
			'wrap' => '<ul>|</ul>',
			'submenu_for_all' => false,
			
			'fields' => array("pid", "idp", "name", "route", "permalink", "reference_id"),
			'additional_fields' => array()
		);
	}
	
	public function get_data() {
		$data = array(
			'menu' => array(),
			'wrap_begin' => '',
			'wrap_end' => '',
			'level' => 1,
			'no_href_with_submenu' => false
		);
		
		if (!empty($this->_config['wrap'])) {
			list($data['wrap_begin'], $data['wrap_end']) = explode('|', $this->_config['wrap']);
		}
		
		$data['menu'] = array();
		
		if ($this->_config['root']) {
			$fields = array_merge($this->_config['fields'], $this->_config['additional_fields']);
			$data['menu'] = $this->page->Get_submenu($this->_config['root'], $fields);
		} elseif ($this->_config['menu'] !== false) {
			$data['menu'] = $this->page->Get_menu($this->_config['menu']);
		}
		$this->_build_menu($data['menu']);
		
		return $data;
	}
	
	protected function _build_menu(&$menu, $level = 1) {
		$next_level = $level + 1;
		foreach ($menu as $key => $menu_item) {
			if (!isset($menu[$key]['link'])) {
				$menu[$key]['link'] = $this->page->Get_page_link_from_data($menu_item);
			}
			$is_opened = false;
			if ($this->site->Is_opened($menu_item['pid'])) {
				$is_opened = true;
				$menu[$key]['_active'] = true;
			}
			if ($this->_config['max_levels'] > 0 && $next_level > $this->_config['max_levels']) {
				continue;
			}
			if ($this->_config['submenu_for_all'] || $this->site->Is_opened($menu_item['pid'])) {
				if ($this->site->Is_opened($menu_item['pid'])) {
					$menu[$key]['_active'] = true;
				}
				if( v($menu_item['controller']) != 'pc_timeline' ) {
					$fields = array_merge($this->_config['fields'], $this->_config['additional_fields']);
					$menu[$key]['_submenu'] = $this->page->Get_submenu($menu_item['pid'], $fields);
					if (empty($menu[$key]['_submenu'])) {
						$additional_menu = $this->site->Get_data('additional_menu_' . $menu_item['pid']);
						if ($additional_menu) {
							$menu[$key]['_submenu'] = $additional_menu;
						}
					}
					else {
						$this->_build_menu($menu[$key]['_submenu'], $next_level);
					}
				}
			}
		}
	}
	
}