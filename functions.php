<?php
# ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http:#www.gnu.org/licenses/>.

/**
* Function used to simply verify if given value is set. This technique just makes code more elegant.
* It's important to mention, that value given to this function is given by reference. Also this function 
* is able to asign given value if given variable is set, but empty.
* @param mixed $var given variable to check for it's state.
* @param mixed $default given variable to return if given $var is not set.
* @param mixed $if_empty given variable to be returned if given $var is is empty.
* @return mixed given $var if it's set and not empty, given $if_empty if it's not empty and $var is empty, and $default otherwise.
*/
function v(&$var, $default=null, $if_empty='') {
	if (isset($var)) {
		if ($if_empty === '') return $var;
		elseif (empty($var)) return $if_empty;
		else return $var;
	}
	else return $default;
}

/**
* Function used to simply append to runtime variable $cfg key "core" given key-value-pair.
* @param mixed $key given key to be stored in array as key value.
* @param mixed $value given value to be stored in array as value with given key.
*/
function core_set($key, $value) {
	global $cfg;
	$cfg['core'][$key] = $value;
}

/**
* Function used to simply get runtime variable $cfg key "core" values. To this function can be given any number of parameters. In this function is 
* used "func_get_args()" function.
* @return mixed runtime variable $cfg values by key "core", or second given parameter if set.
*/
function core_get() {
	$args = func_get_args();
	global $cfg;
	if (isset($cfg['core'][$args[0]])) return $cfg['core'][$args[0]];
	if (isset($args[1])) return $args[1];
}


/**
* Function used to add class file and path to runtime variable $class_autoload.
*/
function Register_class_autoloader($class, $path) {
	global $class_autoload;
	$class_autoload[$class] = $path;
}

/**
* Function used modify given string to replace special characters used in javascript.
* @param string $str given text to be modified.
* @return string modified string.
*/
function js_escape($str) {
	/* http://www.javascriptkit.com/jsref/escapesequence.shtml
		\b - backspace (08)
		\f - form feed (0C)
		\n - newline (0A)
		\0 - null character (00)
		\r - carriage return (0D)
		\t - horizontal tab (09)
		\v - vertical tab (0B)
		\' - single quote (27)
		\" - double quote (22)
		\\ - backslash (5C)
		\### - octal (0..377) Latin1 symbol
		\x## - hex (00..FF) Latin1 symbol
		\u#### - hex Unicode symbol
		
		\b is unsupported by PHP
		\f, \v are unsupported by PHP < 5.2.5
	*/
	$str = addcslashes($str, "\x00..\x1F'\"\\/");
	$str = str_replace('\a', '\007', $str);
	return '"'.$str.'"';
}

