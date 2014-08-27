<?php

abstract class PC_widget extends PC_base {
	
	protected $_template_group;
	protected $_template = 'tpl';
	
	protected $_config;
	
	abstract function get_data();
	
	public function Init($config = array()) {
		$this->_config = array_merge($this->_get_default_config(), $config);
		
		if (v($this->_config['debug'])) {
			$this->debug = true;
		}
		
		if (v($this->_config['debug_forced'])) {
			$this->debug_forced = true;
		}
		
		if (isset($this->_config['debug_file'])) {
			$file = $this->_config['debug_file'];
			if (empty($file)) {
				$file = $this->cfg['path']['logs'] . 'widgets/' . get_class($this) . '.html';
			}
			$this->set_instant_debug_to_file($file, false, 5);
		}
			
	}
	
	protected function _get_default_config() {
		return array(
			
		);
	}
	
	public function get_template_group() {
		 return $this->_template_group;
	}
	 
	public function get_template() {
		return $this->_template;
	}
	
	public function Get_variable($var) {
		return $this->core->Get_plugin_variable($var, v($this->plugin_name));
	}
	
	public function get_text($data = false) {
		if (!$data) {
			$data = $this->get_data();
			$this->debug('get_data was called, data keys:', 1);
			$this->debug(array_keys($data), 2);
		}
		$data['tpl_group'] = $this->get_template_group();
		///*
		foreach ($data as $key => $value) {
			$$key = $value;
		}
		
		$file = $this->core->Get_tpl_path($this->get_template_group(), $this->get_template());
		
		$this->debug('File to be included:', 1);
		$this->debug($file, 2);
		
		$s = '';
		$this->Output_start();
		include $file;
		$this->Output_end($s);
		return $s;
		//*/	
		
		//return $this->site->Get_tpl_content($this->get_template_group(), $this->get_template(), $data);
	}

	protected function Get_resource_rel_path($resource = null) {
		return 'widgets/' . $this->_template_group . '/' . $resource;
	}

	public function Get_resource_path($resource = null) {
		return $this->core->Get_rel_path('media', $this->Get_resource_rel_path($resource));
	}

	public function Get_resource_url($resource = null) {
		return $this->core->Get_url('media', $this->Get_resource_rel_path($resource));
	}
 }