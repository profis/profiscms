<?php
class Forms_recaptcha_api extends PC_plugin_api{
	
	
	public function validate() {
		require_once($this->cfg['path']['core_plugins'] . 'forms/classes/PC_recaptcha_validator.php');
		$recapctha_validator = new PC_recaptcha_validator();
		if ($recapctha_validator->validate()) {
			$this->_out = 'OK';
		}
		else {
			$this->_out = 'FAILED';
		}
	}
	
}