/**
* Function used retrieve given website pages structure from appropriate DB tables. In this method is 
* used runtime variables $cfg, $db, $plugins and $sql_parser.
* @param int $id given page id to render tree for.
* @param int $site_id given site id to render tree for.
* @param bool $deleted given indication if also include marked as "deleted" pages.
* @param mixed $search given search pattern.
* @param mixed $date given date to include tree nodes from.
* @return mixed FALSE if imposible to get tree structure, or tree structure otherwise.
*/
function Get_tree_childs($id, $site_id, $deleted=false, $search=null, $date=false) {
	global $core, $cfg, $db, $plugins, $sql_parser;
	if (!empty($search)) {
		$r = $db->prepare("SELECT ".$sql_parser->group_concat("pid", array('separator'=>',','distinct'=>true))." ids"
		." FROM {$cfg['db']['prefix']}content c JOIN {$cfg['db']['prefix']}pages p ON p.id=pid WHERE p.site=? and c.name ".$sql_parser->like("?"));
		$success = $r->execute(array($site_id, '%'.$search.'%'));
		if ($success) {
			$ids = $r->fetchColumn();
			if (!empty($ids)) {
				$q = "SELECT p.*, max(route) route,"
				.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.name'), array('separator'=>'▓','distinct'=>true))." as names,"
				.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.route'), array('separator'=>'▓','distinct'=>true))." routes"
				." FROM {$cfg['db']['prefix']}pages p"
				." LEFT JOIN {$cfg['db']['prefix']}content c ON c.pid=p.id"
				." WHERE p.deleted=".($deleted?1:0)." and p.site=? and p.id in(".$ids.")"
				." GROUP BY p.id,p.site,p.idp,p.nr,p.controller,p.front,p.route_lock,p.published,p.hot,p.nomenu,p.deleted,p.date_from,p.date_to,p.redirect,p.date,p.reference_id"
				." ORDER BY p.front desc,p.nr";
				$r = $db->prepare($q);
				$success = $r->execute(array($site_id));
			}
		}
	}
	else {
		//bin
		if ($id == -1) {
			$deleted = true;
			$q = "SELECT p.*, max(route) route,"
			.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.name'), array('separator'=>'▓','distinct'=>true))." as names,"
			.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.route'), array('separator'=>'▓','distinct'=>true))." routes"
			." FROM {$cfg['db']['prefix']}pages p"
			." LEFT JOIN {$cfg['db']['prefix']}content c ON c.pid=p.id"
			." WHERE p.deleted=".($deleted?1:0)." and p.site=? and p.idp=0"
			." GROUP BY p.id,p.site,p.idp,p.nr,p.controller,p.front,p.route_lock,p.published,p.hot,p.nomenu,p.deleted,p.date_from,p.date_to,p.redirect,p.date,p.reference_id"
			." ORDER BY p.front desc,p.nr";
			$r = $db->prepare($q);
			$success = $r->execute(array($site_id));
		}
		//root
		else if ($id == 0) {
			$q = "SELECT p.*, max(route) route,"
			.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.name'), array('separator'=>'▓','distinct'=>true))." as names,"
			.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.route'), array('separator'=>'▓','distinct'=>true))." routes"
			." FROM {$cfg['db']['prefix']}pages p"
			." LEFT JOIN {$cfg['db']['prefix']}content c ON c.pid=p.id"
			." WHERE p.idp=0 and p.deleted=".($deleted?1:0)." and p.site=?"
			." GROUP BY p.id,p.site,p.idp,p.nr,p.controller,p.front,p.route_lock,p.published,p.hot,p.nomenu,p.deleted,p.date_from,p.date_to,p.redirect,p.date,p.reference_id"
			." ORDER BY p.front desc,p.nr";
			$r = $db->prepare($q);
			$success = $r->execute(array($site_id));
		}
		else {
			$q = "SELECT p.*, max(route) route,"
			.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.name'), array('separator'=>'▓','distinct'=>true))." as names,"
			.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.route'), array('separator'=>'▓','distinct'=>true))." routes"
			." FROM {$cfg['db']['prefix']}pages pp"
			." JOIN {$cfg['db']['prefix']}pages p ON p.idp=pp.id"
			." LEFT JOIN {$cfg['db']['prefix']}content c ON c.pid=p.id"
			." WHERE pp.id=:id and pp.deleted=".($deleted?1:0)." and p.deleted=".($deleted?1:0).($date!==false?' and p.date'.(!is_null($date)?'>=:date_from and p.date<:date_to':' is null'):'')
			." GROUP BY p.id,p.site,p.idp,p.nr,p.controller,p.front,p.route_lock,p.published,p.hot,p.nomenu,p.deleted,p.date_from,p.date_to,p.redirect,p.date,p.reference_id"
			." ORDER BY p.front desc,p.nr";
			$r = $db->prepare($q);
			$params = array('id'=> $id);
			if ($date !== false && !is_null($date)) {
				$params['date_from'] = strtotime(date('Y-m-d', strtotime($date)));
				$params['date_to'] = $params['date_from']+86400;
			}
			$success = $r->execute($params);
		}
	}
	$nodes = array();
	$r_childs = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}pages WHERE idp=? LIMIT 1");
	$r_redirects_from = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}pages WHERE redirect=? LIMIT 1");
	
	if ($success) {
		$list = $r->fetchAll();
		foreach ($list as &$data) {
			//get childs bool
			$data['childs'] = 0;
			$s = $r_childs->execute(array($data['id']));
			if ($s) $data['childs'] = $r_childs->rowCount();
			
			//get redirects from bool
			$data['redirects_from'] = 0;
			$s = $r_redirects_from->execute(array($data['id']));
			if ($s) $data['redirects_from'] = $r_redirects_from->rowCount();
			
			$node = array();
			//basic data
			$node['id'] = $data['id'];
			$node['controller'] = $data['controller'];
			//explode names
			$_names = explode('▓', $data['names']);
			$node['_names'] = array();
			foreach ($_names as $_name) {
				$_name = explode('░', $_name);
				$node['_names'][$_name[0]] = v($_name[1]);
			}
			//explode routes
			$node['_routes'] = array();
			if (isset($data['routes'])) {
				$_routes = explode('▓', $data['routes']);
				$node['_routes'] = array();
				foreach ($_routes as $_route) {
					$_route = explode('░', $_route);
					$node['_routes'][$_route[0]] = v($_route[1]);
				}
			}
			$node['published'] = $data['published'];
			$node['hot'] = $data['hot'];
			$node['nomenu'] = $data['nomenu'];
			$node['date'] = date('Y-m-d', $data['date']);
			$node['reference_id'] = $data['reference_id'];
			//empty node?
			if (!$data['childs'] && empty($data['controller'])) $node['_empty'] = 1;
			//home page?
			if ($data['front']) {
				$node['_front'] = 1;
				$node['cls'] = 'cms-tree-node-home';
				$node['draggable'] = false;
				$node['leaf'] = true;
			}
			//search page
			elseif ($data['controller'] == 'search') {
				$node['cls'] = 'cms-tree-node-search';
				$node['draggable'] = false;
				$node['leaf'] = true;
			}
			elseif ($data['controller'] == 'menu') {
				if (empty($search)) {
					$node['expanded'] = true;
					//expand menu node automatically
					$node['children'] = Get_tree_childs($node['id'], $site_id);
				}
				$node['iconCls'] = 'cms-tree-node-menu';
			}
			elseif (!$data['published']) $node['iconCls'] = 'cms-tree-node-hidden';
			elseif ($data['hot']) {
				if ($data['nomenu']) $node['iconCls'] = 'cms-tree-node-hot_nomenu';
				else $node['iconCls'] = 'cms-tree-node-hot';
			}
			elseif ($data['nomenu']) $node['iconCls'] = 'cms-tree-node-nomenu';
			
			if (!empty($data['redirect'])) {
				$node['_redir'] = true;
				$node['icon'] = 'images/shortcut.png';
			}
			elseif (!empty($data['controller'])) {
				if ($data['controller'] != 'menu') {
					$node['icon'] = 'images/controller.png';
				}
			}
			//how much redirects from?
			$node['redirects_from'] = $data['redirects_from'];
			//$node['qtip'] = 'ID: '.$data['id'];
			if (!empty($search)) {
				$node['expanded'] = true;
				$node['expandable'] = false;
				$node['children'] = array();
				$node['allowDrag'] = false;
				$node['draggable'] = false;
			}
			$nodes[] = $node;
		}
		unset($data);
	}
	if (!empty($search)) {
		$core->Init_hooks('core/tree/search', array(
			'search'=> $search,
			'nodes'=> &$nodes
		));
	}
	
	return $nodes;
}
/**
* Function checks if given array contains values wiht explicitly defined keys; AKA array associative.
* @param mixed $arr given array to perform check for explicitly defined keys.
* @return bool FALSE if array does not contain explicitly defined keys, or TRUE otherwise.
*/
function array_is_assoc(&$arr) {
	return count(array_diff_assoc(array_keys($arr), array_keys(array_keys($arr)))) > 0;
}

