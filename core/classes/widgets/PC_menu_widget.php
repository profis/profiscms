<?php

class PC_menu_widget extends PC_widget {
	
	protected $_template_group = 'menu';
	protected $_template = 'tpl';
	
	protected function _get_default_config() {
		return array(
			'root' => false,
			'wrap' => '<ul>|</ul>'
		);
	}
	
	public function get_data() {
		$data = array(
			'menu' => array(),
			'wrap_begin' => '',
			'wrap_end' => '',
			'level' => 1
		);
		
		if (!empty($this->_config['wrap'])) {
			list($data['wrap_begin'], $data['wrap_end']) = explode('|', $this->_config['wrap']);
		}
		
		$data['menu'] = array();
		
		if ($this->_config['root']) {
			$data['menu'] = $this->page->Get_submenu($this->_config['root'], array("pid", "idp", "name", "route", "permalink"));
		}
		$this->_build_menu($data['menu']);
		
		return $data;
	}
	
	protected function _build_menu(&$menu) {
		foreach ($menu as $key => $menu_item) {
			if (!isset($menu[$key]['link'])) {
				$menu[$key]['link'] = $this->page->Get_page_link_from_data($menu_item);
			}
			if ($this->site->Is_opened($menu_item['pid'])) {
				$menu[$key]['_active'] = true;
				$menu[$key]['_submenu'] = $this->page->Get_submenu($menu_item['pid'], array("pid", "idp", "name", "route", "permalink"));
				if (empty($menu[$key]['_submenu'])) {
					$additional_menu = $this->site->Get_data('additional_menu_' . $this->site->loaded_page['pid']);
					if ($additional_menu) {
						$menu[$key]['_submenu'] = $additional_menu;
					}
				}
				else {
					$this->_build_menu($menu[$key]['_submenu']);
				}
				
			}
			
		}
	}
	
}