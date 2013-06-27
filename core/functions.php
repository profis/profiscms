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

function vv(&$var, $default=null) {
	if (!isset($var)) {
		$var = $default;
	}
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

function _debug($key) {
	global $cfg;
	return false;
}

/**
* Function used to add class file and path to runtime variable $class_autoload.
*/
function Register_class_autoloader($class, $path) {
	global $class_autoload;
	$class_autoload[strtolower($class)] = $path;
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
* @param mixed $controller = false - only specified controller pages are being selected.
* @return mixed FALSE if imposible to get tree structure, or tree structure otherwise.
*/
function Get_tree_childs($id, $site_id, $deleted=false, $search=null, $date=false, $additional = array(), $page_tree_params = array(), Page_manager $page_manager = null) {
	global $core, $cfg, $db, $plugins, $sql_parser, $auth;
	
	$logger = new PC_debug();
	//$logger->file = $cfg['path']['logs'] . 'tree/get_tree_childs.html';
	$logger->debug = true;
	
	$logger->set_instant_debug_to_file($cfg['path']['logs'] . 'tree/get_tree_childs_instant.html', null, 5);
	
	$logger->debug("Get_tree_childs($id, $site_id, $deleted, $search, $date)", 1);
	$logger->debug($additional, 1);
	$logger->debug($page_tree_params, 1);
	
	
	$where = array();
	$additional_where = '';
	if (v($additional['plugin_only'])) {
		$where['controller'] = $additional['plugin_only'];
		$additional_where .= ' AND p.controller = ? ';
	}
	
	$accessible_pages_concat_query_for_search_hook = false;
	$accessible_pages_concat_query_params_for_search_hook = false;
	
	if (!empty($search)) {
		$access_conds = array();
		$page_cond_id = '';
		$page_cond_idp = '';
		if (v($page_tree_params['accessible_page_sets'])) {
			v($page_tree_params['accessible_page_sets']['id']);
			v($page_tree_params['accessible_page_sets']['idp']);
			if (is_array($page_tree_params['accessible_page_sets']['id'])) {
				$id_set = "-1";
				if (!empty($page_tree_params['accessible_page_sets']['id'])) {
					$id_set = implode(',', $page_tree_params['accessible_page_sets']['id']);
				}
				$access_conds[] = 'p.id IN (' . $id_set . ')';
			}
			if (is_array($page_tree_params['accessible_page_sets']['idp'])) {
				$idp_set = "-1";
				if (!empty($page_tree_params['accessible_page_sets']['idp'])) {
					$idp_set = implode(',', $page_tree_params['accessible_page_sets']['idp']);
				}
				$access_conds[] = 'p.idp IN (' . $idp_set . ')';
			}
		}
		
		$logger->debug($page_tree_params, 1);
		
		$access_cond = '';
		if (!empty($access_conds)) {
			$access_cond = implode (' OR ', $access_conds);
			if (count($access_conds) > 1) {
				$access_cond = '(' . $access_cond . ')';
			}
			$access_cond .= ' AND';
		}
		
		$page_ids_select = "SELECT ".$sql_parser->group_concat("p.id", array('separator'=>',','distinct'=>true))." ids";
		$page_ids_from = " FROM {$cfg['db']['prefix']}content c";
		$page_ids_join = " JOIN {$cfg['db']['prefix']}pages p ON p.id=pid";
		$page_ids_where = " WHERE $access_cond p.site=?";
		$page_ids_query_params = array($site_id);
		
		$page_ids_query = $page_ids_select . $page_ids_from . $page_ids_join . $page_ids_where;
		
		//if (!empty($access_cond)) {
		if (!$auth->Authorize_superadmin())
			$accessible_pages_concat_query_for_search_hook = $page_ids_select . " FROM {$cfg['db']['prefix']}pages p" . $page_ids_where;
			$accessible_pages_concat_query_params_for_search_hook = $page_ids_query_params;
		//}
							
		$page_ids_query .= " and c.name ".$sql_parser->like("?");
		$page_ids_query_params[] =  '%'.$search.'%';
		
		$r = $db->prepare($page_ids_query);
		$logger->debug('Search page ids query:', 2);
		$logger->debug_query($page_ids_query, $page_ids_query_params, 2);
		$success = $r->execute($page_ids_query_params);
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
				$query_params = array($site_id);
				$logger->debug('Search page query:', 2);
				$logger->debug_query($q, $query_params, 2);
				$success = $r->execute($query_params);
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
			." WHERE p.idp=0 and p.deleted=".($deleted?1:0)." and p.site=? " . $additional_where
			." GROUP BY p.id,p.site,p.idp,p.nr,p.controller,p.front,p.route_lock,p.published,p.hot,p.nomenu,p.deleted,p.date_from,p.date_to,p.redirect,p.date,p.reference_id"
			." ORDER BY p.front desc,p.nr";
			$r = $db->prepare($q);
			$query_params = array_merge(array($site_id), array_values($where));
			$logger->debug('Page tree query:', 2);
			$logger->debug_query($q, $query_params, 2);
			$success = $r->execute($query_params);
		}
		else {
			$q = "SELECT p.*, max(route) route,"
			.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.name'), array('separator'=>'▓','distinct'=>true))." as names,"
			.$sql_parser->group_concat($sql_parser->concat_ws("░",'c.ln','c.route'), array('separator'=>'▓','distinct'=>true))." routes"
			." FROM {$cfg['db']['prefix']}pages pp"
			." JOIN {$cfg['db']['prefix']}pages p ON p.idp=pp.id"
			." LEFT JOIN {$cfg['db']['prefix']}content c ON c.pid=p.id"
			." WHERE pp.id=:id and pp.deleted=".($deleted?1:0)." and p.deleted=".($deleted?1:0).($date!==false?' and p.date'.(!is_null($date)?'>=:date_from and p.date<:date_to':' is null'):'') . $additional_where
			." GROUP BY p.id,p.site,p.idp,p.nr,p.controller,p.front,p.route_lock,p.published,p.hot,p.nomenu,p.deleted,p.date_from,p.date_to,p.redirect,p.date,p.reference_id"
			." ORDER BY p.front desc,p.nr";
			$r = $db->prepare($q);
			$params = array('id'=> $id);
			if ($date !== false && !is_null($date)) {
				$params['date_from'] = strtotime(date('Y-m-d', strtotime($date)));
				$params['date_to'] = $params['date_from']+86400;
			}
			$query_params = array_merge($params, array_values($where));
			$logger->debug('Page tree query:', 2);
			$logger->debug_query($q, $query_params, 2);
			$success = $r->execute($query_params);
		}
	}
	$nodes = array();
	$r_childs = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}pages WHERE idp=? LIMIT 1");
	$r_redirects_from = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}pages WHERE redirect=? LIMIT 1");
	
	if ($success) {
		$list = $r->fetchAll();
		foreach ($list as &$data) {
			if (v($page_tree_params['check_page_children_access']) == true and !$auth->Authorize_access_to_site_page($site_id, $data['id'])) {
				continue;
			}
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
			if (!$data['childs'] && (empty($data['controller']) || $core->Count_hooks('core/tree/get-childs/'.$data['controller']) < 1)) {
				$node['_empty'] = 1;
			}
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
					//$node['children'] = Get_tree_childs($node['id'], $site_id, $deleted, $search, $date, $additional, $page_tree_params);

					
					if (!is_null($page_manager)) {
						$page_manager->debug_level_offset += 4;
						$node['children'] = $page_manager->get_accessible_children(
							$site_id, 
							$node['id'], 
							array('search' => $search,
								'plugin' => '',
								'additional' => $additional,
								'deleted' => $deleted
							)
						);
					}
					
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
					$plugin_controller_tree_icon_path_from_plugins = $data['controller'] . '/images/PC_controller_tree_icon_default.png';
					$plugin_controller_tree_icon = $cfg['path']['plugins'] . $plugin_controller_tree_icon_path_from_plugins;
					
					if (file_exists($plugin_controller_tree_icon)) {
						$node['icon'] = $cfg['url']['base'] . $cfg['directories']['plugins'] . '/' . $plugin_controller_tree_icon_path_from_plugins;
					}
					else {
						$node['icon'] = 'images/controller.png';
					}
					
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
			
			if (v($additional['checkboxes'])) {
				$node['checkbox'] = true;
			}
			$nodes[] = $node;
		}
		unset($data);
	}
	if (!empty($search)) {
		$hook_object = false;
		$core->Init_hooks('core/tree/search', array(
			'search'=> $search,
			'nodes'=> &$nodes,
			'accessible_page_sets' => v($page_tree_params['accessible_page_sets'], false),
			'accessible_pages_concat_query' => $accessible_pages_concat_query_for_search_hook,
			'accessible_pages_concat_query_params' => $accessible_pages_concat_query_params_for_search_hook,
			'logger' => & $logger,
			'hook_object' => &$hook_object
		));
		if ($hook_object) {
			$logger->debug('Debug from hook object:', 1);
			$logger->debug($hook_object->get_debug_string(), 2);
		}
	}
	
	//$logger->file_put_debug();
	
	return $nodes;
}