if (!function_exists('json_encode')) {
	/**
	* Function used to JSON encode given object.
	* @param mixed $arr given object to be JSON encoded.
	* @return string encoded in JSON.
	*/
	function json_encode(&$arr) {
		switch (gettype($arr)) {
			case 'boolean':
				return $arr ? 'true' : 'false';
			case 'integer':
			case 'double':
				return $arr;
			case 'array':
				$out = array();
				if (array_is_assoc($arr)) {
					foreach ($arr as $k=>$v)
						$out[] = js_escape($k).':'.json_encode($v);
					return '{'.implode(',', $out).'}';
				} else {
					foreach ($arr as $v)
						$out[] = json_encode($v);
					return '['.implode(',', $out).']';
				}
			case 'NULL':
				return 'null';
			default:
				return js_escape($arr);
		}
	}
}

/**
* Function used to generate random alpha-numeric value of given lenght.
* @param int $len given len of returned value.
* @return string random value.
*/
function random_filename($len = 8) {
	$rv = '';
	$letters = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$n = strlen($letters) - 1;
	while ($len-- > 0)
		$rv .= $letters[rand(0, $n)];
	return $rv;
}

if (!function_exists('hex2bin')) {
	/**
	* Function used to convert given hexadecimal encoded string to binary string.
	* @param string $hex given hexadecimal string to be converted.
	* @return string binary string.
	*/
	function hex2bin($hex) {
		$hex = strtoupper(preg_replace('/[^0-9a-f]/i', '', $hex));
		if (strlen($hex) % 1) $hex = '0'.$hex;
		return implode('', array_map('chr', array_map('hexdec', array_filter(explode(':', chunk_split($hex, 2, ':'))))));
	}
}

