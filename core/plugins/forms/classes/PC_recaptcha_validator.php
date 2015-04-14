<?php

class PC_recaptcha_validator {
	
	private $_private_key;
	
	function __construct() {
		global $cfg;
		$this->_private_key = $cfg['forms']['recaptcha_private_key'];
	}

	public function validate() {
		global $cfg;
		
		if( !isset($_REQUEST['g-recaptcha-response']) )
			return false;
		
		$post = 'secret=' . urlencode($this->_private_key) . '&response=' . urlencode($_REQUEST['g-recaptcha-response']) . '&remoteip=' . urlencode($_SERVER["REMOTE_ADDR"]);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'POST /recaptcha/api/siteverify HTTP/1.1',
			'Host: www.google.com',
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($post),
			'Connection: close',
			'User-Agent: cURL/1.0'
		));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

		$response = curl_exec($ch);

		if( curl_errno($ch) ) {
			curl_close($ch);
			return false;
		}
		
		curl_close($ch);
		
		$response = json_decode($response, true);
		
		if( !is_array($response) || !isset($response['success']) )
			return false;
		
		return $response['success'];
	}
}