/**
 * 
 * Function returns false if plugin was not detected or plugin has no appropriate hook.
 * Otherwise function return children array, even empty array() if no children were found. 
 * @global type $core
 * @global type $cfg
 * @global type $node_children
 * @param type $plugin
 * @param type $page_id
 * @param type $additional
 * @param type $level
 * @return boolean
 */
function Get_plugin_page_children($plugin, $page_id, $additional, $level = 0) {
	//pc_shop gov39_phone_directory pc_timeline core/inactive test pc_subscription
	if ($plugin != 'test') {
		//return false;
	}
	global $core, $cfg, $node_children;
	//echo "\n Get_plugin_page_children($plugin, $page_id)" . $cfg['patterns']['plugin_name'];
	
	
	if (preg_match("#^(".$cfg['patterns']['plugin_name'].")/(.+)$#i", $page_id, $m)) {
		//given id is with plugin prefix, what means that we need to generate tree items using that plugin tree renderer
		if (empty($plugin)) {
			$plugin = $m[1];
		}
		$page_id = $m[2];
		//echo "\n    After redefining plugin: $plugin, page_id: $page_id)";
	}

	$plugin_page_key = $plugin . '_' . $page_id;
	if (isset($node_children[$plugin_page_key])) {
		return $node_children[$plugin_page_key];
	}

	if (!empty($plugin)) {
		if ($core->Count_hooks('core/tree/get-childs/'.$plugin) >= 1) {
			//init renderer hooks to generate output results
			
			$core->Init_hooks('core/tree/get-childs/'.$plugin, array(
				'id'=> $page_id,
				'additional'=> &$additional,
				'data'=> &$children
			));
			if (!empty($children)) {
				$node_children[$plugin_page_key] = $children;
				if (v($additional['load_children'])) {
					foreach ($children as $key => $child) {
						if (!isset($child['children']) and isset($child['id'])) {
							$sub_children = Get_plugin_page_children($plugin, $child['id'], $additional, $level + 1);
							if ($sub_children) {
								$children[$key]['children'] = $sub_children;
							}
						}
					}
				}
				return $children;
			}
			return array();
		}
	}
	return false;
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


function random_string($length = 6, $chars = '') {
	if (empty($chars)) {
		$chars = "ABCDEFGHKLMNPRT123456789";
	}
	$code = '';
	for ($i = 0; $i < $length; $i++) {
		$code .= $chars[rand(0, strlen($chars)-1)];
	}
	return $code;
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
* @param string $sqlmask given string to be modified.
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
		$tpl_dir = CMS_ROOT . '/themes/';
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
	$ru = v($_SERVER['REQUEST_URI']);
	$url = explode('/', dirname($_SERVER['PHP_SELF']));
	$_tmp = $url; $url = array(); foreach ($_tmp as $li) { if ($li) { $url[] = $li; } } unset($_tmp);
	$url = array_map('strtolower', $_url = $url);
	$dir_name =  dirname(__FILE__);
	$dir_name = CMS_ROOT;
	$dir = explode('/', str_replace('\\', '/', $dir_name));
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
	$URL_BASE = $httpProtocol.'://'.v($_SERVER['HTTP_HOST']).$URL_REL_BASE;
	
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
		default: return 0;
	}
	return false;
}
//Parse input by type and return parsed value, if input is invalid function returns nothing (NULL)

