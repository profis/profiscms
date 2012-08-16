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
define("PC_UF_MUST_ACTIVATE",			0x00000001);
define("PC_UF_CONFIRM_PASS_CHANGE",		0x00000002);

final class PC_user extends PC_base {
	//session data
	public $Logged_in = false;
	public $ID, $LoginName;
	public $Data = array();
	//post data
	public $Post_login, $Post_password;
	//session data
	public $Session_login, $Session_password;
	
	public function Init() {
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
		$this->Current_secure = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
		$this->Post_login = v($_POST['user_login']);
		$this->Post_password = $this->Sanitize('password', v($_POST['user_password']));
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
		if (isset($this->Session_login, $this->Session_password) && $this->Session_secure == $this->Current_secure) {
			//throw error after trying to login when already logged in: if (isset($this->Post_login, $this->Post_password)) {/*throw error*/}
			$r = $this->prepare("SELECT id,name FROM {$this->db_prefix}site_users WHERE login=? AND password=? AND (flags & ?)=0 and banned=0 LIMIT 1");
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
			return true;
		}
		else {
			$using_cookie = false;
			$cookie_code = $this->GetCookie();
			if( $cookie_code !== null ) {
				$using_cookie = true;
				if( $cookie_code !== false ) {
					$r = $this->prepare("SELECT login, password, flags, banned FROM {$this->db_prefix}site_users WHERE MD5(CONCAT(login,id,password))=? LIMIT 1");
					$s = $r->execute(array($cookie_code));
					if ($s && $r->rowCount() > 0) {
						$data = $r->fetch();
						if( $data["banned"] || ($data["flags"] & PC_UF_MUST_ACTIVATE) != 0 )
							$using_cookie = false; // in case not activated yet we should just ignore login using cookies
						else {
							$this->Post_login = $data["login"];
							$this->Post_password = $data["password"];
						}						
					}
				}
			}
			
			if (!empty($this->Post_login) && !empty($this->Post_password)) {
				$r = $this->prepare("SELECT id,name FROM {$this->db_prefix}site_users WHERE login=? AND password=? AND (flags & ?)=0 and banned=0 LIMIT 1");
				$s = $r->execute(array($this->Post_login, $this->Post_password, PC_UF_MUST_ACTIVATE));
				if (!$s) {
					if( $using_cookie ) $this->DelCookie();
					return false;
				}
				if ($r->rowCount() != 1) {
					if( $using_cookie ) $this->DelCookie();
					return false;
				}
				$data = $r->fetch();
				$_SESSION['user_login'] = $this->Post_login;
				$_SESSION['user_password'] = $this->Post_password;
				$_SESSION['user_secure'] = $this->Current_secure;
				$this->ID = $data['id'];
				$this->LoginName = $this->Post_login;
				$this->Logged_in = true;
				if( isset($_REQUEST["remember"]) && $_REQUEST["remember"] )
					$this->SetCookie();
				return true;
			}
			// used cookie, but not logged in ... remove the cookie
			if( $using_cookie ) $this->DelCookie();
		}
	}
	public function Logout() {
		unset($_SESSION['user_login'], $_SESSION['user_password'], $_SESSION['user_secure']);
		$this->LoginName = '';
		$this->Data = array();
		$this->Logged_in = false;
		if( $this->GetCookie() != null )
			$this->DelCookie();
		return true;
	}
	public function Is_logged_in() {
		return (bool)$this->Logged_in;
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
	public function Create($email, $password, $retyped_password, $name, $terms_and_conditions, $captcha=NULL, $login, $meta=array()) {
		//validate input
		if (!Validate('email', $email)) $r['errors'][] = 'email';
		if (!Validate('password', $password)) $r['errors'][] = 'password';
		if ($password != $retyped_password) $r['errors'][] = 'retyped_password';
		if (!Validate('name', $name)) $r['errors'][] = 'name';
		if (!Validate('name', $login, true)) $r['errors'][] = 'login';
		if (!$terms_and_conditions) $r['errors'][] = 'terms_and_conditions';
		if ($captcha !== NULL && v($_SESSION["captcha_code"], microtime()) != $captcha) $r['errors'][] = 'captcha';
		if (count(v($r['errors']))) return $r;
		//prepare
		$email = strtolower($email);
		//delete not activatedd accounts first
		$this->Delete_not_activated_accounts();
		//check if user exists
		$r = $this->prepare("SELECT id,email,login FROM {$this->db_prefix}site_users WHERE email=? or login=? LIMIT 1");
		$s = $r->execute(array($email, $login));
		if (!$s) {
			$res['errors'][] = 'database';
			return $r;
		}
		if ($r->rowCount() == 1) {
			$d = $r->fetch();
			if ($d['email'] == $email) $res['errors'][] = 'account_exists';
			if ($d['login'] == $login) $res['errors'][] = 'login_exists';
			return $res;
		}
		//prepare
		$now = time();
		$encrypted_password = $this->Encode_password($password);
		$activation_code = md5($email.$this->cfg['salt'].time());
		//create user
		$r = $this->prepare("INSERT INTO {$this->db_prefix}site_users (email,password,name,date_registered,last_seen,confirmation,flags,login,banned) VALUES(?,?,?,?,?,?,?,?,0)");
		$s = $r->execute(array($email, $encrypted_password, $name, $now, $now, $activation_code, PC_UF_MUST_ACTIVATE, $login));
		if (!$s) {
			$res['errors'][] = 'create_account';
			return $res;
		}
		$account_id = $this->db->lastInsertId($this->sql_parser->Get_sequence('site_users'));
		$this->Set_meta_data($meta, $account_id);
		if( isset($_REQUEST["remember"]) && $account_id )
			$this->SetCookie($account_id, $login, $encrypted_password);
		$this->Send_activation_code($email, $activation_code);
		return array(
			'success'=> true,
			'id'=> $account_id,
			'activationCode'=> $activation_code
		);
	}
	public function Send_activation_code($email, $code) {
		include_once $this->core->cfg["path"]["classes"] . "class.phpmailer.php";
		$mail = new PHPMailer();
		$mail->SMTPDebug  = 1;
		$mail->CharSet = "utf-8";
		$mail->SetFrom('no-reply@gov39.ru', 'no-reply@gov39.ru');
		$mail->AddAddress($email);
		$subject = lang('activation_code');
		$mail->Subject = $subject;
		
		$act_page = $this->page->Get_page("activate-account", true, true);
		$url = $this->cfg['url']['base'] . $this->site->Get_link($act_page["route"]) . $code . "/";
		
		$style= '';
		$body = lang('activation_code').': <a href="'.$url.'">'.$url.'</a>';
		
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
		
		$mail = new PHPMailer();
		$mail->SMTPDebug  = 1;
		$mail->CharSet = "utf-8";
		$mail->SetFrom('no-reply@gov39.ru', 'no-reply@gov39.ru');
		$mail->AddAddress($email);
		$subject = lang('pass_change_confirmation_code');
		$mail->Subject = $subject;
		
		$style= '';
		$body = lang('pass_change_confirmation_code').': '.$code;
		
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