/**
* Function used to replace special characters in given string to make given string SQL compatible.
* @param string $mask given string to be modified.
* @return string modified string.
*/
function sqlmask2mask($sqlmask) {
	$sm2m = array(
		'\%' => '%',
		'\_' => '_',
		'%' => '*',
		'_' => '?',
	);
	return strtr($sqlmask, $sm2m);
}

/**
* Function used to replace special characters in given string to make given string PHP compatible
* @param string $mask given string to be modified.
* @return string modified string.
*/
function mask2sqlmask($mask) {
	$m2sm = array(
		'%' => '\%',
		'_' => '\_',
		'*' => '%',
		'?' => '_',
	);
	return strtr($mask, $m2sm);
}

/**
* Function used to store templates information in static variable $TPL_CACHE.
* @return mixed templates information.
*/
function get_themes() {
	static $TPL_CACHE;
	if (!isset($TPL_CACHE)) {
		$TPL_CACHE = array();
		$tpl_dir = dirname(__FILE__).'/themes/';
		foreach (glob($tpl_dir.'*', GLOB_ONLYDIR) as $dir) {
			$dir = substr($dir, strlen($tpl_dir));
			$tpl_cfg = $tpl_dir.$dir.'/config.php';
			unset($theme);
			if (file_exists($tpl_cfg))
				include $tpl_cfg;
			if (!isset($theme))
				$theme = array();
			$theme['theme'] = $dir;
			if (!isset($theme['name']) || $theme['name']=='')
				$theme['name'] = $dir;
			if (!is_array(v($theme['page_types'])))
				$theme['page_types'] = array();
			if (!is_array(v($theme['menus'])))
				$theme['menus'] = array();
			$TPL_CACHE[$dir] = $theme;
		}
	}
	return $TPL_CACHE;
}

/**
* Function used to stop execution. Function "die()" executed inside.
* @param string $s given to be displayed.
*/
function pc_die($s) {
	die('<div style="padding:5px;border:2px dashed #ddd;background:#eee;font:bold 9pt tahoma;color:#666">'.$s.'</div>');
}