function PC_translit($input) {
	//translit cyrillic chars
	$input = remove_utf8_accents($input);
	$cyrillic = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я');
	$latin = array('a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','shch','','y','','eh','yu','ya','A','B','V','G','D','E','Yo','Zh','Z','I','J','K','L','M','N','O','P','R','S','T','U','F','H','C','Ch','Sh','Shch','','Y','','Eh','Yu','Ya');
	$input = str_replace($cyrillic, $latin, $input);
	//translit lithuanian chars
	$lithuanian = array('č', 'Č', 'ę','Ę','ė','Ė','į','Į','š','Š','ų','Ų','ū','Ū','ž','Ž');
	$latin = array('c','C','e','E','e','E','i','I','s','S','u','U','u','U','z','Z');
	$input = str_replace($lithuanian, $latin, $input);
	$input = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $input);
	return $input;
}

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
		case 'permalink':
			//extra - allow filenames or not
			//transliteration
			$input = strtolower(PC_translit($input));
			$simple_replacements = array(
				'`' => '',
				"'" => ''
			);
			$input = str_replace(array_keys($simple_replacements), array_values($simple_replacements), $input);
			//normalize structure
			$allow = '';
			if ($type == 'permalink') {
				$allow .= '\/_';
			}
			$patterns[] = '/[^a-z0-9'.($extra?'\\.\\s':'').$allow.']/'; $replacements[] = '-';
			$patterns[] = '/--+/'; $replacements[] = '-';
			$patterns[] = '/^-*(.*?)-*$/'; $replacements[] = '$1';
			//$patterns[] = '/`/'; $replacements[] = '';
			//$patterns[] = '/\'/'; $replacements[] = '';
			$input = preg_replace($patterns, $replacements, $input);
			if (empty($input)) return 'untitled';
			return $input;
			break;
		case 'boolean':
			if ($input == 'false') $input = false;
			if ($extra) return ($input?1:0);
			return ($input?true:false);
			break;
		case 'scripts_and_styles':
			$patterns = array(
				'/<script[^>]*?>[\s\S]*?<\/script>/ui',
				'/<style[^>]*?>[\s\S]*?<\/style>/ui'
			);
			$replacement = '';
			return preg_replace($patterns, $replacement, $input);
			break;
		default:
			return '';
	}
	return '';
}

