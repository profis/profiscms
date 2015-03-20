<?php

class Config_config_admin_api extends PC_plugin_admin_api {
		
	protected function _set_plugin_name() {
		$this->_plugin_name = 'config';
	}
	
	public function get($controller) {
		
		$model = new PC_config_model();

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

		$model = new PC_config_model();

		foreach ($data as $key => $value) {
			$model->update(array(
				'value' => $value
			), array(
				'where' => array(
					'plugin' => $controller,
					'ckey' => $key
				)
			));
			if (!empty($controller)) {
				$this->cfg[$controller][$key] = $value;
			}
			$this->core->Init_hooks('plugin/config/after-update/' . $controller . ':' . $key, array(
				'value'=> &$value,
			));
		}
		
		$this->_out['success'] = true;
	}
	
}

?>
