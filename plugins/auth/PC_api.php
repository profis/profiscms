<?php
if (!($core instanceof PC_core)) exit;
header('Content-Type: application/json');
header('Cache-Control: no-cache');

function _auth_api_extract_group_id($id) {
	if (!preg_match("#^(group_)?([0-9]+)$#", $id, $m)) return false;
	return $m[2];
}

require_once $cfg['path']['classes'].'crypt_rsa_bcmath.php';
if (!v($math, false)) $math = new Crypt_RSA_Math_BCMath();

$out = array();
switch (v($_GET['action'])) {
	case 'get_config':
		/**
		 * Generate Diffie-Hellman parameters
		 * dhke = Diffie-Hellman key exchange
		 */
		if (!isset($_SESSION['dhke']['p'], $_SESSION['dhke']['g'], $_SESSION['dhke']['a'], $_SESSION['dhke']['aa'])) {
			if (!function_exists('huge_rand')) {
				function huge_rand($limit) {
					$n = strlen($limit) - 2;
					$o = rand(1, 9);
					for ($i=0; $i<$n; $i++)
						$o .= rand(0, 9);
					return $o;
				}
			}
			$_SESSION['dhke']['p'] = '79615414400960334200212853610324162335962070852220871118866290591665222687243';
			$_SESSION['dhke']['g'] = huge_rand($_SESSION['dhke']['p']);
			$_SESSION['dhke']['a'] = huge_rand($_SESSION['dhke']['p']);
			$_SESSION['dhke']['aa'] = $math->powmod($_SESSION['dhke']['g'], $_SESSION['dhke']['a'], $_SESSION['dhke']['p']);
		}
		$out['dhke'] = array(
			'p' => bin2hex(strrev($math->int2bin($_SESSION['dhke']['p']))),
			'g' => bin2hex(strrev($math->int2bin($_SESSION['dhke']['g']))),
			'aa' => bin2hex(strrev($math->int2bin($_SESSION['dhke']['aa'])))
		);
		
		$descriptions = array(
			'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
			'Vestibulum volutpat nulla a turpis',
			'Cras imperdiet dapibus lorem',
			'Nunc accumsan purus sed',
			'Aenean nisi sapien',
			'Quisque eu lectus ante',
			'Donec commodo aliquet elit',
			'Proin varius nisl ac nulla lacinia scelerisque non et tellus',
			'Vestibulum lobortis dictum urna a pellentesque'
		);
		
		//permission types
		$r = $auth->permissions->Get();
		$perms = array();
		foreach ($r as $d) {
			$perms[] = array(
				$d['plugin'],
				$d['name'],
				$descriptions[rand(0, count($descriptions)-1)]
			);
		}
		$out['perms'] = $perms;
		break;
	case 'get_tree':
		$groups = $auth->groups->Get();
		foreach ($groups as &$g) {
			$g['type'] = 'group';
			$g['draggable'] = false;
			$g['icon'] = 'images/group.png';
			$g['text'] = $g['groupname'];
			$g['expanded'] = true;
			$g['children'] = array();
			$users = $auth->users->Get(null, $g['id']);
			if (count($users)) {
				foreach ($users as $user) {
					$g['children'][] = array(
						'id'=> $user['id'],
						'type'=> 'user',
						'icon'=> 'images/user.png',
						'leaf'=> true,
						'text'=> $user['username']
					);
				}
			}
			$g['id'] = 'group_'.$g['id'];
		}
		$out = $groups;
		break;
	//users
	case 'get_user':
		$id = v($_POST['id']);
		$d = $auth->users->Get($id);
		if ($d) {
			unset($d['pass']);
			$d['type'] = 'user';
			$d['permissions'] = array();
			$r = $auth->permissions->Get_by_user($id);
			if ($r) {
				if (count($r)) foreach ($r as $perm) {
					$d['permissions'][$perm['plugin']][$perm['name']] = $perm['data'];
				}
			}
			$out = $d;
		}
		break;
	case 'edit_user':
		$id = v($_POST['id']);
		$name = v($_POST['name']);
		$language = v($_POST['language'], null);
		if (isset($_POST['pass_aes'])) {
			$bb = $math->bin2int(strrev(hex2bin(v($_POST['bb']))));
			$key = strrev(str_pad($math->int2bin($math->powmod($bb, $_SESSION['dhke']['a'], $_SESSION['dhke']['p'])), 32, "\0"));
			$iv = hex2bin(v($_POST['iv']));
			$pass_aes = hex2bin(v($_POST['pass_aes']));
			$password = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $pass_aes, MCRYPT_MODE_CBC, $iv), "\0");
			if (empty($password)) {
				$password = null;
			}
		}
		else $password = null;
		if ($out['success'] = $auth->users->Edit($id, $name, $language, $password)) {
			//save permissions
			$permissions = v($_POST['permissions'], "");
			$perms_list = json_decode($permissions, true);
			$out['permissions_saved'] = false;
			if ($perms_list) {
				foreach ($perms_list as $plugin=>$data_array) {
					foreach ($data_array as $name=>$data) {
						$out['permissions_saved'] = $auth->permissions->Save_for_user($id, $plugin, $name, $data);
					}
				}
			}
			//load user & fresh permissions
			$out['data'] = $auth->users->Get($id);
			$out['data']['type'] = 'user';
			$out['data']['permissions'] = array();
			$r = $auth->permissions->Get_by_user($id);
			if ($r) {
				if (count($r)) foreach ($r as $perm) {
					$out['data']['permissions'][$perm['plugin']][$perm['name']] = $perm['data'];
				}
			}
		}
		break;
	case 'create_user':
		//$id = v($_POST['id']);
		$name = v($_POST['name']);
		$language = v($_POST['language'], null);
		$group_id = _auth_api_extract_group_id(v($_POST['group_id']));
		if (!isset($_POST['pass_aes'])) $out['success'] = false;
		else {
			$bb = $math->bin2int(strrev(hex2bin(v($_POST['bb']))));
			$key = strrev(str_pad($math->int2bin($math->powmod($bb, $_SESSION['dhke']['a'], $_SESSION['dhke']['p'])), 32, "\0"));
			$iv = hex2bin(v($_POST['iv']));
			$pass_aes = hex2bin(v($_POST['pass_aes']));
			$password = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $pass_aes, MCRYPT_MODE_CBC, $iv), "\0");
			if (empty($password)) {
				$out['success'] = false;
			}
			else {
				$s = $auth->users->Create($name, $language, $password, $group_id);
				$out['success'] = (bool)$s;
				if ($s) {
					$out['data'] = $auth->users->Get($s);
					$out['data']['type'] = 'user';
					$out['data']['permissions'] = array();
					$r = $auth->permissions->Get_by_user($s);
					if ($r) {
						if (count($r)) foreach ($r as $perm) {
							$out['data']['permissions'][$perm['plugin']][$perm['name']] = $perm['data'];
						}
					}
				}
			}
		}
		
		break;
	case 'delete_user':
		$id = v($_POST['id']);
		$out['success'] = $auth->users->Delete($id);
		break;
	case 'change_user_group':
		$id = v($_POST['id']);
		$group_id = _auth_api_extract_group_id(v($_POST['group_id']));
		$out['success'] = $auth->users->Change_group($id, $group_id);
		break;
	//groups
	case 'get_group':
		$id = _auth_api_extract_group_id(v($_POST['id']));
		if ($id !== false) {
			$d = $auth->groups->Get($id);
			if ($d) {
				$d['type'] = 'user';
				$out = $d;
			}
		}
		break;
	case 'edit_group':
		$id = _auth_api_extract_group_id(v($_POST['id']));
		if ($id === false) {
			$out['success'] = false;
			$out['error'] = 'id';
		}
		else {
			$name = v($_POST['name']);
			//$permissions = json_decode($permissions, true);
			$s = $auth->groups->Edit($id, $name);
			if ($out['success'] = $s) {
				$out['data'] = $auth->groups->Get($id);
			}
		}
		break;
	case 'create_group':
		$name = v($_POST['name']);
		$s = $auth->groups->Create($name);
		$out['success'] = (bool)$s;
		if ($s) {
			$out['data'] = $auth->groups->Get($s);
		}
		break;
	case 'delete_group':
		$id = _auth_api_extract_group_id(v($_POST['id']));
		$out['success'] = $auth->groups->Delete($id);
		break;
}
if (!isset($dont_send_json)) echo json_encode($out);