/**
 * ProfisCMS: function from Wordpress
 * Sanitizes a filename replacing whitespace with dashes
 *
 * Removes special characters that are illegal in filenames on certain
 * operating systems and special characters requiring special escaping
 * to manipulate at the command line. Replaces spaces and consecutive
 * dashes with a single dash. Trim period, dash and underscore from beginning
 * and end of filename.
 *
 * @since 2.1.0
 *
 * @param string $filename The filename to be sanitized
 * @return string The sanitized filename
 */
function sanitize_file_name( $filename ) {
    $filename_raw = $filename;
    $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
    $filename = str_replace($special_chars, '', $filename);
    $filename = preg_replace('/[\s-]+/', '-', $filename);
    $filename = trim($filename, '.-_');
    return $filename;
}

/**
* Function used to format given object to HTML string.
* @param mixed $var given variable to be applied with HTML markup for more human-readable look.
* @param bool $echo = true given indication if formed HTML string should be printed out.
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
* @param string $keys given comma-separated values to be used as variables when calling "PC_core".
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
* @param bool $append_dots indication whether to append dots to the end of text.
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

/** Implodes an array containing values for multiple languages into a single string.
* 
* Array keys pose as language codes and must be 2 latin characters exactly for
* the returned string to be parsable correctly.
* Array values pose as strings for the language code, contained in the key.
* @param array $data An associative array, containing all the values
* @return string Returns an imploded string
* @see Multilang_explode()
*/
function Multilang_implode($data) {
	$str = '';
	foreach( $data as $lng => $dstr )
		$str .= (empty($str) ? '' : '█') . substr($lng, 0, 2) . $dstr;
	return $str;
}

/** Explodes a string having values for several languages into an array.
* @param string $str The string to explode.
* @return array Returns an associative array containing language code as key and string for that language as value.
* @see Multilang_implode()
*/
function Multilang_explode($str) {
	$data = preg_split('#█#u', $str);
	$arr = Array();
	foreach( $data as $dstr )
		$arr[substr($dstr, 0, 2)] = substr($dstr, 2);
	return $arr;
}