// returns parsed & sorted Accept-Language string as a 2D array
/**
* Function used to retrieve server accepted languages sorted array. Array stored as static variable.
* @return mixed array containing server accepted languages.
*/
function get_accept_languages() {
	static $AL_CACHE;
	if (!isset($AL_CACHE)) {
		$AL_CACHE = array();
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$al = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
			foreach ($al as $k=>$v) {
				$q = 1;
				$x = explode(';', $v, 2);
				if (preg_match('/q=([\d.]+)$/', v($x[1]), $m))
					$q = $m[1];
				if (preg_match('/^(\w{2})/', $x[0], $m))
					$AL_CACHE[] = array( // <--- returned array item
						'ln' => strtolower($m[1]),
						'q' => (float)$q,
						'i' => $k
					);
			}
			function _cmp_al($a, $b) {
				$cmp = $b['q'] - $a['q']; // larger q < smaller q
				if ($cmp == 0)
					$cmp = $a['i'] - $b['i']; // smaller i < larger i
				return $cmp==0 ? 0 : $cmp<0 ? -1 : 1;
			}
			usort($AL_CACHE, '_cmp_al');
		}
	}
	return $AL_CACHE;
}

/**
* Function used to retrieve server used protocol name. 
* @return string name of used protocol.
* @see Is_secure_protocol().
*/
function Get_server_protocol() {
	return (Is_secure_protocol()?'https':'http');
}

/**
* Function used to retrieve server indication if server uses secure protocol.
* @return bool TRUE if server uses secure protocol, or FALSE otherwise.
*/
function Is_secure_protocol() {
	return (v($_SERVER['HTTPS'])?($_SERVER['HTTPS']!='off'?true:false):false);
}

/**
* Function used to retrieve requested page base path and url.
* @return mixed array containing requested page base path and url.
*/
function get_dir_url_info() {
	$ru = $_SERVER['REQUEST_URI'];
	$url = explode('/', dirname($_SERVER['PHP_SELF']));
	$_tmp = $url; $url = array(); foreach ($_tmp as $li) { if ($li) { $url[] = $li; } } unset($_tmp);
	$url = array_map('strtolower', $_url = $url);
	$dir = explode('/', str_replace('\\', '/', dirname(__FILE__)));
	$_tmp = $dir; $dir = array(); foreach ($_tmp as $li) { if ($li) { $dir[] = $li; } } unset($_tmp);
	$dir = array_map('strtolower', $_dir = $dir);
	$c2 = count($url); $idx = count($dir); $idxc = 0;
	for ($i = (count($dir) - 1), $c = count($dir); $i >= 0; $i--) {
		if (implode('/', array_slice($dir, $i, $c - $i)) == implode('/', array_slice($url, 0, $c - $i))) {
			$idxc = $c - $i;
			$idx = $i;
		}
		if (($c - $i) >= $c2) break;
	}
	$DIR_DOC_ROOT = ($idx == 0) ? '/' : ('/'.implode('/', array_slice($_dir, 0, $idx)));
	if (preg_match('#^/[^:/]+:/.*$#i', $DIR_DOC_ROOT)) $DIR_DOC_ROOT = substr($DIR_DOC_ROOT, 1);
	$URL_REL_BASE = ($idxc == 0) ? '/' : ('/'.implode('/', array_slice($_url, 0, $idxc)));
	if ($URL_REL_BASE != '/') $URL_REL_BASE .= '/';
	$httpProtocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && mb_strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http';
	$URL_BASE = $httpProtocol.'://'.$_SERVER['HTTP_HOST'].$URL_REL_BASE;
	
	$out = array(
		'url' => array('base'=> $URL_BASE),
		'base_path' => rtrim($URL_REL_BASE, "/").'/'
	);
	return $out;
}

