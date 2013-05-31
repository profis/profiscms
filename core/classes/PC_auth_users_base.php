<?php


/**
 * Utility class for users
 *
 */
class PC_auth_users_base {
	public function Encode_password($pass, $salt = '') {
		return md5(sha1($pass . $salt));
	}
}

?>
