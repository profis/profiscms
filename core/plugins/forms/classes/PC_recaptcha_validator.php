<?php

class PC_recaptcha_validator extends PC_debug{
	
	private $_private_key;
	
	function __construct() {
		global $cfg;
		require_once($cfg['path']['core_plugins'] . 'forms/libs/recaptchalib.php');
		$this->_private_key = $cfg['forms']['recaptcha_private_key'];
		$this->debug = true;
		$this->debug_forced = true;
		$this->set_instant_debug_to_file($cfg['path']['logs'] . 'recpt.html', true);
	}

	public function validate() {
		if (isset($_SESSION['recaptcha']) and 
				$_SESSION['recaptcha'] and 
				$_SESSION['recaptcha']['recaptcha_challenge_field'] == $_POST["recaptcha_challenge_field"] and 
				$_SESSION['recaptcha']['recaptcha_response_field'] == $_POST["recaptcha_response_field"] and 
				$_SESSION['recaptcha']['validated']) {
			$_SESSION['recaptcha'] = false;
			return true;
		}
		$resp = recaptcha_check_answer ($this->_private_key,
			$_SERVER["REMOTE_ADDR"],
			v($_POST["recaptcha_challenge_field"]),
			v($_POST["recaptcha_response_field"]));
		
		$this->debug_time_and_ip();
		$this->debug('Validating ' . v($_POST["recaptcha_challenge_field"]));
		$this->debug('and ' . v($_POST["recaptcha_response_field"]));
		$this->debug('Validation result:  ', 2);
		$this->debug($resp->is_valid, 2);
		
		if ($resp->is_valid) {
			$_SESSION['recaptcha'] = array(
				'recaptcha_challenge_field' => $_POST["recaptcha_challenge_field"],
				'recaptcha_response_field' => $_POST["recaptcha_response_field"],
				'validated' => true
			);
		}
		else {
			$_SESSION['recaptcha'] = false;
		}
		return $resp->is_valid;
	}
	
	
}
