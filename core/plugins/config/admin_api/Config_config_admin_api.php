<?php

class Config_config_admin_api extends PC_plugin_admin_api {
		
	protected function _set_plugin_name() {
		$this->_plugin_name = 'config';
	}
	
	public function get($controller) {
		
		$model = new PC_config_model();
		$model->absorb_debug_settings($this);
		
		$this->_out['data'] = $model->get_all(array(
			'where' => array(
				'plugin' => $controller,
				'site' => 0,
				
			),
			'key' => 'ckey',
			'value' => 'value'
		));
		
		$this->_out['success'] = true;
	}
	
	public function save($controller) {
		$data = json_decode($_POST['data'], true);
		$this->debug($data);
		
		$model = new PC_config_model();
		$model->absorb_debug_settings($this);
		
		foreach ($data as $key => $value) {
			$model->update(array(
				'value' => $value
			), array(
				'where' => array(
					'plugin' => $controller,
					'ckey' => $key
				)
			));
		}
		
		$this->_out['success'] = true;
	}
	
}

?>
