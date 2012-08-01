<?php
final class PC_controllers extends PC_base {
	private $_list = array();
	private $_options = array();
	public function Init() {
		$this->_list = array(
			'all'=> array(),
			'active'=> array()
		);
	}
	public function Get($type) {
		if (isset($this->_list[$type])) return $this->_list[$type];
		else return false;
	}
	public function Register($plugin) {
		if (!in_array($plugin, $this->_list['all'])) {
			$this->_list['all'][] = $plugin;
		}
		return true;
	}
	public function Set_active($plugin, $active = true) {
		if (!in_array($plugin, $this->_list['all'])) return false;
		if (in_array($plugin, $this->_list['active']) !== $active) {
			$this->_list['active'][] = $plugin;
		}
		return true;
	}
	public function Exists($plugin) {
		
	}
	public function Is_active($plugin) {
		
	}
	public function Set_option($plugin, $name, $value) {
		$this->_options[$plugin][$name] = $value;
		return true;
	}
	public function Get_option($plugin, $name) {
		if (isset($this->_options[$plugin][$name])) {
			return $this->_options[$plugin][$name];
		}
		else return null;
	}
	public function Get_path() {
		if (!$this->site->Is_loaded()) return false;
		$path = array();
		foreach ($this->site->loaded_page['route_path'] as $p) {
			if (!empty($p['controller'])) $path[] = $p['controller'];
		}
		return $path;
	}
	public function Execute() {
		if (!$this->site->Is_loaded()) return false;
		$total = count($this->site->loaded_page['route_path']);
		for ($i=0; isset($this->site->loaded_page['route_path'][$i]); $i++) {
			$p =& $this->site->loaded_page['route_path'][$i];
			if (!empty($p['controller'])) {
				if ($i < $total-1) if ($this->Get_option($p['controller'], 'inheritance') !== true) {
					continue;
				}
				if (!$this->plugins->Is_active($p['controller'])) {
					return false;
				}
				//execute controller
				$this->site->controller = $this->plugins->Get_controller($p['controller']);
				if (!$this->site->controller) {
					$this->core->Show_error('controller_not_found');
				}
				elseif (!method_exists($this->site->controller, 'Process')) {
					return false;
				}
				else {
					$args = array($this->site->loaded_page);
					$r = call_user_func_array(array($this->site->controller, 'Process'), $args);
				}
			}
		}
		return true;
		
		//call controller defined by this route
		if ($route['controller'] != 'page') {
			try {
				print_pre($route);
				if ($this->plugins->Is_active($route['controller'])) {
					$this->controller = $this->plugins->Get_controller($route['controller']);
					//---
				}
				else {
					$this->core->Show_error('controller_is_not_active');
					$this->core->Init_hooks('after_render', array(
						'rendered'=> $this->render,
						'page'=> $route
					));
					return $this->render;
				}
			}
			catch (PC_controller_exception $e) {
				switch ($e->getMessage()) {
					case 'required_routes':
						$this->core->Show_error(404);
						$this->core->Init_hooks('after_render', array(
							'rendered'=> $this->render,
							'page'=> $route
						));
						return $this->render;
						break;
					default:
						$this->core->Show_error('PC_controller_exception -> '.$e->getMessage());
						$this->core->Init_hooks('after_render', array(
							'rendered'=> $this->render,
							'page'=> $route
						));
						return $this->render;
				}
			}
		}
	}
}