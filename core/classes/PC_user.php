<?php
/*final class PC_user extends PC_base {
	public function 
	public function Get($id=null) {}
	public function Create($user, $pass, $data=array()) {}
	public function Delete($id_list) {}
	public function Activate() {}
	public function Deactivate() {}
	public function Edit() {}
}*/
define("PC_UF_DEFAULT",			0x00000000);
define("PC_UF_MUST_ACTIVATE",			0x00000001);
define("PC_UF_CONFIRM_PASS_CHANGE",		0x00000002);

class PC_user extends PC_base {
	
	const PC_UF_DEFAULT = 0x00000000;
	
	//session data
	public $Logged_in = false;
	public $ID, $LoginName;
	public $Data = array();
	//post data
	public $Post_login, $Post_password;
	//session data
	public $Session_login, $Session_password;
	
	public $just_logged_in = false;
	
	public function Init() {
		$this->debug = true;
		$this->set_instant_debug_to_file($this->cfg['path']['logs'] . 'pc_user.html', null, 7);
		
		$this->Refresh();
		if (v($_REQUEST['user_logout'])) {
			$this->Logout();
			session_write_close();
			header('Location: '.$this->cfg['url']['base']);
			exit();
		}
		else $this->Login();
	}
	public function Refresh() {
		$this->debug("Refresh()");
		$this->Current_secure = md5($_SERVER['REMOTE_ADDR'].v($_SERVER['HTTP_USER_AGENT']));
		$this->Post_login = v($_POST['user_login']);
		$this->Post_password = $this->Sanitize('password', v($_POST['user_password']));
		if (empty($this->Post_password)) {
			$this->debug(':( Post_password after sanitizing is empty', 1);
		}
		$this->Session_login = v($_SESSION['user_login']);
		$this->Session_password = Sanitize('md5', v($_SESSION['user_password']));
		$this->Session_secure = Sanitize('md5', v($_SESSION['user_secure']));
		return $this;
	}
	public function CheckPassword($pass, $user_id=0) {
		if ($user_id == 0) $user_id = $this->ID;
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}site_users WHERE password=? AND id=? LIMIT 1");
		if( !$r->execute(array($this->Sanitize('password', $pass), $user_id)) )
			return false;
		$f = $r->fetch();
		if( !empty($f) )
			return true;
		return false;
	}
	public function ChangePassword($pass, $user_id=0) {
		if ($user_id == 0) $user_id = $this->ID;
		$pass_code = $this->Sanitize('password', $pass);
		if (emty($pass_code)) {
			return false;
		}
		$r = $this->prepare("UPDATE {$this->db_prefix}site_users SET password=? WHERE id=?");
		if( !$r->execute(array($pass_code, $user_id)) )
			return false;
		if( $this->ID == $user_id ) {
			$_SESSION["user_password"] = $pass_code;
			$this->Session_password =  Sanitize('md5', v($_SESSION['user_password']));
		}
		return true;
	}
	public function Login() {
		$this->debug("Login()");
		$login_field_in_select = 'login';
		$login_field_in_clause = 'login';
		if (isset($this->cfg['site_users']) and v($this->cfg['site_users']['email_as_login'])) {
			$login_field_in_select = 'email as login';
			$login_field_in_clause = 'email';
		}
		if (isset($this->Session_login, $this->Session_password) && $this->Session_secure == $this->Current_secure) {
			$this->debug('session', 1);
			//throw error after trying to login when already logged in: if (isset($this->Post_login, $this->Post_password)) {/*throw error*/}
			$r = $this->prepare("SELECT id,name FROM {$this->db_prefix}site_users WHERE $login_field_in_clause=? AND password=? AND (flags & ?)=0 and banned=0 LIMIT 1");
			$s = $r->execute(array($this->Session_login, $this->Session_password, PC_UF_MUST_ACTIVATE));
			if (!$s) {
				$this->Logout();
				return false;
			}
			if ($r->rowCount() != 1) {
				$this->Logout();
				return false;
			}
			$data = $r->fetch();
			$this->ID = $data['id'];
			$this->LoginName = $this->Session_login;
			$this->Logged_in = true;
			$this->Get_data();
			$this->debug(':) From session');
			return true;
		}
		else {
			$this->debug('not session', 1);
			$using_cookie = false;
			$cookie_code = $this->GetCookie();
			if( $cookie_code !== null ) {
				$using_cookie = true;
				if( $cookie_code !== false ) {
					$r = $this->prepare("SELECT $login_field_in_select, password, flags, banned FROM {$this->db_prefix}site_users WHERE MD5(CONCAT(login,id,password))=? LIMIT 1");
					$s = $r->execute(array($cookie_code));
					if ($s && $r->rowCount() > 0) {
						$data = $r->fetch();
						if( $data["banned"] || ($data["flags"] & PC_UF_MUST_ACTIVATE) != 0 )
							$using_cookie = false; // in case not activated yet we should just ignore login using cookies
						else {
							$this->debug('Setting Post_login from data', 1);
							$this->Post_login = $data["login"];
							$this->Post_password = $data["password"];
						}						
					}
				}
			}
			
			if (!empty($this->Post_login) && !empty($this->Post_password)) {
				$this->debug('Post_login', 1);
				$this->login_attempt = true;
				$query = "SELECT id,name,password FROM {$this->db_prefix}site_users WHERE $login_field_in_clause=? AND (flags & ?)=0 and banned=0 LIMIT 1";
				$query_params = array($this->Post_login, PC_UF_MUST_ACTIVATE);
				$r = $this->prepare($query);
				//echo $this->get_debug_query_string($query, $query_params);
				$s = $r->execute($query_params);
				if (!$s) {
					if( $using_cookie ) $this->DelCookie();
					$this->login_error = 'login';
					$this->debug(':( User not found');
					return false;
				}
				if ($r->rowCount() != 1) {
					if( $using_cookie ) $this->DelCookie();
					$this->login_error = 'login';
					$this->debug(':( Not one user');
					return false;
				}
				$data = $r->fetch();
				if ($data['password'] != $this->Post_password) {
					$this->login_error = 'password';
					$this->debug(':( Wrong password');
					return false;
				}
				$_SESSION['user_login'] = $this->Post_login;
				$_SESSION['user_password'] = $this->Post_password;
				$_SESSION['user_secure'] = $this->Current_secure;
				$this->ID = $data['id'];
				$this->LoginName = $this->Post_login;
				$this->Logged_in = true;
				$this->just_logged_in = true;
                $this->Get_data();
				if( isset($_REQUEST["remember"]) && $_REQUEST["remember"] )
					$this->SetCookie();
				if( isset($_REQUEST["redirect"]) && $_REQUEST["redirect"] ) {
					header('307 Temporary Redirect', true, 307);
					header('Location: ' . $_REQUEST["redirect"]);
					session_write_close();
					exit();
				}
				return true;
			}
			else {
				$this->debug(':( Post_login is empty: ' . $this->Post_login, 1);
			}
			// used cookie, but not logged in ... remove the cookie
			if( $using_cookie ) $this->DelCookie();
		}
	}
	public function Logout() {
		$this->debug('Logout()');
		unset($_SESSION['user_login'], $_SESSION['user_password'], $_SESSION['user_secure']);
		$this->LoginName = '';
		$this->Data = array();
		$this->Logged_in = false;
		if( $this->GetCookie() != null )
			$this->DelCookie();
		return true;
	}
	public function Is_logged_in() {
		$return = (bool)$this->Logged_in;
		$this->debug('Is_logged_in: ' . $return);
		return $return;
	}
	public function GetID() {
		if (!$this->Is_logged_in()) return null;
		return $this->ID;
	}
	public function GetCookieId() {
		return "profiscms4_" . substr(md5($this->core->cfg['salt']), 5, 5);
	}
	public function GetCookie() {
		$cookie_id = $this->GetCookieId();
		if( !isset($_COOKIE[$cookie_id]) )
			return null;
		$code = base64_decode($_COOKIE[$cookie_id]);
		return preg_match('#^[0-9a-f]{32}$#', $code) ? $code : false;
	}
	public function SetCookie($id = null, $login = null, $pass = null) {
		if( $id === null || $login === null || $pass === null ) {
			$data = $this->Get_data();
			$remember_code = rtrim(base64_encode(md5($data["login"] . $data["id"] . $data["password"])), "=");
		}
		else
			$remember_code = rtrim(base64_encode(md5($login . $id. $pass)), "=");
		setcookie($this->GetCookieId(), $remember_code, time() + 86400*365, "/", $_SERVER["HTTP_HOST"]);
	}
	public function DelCookie() {
		setcookie($this->GetCookieId(), "", time() - 3600, "/", $_SERVER["HTTP_HOST"]);
	}
	
	public function Sanitize($type, $input) {
		switch ($type) {
			case 'password':
				if (Validate('password', $input)) return $this->Encode_password($input);
				break;
			default:
				return '';
		}
		return '';
	}
	public function Get_data($user_id=0, $refresh=false, $keys=null) {
		if (!$this->Logged_in) return false;
		if ($user_id == 0) $user_id = $this->ID;
		if ($user_id == $this->ID && !empty($this->Data) && !$refresh) {
			return $this->Data;
		}
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}site_users WHERE id=? LIMIT 1");
		$s = $r->execute(array($user_id));
		if (!$s) return false;
		if ($r->rowCount() != 1) return false;
		$data = $r->fetch();
		if ($user_id == $this->ID) $this->Data = $data;
		return $data;
	}
	public function Set_data($data, $user_id=0) {
		if ($user_id == 0) $user_id = $this->ID;
		$r = $this->prepare("UPDATE {$this->db_prefix}site_users SET name=?, email=? WHERE id=? LIMIT 1");
		if( !$r->execute(Array($data["name"], $data["email"], $user_id)) ) {
			return false;
		}
		return true;
	}
	public function Get_meta_data($keys=null, $user_id=0) {
		if (!is_null($keys) && !is_array($keys)) $keys = Array($keys);
		if ($user_id == 0) $user_id = $this->ID;
		if( !is_numeric($user_id) ) return false;
		$r = $this->prepare("SELECT mkey, mvalue FROM {$this->db_prefix}site_users_meta WHERE id=?" . (is_array($keys) ? (" AND mkey IN (" . implode(",", array_map(Array($this->db, "quote"), $keys)) . ")") : ""));
		if (!$r->execute(array($user_id))) return false;
		$data = Array();
		while ($f = $r->fetch())
			$data[$f["mkey"]] = $f["mvalue"];
		if( !is_null($keys) )
			foreach($keys as $k)
				if( !isset($data[$k]) )
					$data[$k] = null;
		return $data;
	}
	public function Set_meta_data($data, $user_id=0) {
		if ($user_id == 0) $user_id = $this->ID;
		if( !is_numeric($user_id) ) return false;
		if ($this->cfg["db"]["type"] == "mysql") {
			$r_upd = $this->prepare("INSERT INTO {$this->db_prefix}site_users_meta (id, mkey, mvalue) VALUES (?,?,?) ON DUPLICATE KEY UPDATE mvalue=VALUES(mvalue)");
			$r_del = $this->prepare("DELETE FROM {$this->db_prefix}site_users_meta WHERE id=? AND mkey=?");
			foreach ($data as $k => $v) {
				if (is_null($v))
					$r_del->execute(array($user_id, $k));
				else
					$r_upd->execute(array($user_id, $k, $v));
			}
		}
		else {
			$mode = $this->db->getAttribute(PDO::ATTR_ERRMODE);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
			$r_ins = $this->prepare("INSERT INTO {$this->db_prefix}site_users_meta (id, mkey, mvalue) VALUES (?,?,?)");
			$r_upd = $this->prepare("UPDATE {$this->db_prefix}site_users_meta SET mvalue=? WHERE id=? AND mkey=?");
			$r_del = $this->prepare("DELETE FROM {$this->db_prefix}site_users_meta WHERE id=? AND mkey=?");
			foreach ($data as $k => $v) {
				if (is_null($v))
					$r_del->execute(array($user_id, $k));
				else {
					// this is really bad and should be done some other way
					$r_ins->execute(array($user_id, $k, $v));
					$r_upd->execute(array($v, $user_id, $k));
				}
			}
			$this->db->setAttribute(PDO::ATTR_ERRMODE, $mode);
		}
		return true;
	}	
	public function Encode_password($pass) {
		return md5(sha1($pass));
	}
	public function Create($email, $password = '', $retyped_password = '', $name = '', $terms_and_conditions = 0, $captcha=NULL, $login = '', $meta=array()) {
		$this->debug("Create()");
		$this->debug($this->get_callstack(), 3);
		//validate input
		$login_after_create = false;
		$user_model = false;
		
		$r = array(
			'errors' => array()
		);
		
		if (is_array($email)) {
			$data = $email;
			if (is_object($password) and $password instanceof PC_model) {
				$user_model = $password;
			}
			$password = v($email['password']);
			$retyped_password = v($email['retyped_password']);
			$name = v($email['name']);
			$terms_and_conditions = v($email['terms_and_conditions']);
			$captcha = v($email['captcha']);
			$login = v($email['login']);
			$meta = v($email['meta'], array());
			$login_after_create = v($email['login_after_create'], false);
			
			$email = v($email['email']);
			
		}
		if ($user_model) {
			//$user_model->filter($data);
			$validation_data = array();
			$user_model->validate($data, $validation_data);
			$r['errors'] = $validation_data;
			if (count(v($r['errors']))) {
				$this->debug(":( Model not validated", 1);
				return $r;
			}
		}
		else {
			if (!Validate('email', $email)) $r['errors'][] = 'email';
			if (!Validate('password', $password)) $r['errors'][] = 'password';
			if ($password != $retyped_password) $r['errors'][] = 'retyped_password';
			if (!Validate('name', $name)) $r['errors'][] = 'name';
			if (!Validate('name', $login, true)) $r['errors'][] = 'login';
			if (!$terms_and_conditions) $r['errors'][] = 'terms_and_conditions';
			if ($captcha !== NULL && v($_SESSION["captcha_code"], microtime()) != $captcha) $r['errors'][] = 'captcha';
			if (count(v($r['errors']))) {
				$this->debug(":( Captha problems", 1);
				return $r;
			}
			//prepare
			$email = strtolower($email);
			//delete not activatedd accounts first
			$this->Delete_not_activated_accounts();
			//check if user exists
			$r = $this->prepare("SELECT id,email,login FROM {$this->db_prefix}site_users WHERE email=? or login=? LIMIT 1");
			$s = $r->execute(array($email, $login));
			if (!$s) {
				$res['errors'][] = 'database';
				$this->debug($res,1);
				return $r;
			}
			if ($r->rowCount() == 1) {
				$d = $r->fetch();
				if ($d['email'] == $email) $res['errors'][] = 'account_exists';
				if ($d['login'] == $login) $res['errors'][] = 'login_exists';
				$this->debug($res, 1);
				return $res;
			}
		}
		
		
		//prepare
		$now = time();
		$encrypted_password = $this->Encode_password($password);
		$activation_code = md5($email.$this->cfg['salt'].time());
		//create user
		$insert_query = "INSERT INTO {$this->db_prefix}site_users (email,password,name,date_registered,last_seen,confirmation,flags,login,banned) VALUES(?,?,?,?,?,?,?,?,0)";
		$r = $this->prepare($insert_query);
		$flag = PC_UF_MUST_ACTIVATE;
		if (isset($this->cfg['site_users']) and v($this->cfg['site_users']['no_confirmation'])) {
			$flag = PC_UF_DEFAULT;
		}
		$insert_query_params = array($email, $encrypted_password, $name, $now, $now, $activation_code, $flag, $login);
		$s = $r->execute($insert_query_params);
		if (!$s) {
			$res['errors'][] = 'create_account';
			$res['errors'][] = $this->get_debug_query_string($insert_query, $insert_query_params);
			$this->debug($res, 1);
			return $res;
		}
		$account_id = $this->db->lastInsertId($this->sql_parser->Get_sequence('site_users'));
		$this->Set_meta_data($meta, $account_id);
		if( isset($_REQUEST["remember"]) && $account_id )
			$this->SetCookie($account_id, $login, $encrypted_password);
		if ($flag == PC_UF_MUST_ACTIVATE) {
			$this->Send_activation_code($email, $activation_code);
		}
		elseif ($login_after_create) {
			$login_string = $login;
			if (isset($this->cfg['site_users']) and v($this->cfg['site_users']['email_as_login'])) {
				$login_string = $email;
			}
			$_POST['user_login'] = $login_string;
			$_POST['user_password'] = $password;
			$this->Refresh()->Login();
		}
		$this->debug('User has been created', 1);
		
		return array(
			'success'=> true,
			'id'=> $account_id,
			'activationCode'=> $activation_code
		);
	}
	public function Send_activation_code($email, $code) {
		$mail = new PHPMailer();
		$mail->SMTPDebug  = 1;
		$mail->CharSet = "utf-8";
		
		$from_email = $from_name = '';
		
		if (isset($this->cfg['site_users'])) {
			$from_email = v($this->cfg['site_users']['email_sender_email']);
			$from_name = v($this->cfg['site_users']['email_sender_name']);
		}
		if (empty($from_email)) {
			$from_email = v($this->cfg['from_email']);
		}
		if (empty($from_name)) {
			$from_name = v($this->cfg['from_email']);
		}
		
		$mail->SetFrom($from_email, $from_name);
		$mail->AddAddress($email);
		$subject = lang('activation_code');
		$mail->Subject = $subject;
		
		$act_page = $this->page->Get_page("activate-account", true, true);
		if (empty($act_page)) {
			$ctrl_pages = $this->page->Get_by_controller('site_users_registration');
			if (count($ctrl_pages)) {
				$act_page = $this->page->Get_page($ctrl_pages[0]);
			}
		}
		$url = $this->cfg['url']['base'] . $this->site->Get_link($act_page["route"]) . 'activate/' . $code . "/";
		
		$style= '';
		
		$body = $this->core->Get_plugin_variable('email_tpl_activation', 'site_users_registration');
		
		if (empty($body)) {
			$body = lang('activation_code').': {link}';
		}
		
		$markers = array(
			'{link}' => '<a href="'.$url.'">'.$url.'</a>'
		);
		
		$body = str_replace(array_keys($markers), array_values($markers), $body);
		
		$mail->MsgHTML('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
			<html>
				<head>
					<title>' . $subject . '</title>
					<meta http-equiv=Content-Type content="text/html; charset=utf-8">
					' . ($style?"<style><!--\n$style\n--></style>\n\t\t":"") . '</head>
				<body>' . $body . '</body>
			</html>');
		if (!$mail->Send()) return false;
		return true;
	}
	public function Send_pass_change_code($email) {
		include_once $this->core->cfg["path"]["classes"] . "class.phpmailer.php";
		
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}site_users WHERE email=? AND (flags & ?) = 0 LIMIT 1");
		if( !$r->execute(array($email, PC_UF_MUST_ACTIVATE)) ) return false;
		if( $r->rowCount() != 1 ) return false;
		$id = intval($r->fetchColumn());
		do {
			$code = mt_rand(100000000, 999999999);
			$r = $this->prepare("SELECT id FROM {$this->db_prefix}site_users WHERE confirmation=? AND (flags & ?)<>0");
			$r->execute(Array($code, PC_UF_CONFIRM_PASS_CHANGE));
		} while( $r->fetchColumn() );
		
		$r = $this->prepare("UPDATE {$this->db_prefix}site_users SET confirmation=?, flags=flags | ? WHERE id=?");
		$r->execute(Array($code, PC_UF_CONFIRM_PASS_CHANGE, $id));
		
		$from_email = $from_name = '';
		
		if (isset($this->cfg['site_users'])) {
			$from_email = v($this->cfg['site_users']['email_sender_email']);
			$from_name = v($this->cfg['site_users']['email_sender_name']);
		}
		if (empty($from_email)) {
			$from_email = v($this->cfg['from_email']);
		}
		if (empty($from_name)) {
			$from_name = v($this->cfg['from_email']);
		}
		$subject = $this->core->Get_variable('pass_change_confirmation_code', null, 'site_users_pass_change');
		
		$style= '';
		
		$body = $this->core->Get_plugin_variable('email_tpl_pass_change', 'site_users_pass_change');
		
		if (empty($body)) {
			$body = $this->core->Get_variable('pass_change_confirmation_code', null, 'site_users_pass_change').': {code}';
		}
		
		$markers = array(
			'{code}' => $code
		);
		
		$body = str_replace(array_keys($markers), array_values($markers), $body);
		
		
		$params = array(
			'subject' => $subject,
			'from_email' => $from_email,
			'from_name' => $from_name
		);
		
		$message = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
			<html>
				<head>
					<title>' . $subject . '</title>
					<meta http-equiv=Content-Type content="text/html; charset=utf-8">
					' . ($style?"<style><!--\n$style\n--></style>\n\t\t":"") . '</head>
				<body>' . $body . '</body>
			</html>';
		
		
		
		if (!PC_utils::sendEmail($email, $message, $params)) return false;
		return true;
	}
	public function Confirm_password_change($password, $retyped_password, $confirmation_code) {
		$res = Array("errors" => Array());
		$r = $this->prepare("SELECT id FROM {$this->db_prefix}site_users WHERE (flags & ?)<>0 AND confirmation=? LIMIT 1");
		if( !$r->execute(array(PC_UF_CONFIRM_PASS_CHANGE, $confirmation_code)) )
			$res["errors"][] = "pass_change_code";
		else if( $r->rowCount() != 1 )
			$res["errors"][] = "pass_change_code";
		else
			$id = $r->fetchColumn();
		
		if( !Validate('password', $password) )
			$res["errors"][] = "password";
		else if( $password != $retyped_password )
			$res["errors"][] = "retyped_password";
		
		if( empty($res["errors"]) ) {
			$r = $this->prepare("UPDATE {$this->db_prefix}site_users SET password=?, confirmation=NULL, flags=flags & ? WHERE id=?");
			$r->execute(Array($this->Encode_password($password), ~PC_UF_CONFIRM_PASS_CHANGE, $id));
		}
		return $res;
	}
	public function Activate($code, $login=true) {
		if (!Validate('md5', $code)) return false;
		$r = $this->prepare("SELECT id,email,login,password FROM {$this->db_prefix}site_users WHERE confirmation=? AND (flags & ?)<>0 LIMIT 1");
		$s = $r->execute(array($code, PC_UF_MUST_ACTIVATE));
		if (!$s) return false;
		$data = $r->fetch();
		$r = $this->prepare("UPDATE {$this->db_prefix}site_users SET confirmation=null, flags=(flags & ?) WHERE id=?");
		$s = $r->execute(array( ~(PC_UF_MUST_ACTIVATE | PC_UF_CONFIRM_PASS_CHANGE), $data["id"]));
		if (!$s) return false;
		if ($login) {
			$_POST['user_login'] = $data['login'];
			$_POST['user_password'] = $data['password'];
			$this->Refresh()->Login();
		}
		/*kas cia vyksta?
		if( $this->GetCookie() != null )
			$this->Login(); // just in case user does not get himself redirected anywhere*/
		return $data;
	}
	public function Delete_not_activated_accounts($ttl=33200) {
		$r = $this->prepare("DELETE FROM {$this->db_prefix}site_users WHERE (flags & ?)<>0 AND date_registered<?");
		$s = $r->execute(array(PC_UF_MUST_ACTIVATE, time()-$ttl));
		if (!$s) return false;
		return true;
	}
}