/**
* Function used to retrieve requested page base path and url.
* @param string $type given type of the input.
* @param mixed $input given input to be validated.
* @param bool $extra given extra validation. If TRUE, type "ID" value "0" is valid.
* @param mixed $options given options to coplex input validation.
* @return bool TRUE if given value is valid, or FALSE otherwise.
*/
function Validate($type, $input, $extra=false, $options=array()) {
	switch ($type) {
		case 'route':
			if (preg_match("/[a-ž0-9][a-ž0-9\-_]*[a-ž0-9]/u", $input)) return true;
			break;
		case 'id':
			$input = (int)$input;
			if ($extra) {
				if ($input >= 0) return true;
			} else if ($input > 0) return true;
			break;
		case 'email':
			//regexp: preg_match('/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/i', $input)
			if (filter_var($input, FILTER_VALIDATE_EMAIL)) return true;
			break;
		case 'password':
			if (preg_match('/^.{8,30}$/i', $input)) return true;
			break;
		case 'name':
			$length_from = 2;
			$length_to = 100;
			if (isset($options['length']['from'])) $length_from = (int)$options['length']['from'];
			if (isset($options['length']['to'])) $length_to = (int)$options['length']['to'];
			if ($extra) { if (preg_match("#^.{".$length_from.",".$length_to."}$#u", $input)) return true; }
			elseif (preg_match("#^[\pL][\pL\pN\-. ]{".($length_from-2).",".($length_to-2)."}[\pL\-. ]$#u", $input)) return true;
			break;
		case 'country':
			//tikrinti ar yra sarase
			if (preg_match('/^[a-z]{2,30}$/i', $input)) return true;
			break;
		case 'birth_date':
			if (preg_match("/^(19[0-9]{2}|20[0-9]{2})\.(0[1-9]|1[0-2])\.(0[1-9]|[1-2][0-9]|3[0-1])$/", $input)) {
				$date = explode(".", $input);
				//check if a date is not a future date
				if ($date[0] < date("Y")) {
					//check if the existing date specified
					//note: February in a leap year has 29 days instead of the usual 28
					$leap_year = ($date[0] % 400 == 0 || ($date[0] % 4 == 0 && $date[0] % 100 != 0));
					if ($leap_year && $date[1] == "02") {
						if ($date[2] < 1 || $date[2] > 29) return false;
					}
					else {
						$days_in_a_month = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
						if ($date[2] < 1 || $date[2] > $days_in_a_month[(int)$date[1]]) return false;
					}
					return true;
				}
			}
			break;
		case 'boolean':
			//in this case, boolean could only be 1 or 0 (mainly used in the database, where boolean could only be 1 or 0)
			if ($input == 1 || $input == 0) return true;
			break;
		case 'description':
			//length is same as 'tinytext' data type in mysql
			if (preg_match('/^[a-ž0-9\-_\(\)\[\]&#$\.,\/\\ :%]{0,255}$/ui', $input)) return true;
			break;
		case 'domain': //if extra is true, then only first level domains are allowed (i.e. example.com, yourname.net)
			$pattern = '/^([a-z0-9]([-a-z0-9]*[a-z0-9])?\\.)';
			if ($extra) $pattern .= '+';
			// append TLD pattern
			$pattern .= '((a[cdefgilmnoqrstuwxz]|aero|arpa)|(b[abdefghijmnorstvwyz]|biz)|(c[acdfghiklmnorsuvxyz]|cat|com|coop)|d[ejkmoz]|(e[ceghrstu]|edu)|f[ijkmor]|(g[abdefghilmnpqrstuwy]|gov)|h[kmnrtu]|(i[delmnoqrst]|info|int)|(j[emop]|jobs)|k[eghimnprwyz]|l[abcikrstuvy]|(m[acdghklmnopqrstuvwxyz]|mil|mobi|museum)|(n[acefgilopruz]|name|net)|(om|org)|(p[aefghklmnrstwy]|pro)|qa|r[eouw]|s[abcdeghijklmnortvyz]|(t[cdfghjklmnoprtvwz]|travel)|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw])$/i';
			if (preg_match($pattern, $input)) return true;
		case 'int':
			//options: min_range, max_range
			if (filter_var($input, FILTER_VALIDATE_INT, array('options'=>$options))) return true;
			break;
		case 'md5':
			if (strlen($input) == 32) return true;
			break;
		case 'integer':
			//extra = minimum
			if ((ctype_digit($input) || is_int($input)) && $input >= $extra) return true;
			break;
		default: return false;
	}
	return false;
}
//Parse input by type and return parsed value, if input is invalid function returns nothing (NULL)