function remove_utf8_accents($string) {
	$accents = array(
		'à' => 'a', 'ô' => 'o', 'ď' => 'd', 'ë' => 'e', 'š' => 's', 'ơ' => 'o',
		'ß' => 'ss', 'ă' => 'a', 'ř' => 'r', 'ț' => 't', 'ň' => 'n', 'ā' => 'a', 'ķ' => 'k',
		'ŝ' => 's', 'ỳ' => 'y', 'ņ' => 'n', 'ĺ' => 'l', 'ħ' => 'h', 'ó' => 'o',
		'ú' => 'u', 'ě' => 'e', 'é' => 'e', 'ç' => 'c', 'ẁ' => 'w', 'ċ' => 'c', 'õ' => 'o',
		'ø' => 'o', 'ģ' => 'g', 'ŧ' => 't', 'ș' => 's', 'ė' => 'e', 'ĉ' => 'c',
		'ś' => 's', 'î' => 'i', 'ű' => 'u', 'ć' => 'c', 'ę' => 'e', 'ŵ' => 'w',
		'ū' => 'u', 'č' => 'c', 'ö' => 'oe', 'è' => 'e', 'ŷ' => 'y', 'ą' => 'a', 'ł' => 'l',
		'ų' => 'u', 'ů' => 'u', 'ş' => 's', 'ğ' => 'g', 'ļ' => 'l', 'ƒ' => 'f', 'ž' => 'z',
		'ẃ' => 'w', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', 'ť' => 't',
		'ŗ' => 'r', 'ä' => 'ae', 'í' => 'i', 'ŕ' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o',
		'ē' => 'e', 'ñ' => 'n', 'ń' => 'n', 'ĥ' => 'h', 'ĝ' => 'g', 'đ' => 'd', 'ĵ' => 'j',
		'ÿ' => 'y', 'ũ' => 'u', 'ŭ' => 'u', 'ư' => 'u', 'ţ' => 't', 'ý' => 'y', 'ő' => 'o',
		'â' => 'a', 'ľ' => 'l', 'ẅ' => 'w', 'ż' => 'z', 'ī' => 'i', 'ã' => 'a', 'ġ' => 'g',
		'ō' => 'o', 'ĩ' => 'i', 'ù' => 'u', 'į' => 'i', 'ź' => 'z', 'á' => 'a',
		'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u', 'ĕ' => 'e',

		'А' => 'A',
		'а' => 'a',
		
		
		
		
		'Ә' => 'A','ә' => 'a',    
		'Б' => 'B','б' => 'b',    
		'В' => 'V','в' => 'v',     
		'Г' => 'G','г' => 'g',     
		'Ғ' => 'G','ғ' => 'g',    
		'Д' => 'D','д' => 'd',   
		'Е' => 'E','е' => 'e',    
		'Ё' => 'Yo','ё' => 'yo', 
		'Ж' => 'J','ж' => 'j',    
		'З' => 'Z','з' => 'z',   
		'И' => 'I','и' => 'i',   
		'Й' => 'I','й' => 'i',   
		'К' => 'K','к' => 'k',   
		'Қ' => 'K','қ' => 'k',   
		'Л' => 'L','л' => 'l',     
		'М' => 'M','м' => 'm',     
		'Н' => 'N','н' => 'n',     
		'Ң' => 'N','ң' => 'n',     
		'О' => 'O','о' => 'o',     
		'Ө' => 'O','ө' => 'o',     
		'П' => 'P','п' => 'p',     
		'Р' => 'R','р' => 'r',     
		'С' => 'S','с' => 's',     
		'Т' => 'T','т' => 't',     
		'У' => 'U','у' => 'u',    
		'Ұ' => 'U','ұ' => 'u',  
		'Ү' => 'U','ү' => 'u',  
		'Ф' => 'F','ф' => 'f',  
		'Х' => 'H','х' => 'h',  
		'Ц' => 'T','ц' => 't',  
		'Ч' => 'Ch','ч' => 'ch', 
		'Ш' => 'S','ш' => 's',  
		'Щ' => 'Shch','щ' => 'shch',
		'Ъ' => '','ъ' => '', 
		'Ы' => 'Y','ы' => 'y',  
		'І' => 'Y','і' => 'y',  
		'Ь' => '','ь' => '',  
		'Э' => 'Eh','э' => 'eh', 
		'Ю' => 'Yu','ю' => 'yu',  
		'Я' => 'I','я' => 'I', 
		'Һ' => 'H','һ' => 'h', 
		
		
		'À' => 'a', 'Ô' => 'o', 'Ď' => 'd', 'Ë' => 'e', 'Š' => 's', 'Ơ' => 'o',
		'ß' => 'ss','Ă' => 'a', 'Ř' => 'r', 'Ț' => 't', 'Ň' => 'n', 'Ā' => 'a', 'Ķ' => 'k',
		'Ŝ' => 's', 'Ỳ' => 'y', 'Ņ' => 'n', 'Ĺ' => 'l', 'Ħ' => 'h', 'Ó' => 'o',
		'Ú' => 'u', 'Ě' => 'e', 'É' => 'e', 'Ç' => 'c', 'Ẁ' => 'w', 'Ċ' => 'c', 'Õ' => 'o',
		'Ø' => 'o', 'Ģ' => 'g', 'Ŧ' => 't', 'Ș' => 's', 'Ė' => 'e', 'Ĉ' => 'c',
		'Ś' => 's', 'Î' => 'i', 'Ű' => 'u', 'Ć' => 'c', 'Ę' => 'e', 'Ŵ' => 'w',
		'Ū' => 'u', 'Č' => 'c', 'Ö' => 'oe', 'Ŷ' => 'y', 'Ą' => 'a', 'Ł' => 'l',
		'Ų' => 'u', 'Ů' => 'u', 'Ş' => 's', 'Ğ' => 'g', 'Ļ' => 'l', 'Ƒ' => 'f', 'Ž' => 'z',
		'Ẃ' => 'w', 'Å' => 'a', 'Ì' => 'i', 'Ï' => 'i', 'Ť' => 't',
		'Ŗ' => 'r', 'Ä' => 'ae','Í' => 'i', 'Ŕ' => 'r', 'Ê' => 'e', 'Ü' => 'ue', 'Ò' => 'o',
		'Ē' => 'e', 'Ñ' => 'n', 'Ń' => 'n', 'Ĥ' => 'h', 'Ĝ' => 'g', 'Đ' => 'd', 'Ĵ' => 'j',
		'Ÿ' => 'y', 'Ũ' => 'u', 'Ŭ' => 'u', 'Ư' => 'u', 'Ţ' => 't', 'Ý' => 'y', 'Ő' => 'o',
		'Â' => 'a', 'Ľ' => 'l', 'Ẅ' => 'w', 'Ż' => 'z', 'Ī' => 'i', 'Ã' => 'a', 'Ġ' => 'g',
		'Ō' => 'o', 'Ĩ' => 'i', 'Ù' => 'u', 'Į' => 'i', 'Ź' => 'z', 'Á' => 'a',
		'Û' => 'u', 'Þ' => 'th','Ð' => 'dh', 'Æ' => 'ae',			 'Ĕ' => 'e'
	);
	return str_replace(array_keys($accents), array_values($accents), $string);
}

