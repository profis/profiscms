<?php

class PC_recaptcha_validator {
	
	private $_private_key;
	
	function __construct() {
		global $cfg;
		require_once($cfg['path']['core_plugins'] . 'forms/libs/recaptchalib.php');
		$this->_private_key = $cfg['forms']['recaptcha_private_key'];
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