/**
* Function used to make given input safe for further operation.
* @param string $type given type of the input.
* @param mixed $input given input to be validated.
* @param bool $extra given extra validation. If TRUE, type "ID" value "0" is valid.
* @return mixed modified value which is compatible for further processing (for example: can be inserted to DB).
*/
function Sanitize($type, $input, $extra=null) {
	switch ($type) {
		case 'email':
			if (Validate('email', $input)) return strtolower($input);
			break;
		case 'md5':
			if (strlen($input) == 32) return $input;
			break;
		case 'filename': $extra = true;
		case 'route':
			//extra - allow filenames or not
			//translit cyrillic chars
			$cyrillic = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я');
			$latin = array('a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','shсh','','y','','eh','yu','ya','A','B','V','G','D','E','Yo','Zh','Z','I','J','K','L','M','N','O','P','R','S','T','U','F','H','C','Ch','Sh','Shсh','','Y','','Eh','Yu','Ya');
			$input = str_replace($cyrillic, $latin, $input);
			//transliteration
			$input = strtolower(iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $input));
			//normalize structure
			$patterns[] = '/[^a-z0-9'.($extra?'\\.\\s':'').']/'; $replacements[] = '-';
			$patterns[] = '/--+/'; $replacements[] = '-';
			$patterns[] = '/^-*(.*?)-*$/'; $replacements[] = '$1';
			$input = preg_replace($patterns, $replacements, $input);
			if (empty($input)) return 'untitled';
			return $input;
			break;
		case 'boolean':
			if ($input == 'false') $input = false;
			if ($extra) return ($input?1:0);
			return ($input?true:false);
			break;
		default:
			return '';
	}
	return '';
}

/**
* Function used to format given object to HTML string.
* @param mixed $var given variable to be applied with HTML markup for more human-readable look.
* @param bool $echo given indication if formed HTML string should be printed out.
* @return string formated HTML string representing given object.
*/
function print_pre($var, $echo=true) {
	$str = '<pre>'.print_r($var, true).'</pre>';
	if ($echo) echo $str;
	else return $str;
}