function pc_sanitize_value($sanitized_value, $filters=array()) {
	if(!is_array($filters))	{
		$filters = explode(',',$filters);
	}	   
	if (is_array($sanitized_value)) {
		foreach ($sanitized_value as $key => $value) {
			$sanitized_value[$key] = pc_sanitize_value($value, $filters);
		}
		return $sanitized_value;
	}	    
	if (!is_array($sanitized_value)){
		foreach ($filters as $filter) {
			switch ($filter) {
				case 'htmlspecialchars':
					if (!is_numeric($sanitized_value)) {
						$sanitized_value = htmlspecialchars($sanitized_value, ENT_QUOTES);
					}
					if(get_magic_quotes_gpc()) {
						$sanitized_value = stripslashes($sanitized_value);
					}
				break;
				case 'htmlentities':
					if (!is_numeric($sanitized_value)) {
						$sanitized_value = htmlentities($sanitized_value, ENT_QUOTES);
					}
					if(get_magic_quotes_gpc()) {
						$sanitized_value = stripslashes($sanitized_value);
					}
				break;
				case 'stripslashes':
				  $sanitized_value = stripslashes($sanitized_value);
				break;
				case 'strip_tags':
				  $sanitized_value = strip_tags($sanitized_value);
				break;
				case 'magic_quotes_strip':
					if(get_magic_quotes_gpc()) {
						$sanitized_value = stripslashes($sanitized_value);
					}
				break;
				case 'mysql':
					if(get_magic_quotes_gpc()) {
						$sanitized_value = stripslashes($sanitized_value);
					}
					$sanitized_value = pc_escape_mysql($sanitized_value);
				break;
				default:
				break;
			}
		}     
	}
	return $sanitized_value;  	
}

/**
 * Method for preparing html output from database
 * @param type $value
 * @return type
 */
function pc_e($value) {
	return pc_sanitize_value($value, array('htmlspecialchars'));
}


function pc_add_trailing_slash(&$url) {
	if (substr($url, -1) != '/') {
		$url .= '/';
	}
	return $url;
}

function pc_remove_trailing_slash(&$url) {
	$url = rtrim($url, '/');
}

function pc_remove_trailing_slash_if_needed(&$url) {
	global $cfg;
	if (v($cfg['router']['no_trailing_slash'])) {
		$url = rtrim($url, '/');
	}
	return $url;
}

function pc_append_route($url, $route = '') {
	if (empty($route)) {
		return $url;
	}
	$route = rtrim($route, '/');
	global $cfg;
	if (!empty($url) and substr($url, -1) != '/') {
		$url .= '/';
	}
	if (v($cfg['router']['no_trailing_slash'])) {
		$url .= $route;
	}
	else {
		$url .= $route . '/';
	}
	return $url;
}

?>