/**
* Function used to download given file to user.
* @param string $path given path of the file to be downloaded.
*/
function download_file($path) {
  // Must be fresh start
  if (headers_sent()) die('Headers Sent');
  // Required for some browsers
  if(ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
  // File Exists?
  if(file_exists($path)){
    // Parse Info / Get Extension
    $fsize = filesize($path);
    $path_parts = pathinfo($path);
    $ext = strtolower($path_parts["extension"]);
    // Determine Content Type
    switch ($ext) {
      case "pdf": $ctype="application/pdf"; break;
      case "exe": $ctype="application/octet-stream"; break;
      case "zip": $ctype="application/zip"; break;
      case "doc": $ctype="application/msword"; break;
      case "xls": $ctype="application/vnd.ms-excel"; break;
      case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
      case "gif": $ctype="image/gif"; break;
      case "png": $ctype="image/png"; break;
      case "jpeg":
      case "jpg": $ctype="image/jpg"; break;
      default: $ctype="application/force-download";
    }
    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Type: $ctype");
    header("Content-Disposition: attachment; filename=\"".basename($path)."\";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$fsize);
	//while (@ob_end_flush());
    readfile($path);
  } else die('File Not Found');
}

/**
* Function used to get available languages in "PC_core". Runtime variable "core" used.
* @param string $key given comma-separated values to be used as variables when calling "PC_core".
* @return string JSON encoded array of available languages.
* @see PC_core::Get_variable().
* @see json_encode().
*/
function js_lang_array($keys) {
	global $core;
	if( !is_array($keys) )
		$keys = explode(",", $keys);
	$langs = Array();
	foreach($keys as $key) {
		$val = (($k = trim($key)) !== "") ? $core->Get_variable($k) : NULL;
		if( !is_string($val) )
			$val = "";
		$langs[$k] = $val;
	}
	return json_encode($langs);
}

/**
* Function used to get formated text info in current language by given string id.
* @param string $str_id given string id to be retrieved.
* @return string formated text.
* @see PC_core::Get_variable().
*/
function lang($str_id) {
	global $core;
	$rv = $core->Get_variable($str_id);
	if( func_num_args() > 1 ) {
		$args = func_get_args();
		$args[0] = $rv;
		$rv = call_user_func_array("sprintf", $args);
	}
	return $rv;
}


/**
* Function used to get formated text info in current language by given arguments. This function calls "func_get_args()" function.
* Also "lang()" function is called submited argumets returned by "func_get_args()".
* @return mixed value returned by "addcslashes()".
*/
function slang() {
$args = func_get_args(); 
return addcslashes(call_user_func_array("lang", $args), "\r\n\t\"\'\\"); 
}

/**
* Function used to get formated text info in current language by given arguments. This function calls "func_get_args()" function.
* Also "lang()" function is called submited argumets returned by "func_get_args()".
* @return mixed value returned by "htmlspecialchars()".
*/
function qlang() {
$args = func_get_args();
return htmlspecialchars(call_user_func_array("lang", $args)); 
}

/**
* Function used to print formated text info in current language by given arguments. This function calls "func_get_args()" function.
* Also "lang()" function is called submited argumets returned by "func_get_args()".
*/
function elang() {
$args = func_get_args(); 
echo call_user_func_array("lang", $args); 
}

/**
* Function used to print formated text info in current language by given arguments. This function calls "func_get_args()" function.
* Also "slang()" function is called submited argumets returned by "func_get_args()".
*/
function eslang() {
$args = func_get_args(); 
echo call_user_func_array("slang", $args); 
}

/**
* Function used to print formated text info in current language by given arguments. This function calls "func_get_args()" function.
* Also "qlang()" function is called submited argumets returned by "func_get_args()".
*/
function eqlang() {
$args = func_get_args(); 
echo call_user_func_array("qlang", $args);
}

/**
* Function used to get part of of given string to given lenght. This function performs some special chars replacement as well.
* @param string $text given string to be shortened.
* @param int $max_length given lenght of returned string.
* @param string $delimiter given replaced by shorter notation.
* @return string part of given string.
*/
function Get_short_text($text, $max_length=100, $append_dots=true, $delimiter='<span style="display:none" id="pc_page_break">&nbsp;</span>') {
	if (!empty($delimiter)) {
		$replace_delimiter = '╬';
		$text = str_replace($delimiter, $replace_delimiter, $text);
		$delimiter = $replace_delimiter;
	}
	$text = strip_tags($text);
	if (!empty($delimiter)) {
		$delimiter_pos = mb_strpos($text, $delimiter);
		if ($delimiter_pos !== false) {
			$to = $delimiter_pos;
		}
	}
	if (!v($to)) {
		$len = mb_strlen($text);
		if ($len > $max_length) $to = $max_length;
	}
	if (v($to, false) !== false) {
		$text = mb_substr($text, 0, $to);
		if ($append_dots) $dots = (!empty($text)?'...':'');
	}
	if (!$delimiter_pos) $text = preg_replace('#[\.,:;\)\-"\'>\?\!][^\ \.,:;\)\-"\'>\?\!]*$#i', '', $text);
	$text = trim($text, '.,:;- ').v($dots,'');
	return $text;
}

/**
* Function used to encode to hexadecimal given string.
* @param string $str given string to be hexadecimaly encoded.
* @param bool $ent indication if prepend each byte with 'x'. 
* @return string in hexadecimal format.
*/
function Hex_encode($str, $ent = false) {
	$encoded = '';
	$strlen =  function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
	$substr =  function_exists('mb_substr') ? 'mb_substr' : 'substr';
	$ch = $ent ? '&#x' : '%';
	$length = $strlen($str);
	for ($i = 0; $i < $length; $i++)
		$encoded .= $ch.wordwrap(bin2hex($substr($str, $i, 1)), 2, $ch, true).($ent ? ';' : '');
	return $encoded;
}
?>