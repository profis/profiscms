<?php
/** ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/*
new saving system (future):
1) data validation
2) if at least one error occurred - save nothing and return error!
*/
error_reporting(0); //ensure PHP won't output any error data and won't destroy JSON structure
$cfg['core']['no_login_form'] = true; //don't output login form if there's no active session
require_once('admin.php'); //ensure the user is authorized, otherwise stop executing this script
$auth->debug = true;
$auth->set_instant_debug_to_file($cfg['path']['logs'] . 'auth/auth_for_ajax_page_php.html', false, 5);
if (!$auth->Authorize_access_to_pages()) die('No access');
//header('Content-Type: application/json');
header('Cache-Control: no-cache');
$out = array();
//parse actions and return JSON result
$action = isset($_GET['action'])? $_GET['action'] : v($_POST['action']);
$out = array(); //otherwise, if no response data for request found, JSON will return nothing
//get page in all languages

$logger = new PC_debug();
$logger->debug = true;

$logger->debug("Action: " . $action);

require_once 'classes/Page_manager.php';
$page_manager = new Page_manager();

$page_manager->absorb_debug_settings($auth, 5);

$page_id = v($_POST['id']);


if ($action == "get") {
	$page_id = v($_POST['id']);
	if (!$page_manager->is_node_accessible($page_id)) {
		$out = array(
			'error' => 'authorization failed for page_node'
		);
		echo json_encode($out);
		exit;
	}
	$logger->debug('get');
	if (isset($_POST['id'])) if (is_numeric($_POST['id'])) {
		$logger->debug('id is numeric - load page');
		$r = $db->prepare("SELECT p.*,"
		.$sql_parser->group_concat($sql_parser->concat_ws("░", 'rc.pid', 'rc.ln', 'rc.name'), array('distinct'=>true, 'separator'=>'▓'))." redirects_from"
		." FROM {$cfg['db']['prefix']}pages p"
		." LEFT JOIN {$cfg['db']['prefix']}pages rp ON rp.redirect=".$sql_parser->cast('p.id', 'text')
		." LEFT JOIN {$cfg['db']['prefix']}content rc ON rc.pid=rp.id"
		." WHERE p.id=?"
		." GROUP BY p.id,p.site,p.idp,p.nr,p.controller,p.front,p.route_lock,p.published,p.hot,p.nomenu,p.date_from,p.date_to,p.deleted,p.redirect,p.date,p.reference_id");
		$r_content = $db->prepare("SELECT c.*,username FROM {$cfg['db']['prefix']}content c"
		." LEFT JOIN {$cfg['db']['prefix']}auth_users u ON u.id=update_by"
		." WHERE pid=?");
		$success = $r->execute(array($_POST['id']));
		if ($success) {
			$data = $r->fetch();
			if (!is_null($data['date_from'])) $data['date_from'] = $data['date_from'] - $data['date_from'] % 60;
			if (!is_null($data['date_to'])) $data['date_to'] = $data['date_to'] - $data['date_to'] % 60;
			if (empty($data['redirect'])) $data['redirect'] = '';
			if ($data) {
				//explode pages that uses redirects to this page
				if (!empty($data['redirects_from']) && $data['redirects_from'] != '▓') {
					$temp = explode('▓', $data['redirects_from']);
					$data['redirects_from'] = array();
					foreach ($temp as $from) {
						if (empty($from)) continue;
						$from = explode('░', $from);
						$data['redirects_from'][$from[0]][$from[1]] = (isset($from[2])?$from[2]:'');
					}
				} else unset($data['redirects_from']);
				//set output data
				$out = $data;
				//$out['ignore_gmt_offset'] = false;
				//if (v($cfg['ignore_time_zone'])) {
					//$out['ignore_gmt_offset'] = true;
				//}
				//append content in all languages
				$out['content'] = array();
				$r_content->execute(array($_POST['id']));
				if ($r_content) {
					$content = $r_content->fetchAll();
					$r_archive = $db->prepare("SELECT a.id,username,a.time FROM {$cfg['db']['prefix']}content_archive a"
					." LEFT JOIN {$cfg['db']['prefix']}auth_users u ON u.id=user_id"
					." WHERE tree_id=? and a.ln=?");
					foreach ($content as &$data) {
						$data['update_by'] = $data['username'];
						$out['content'][$data['ln']] = $data;
						//append content archive
						$r_archive->execute(array($_POST['id'], $data['ln']));
						while ($archive_data = $r_archive->fetch()) {
							$out['content'][$data['ln']]['archive'][] = $archive_data;
						}
					}
				}
			}
		}
	}
	else {
		$logger->debug('id is not numeric - load plugin page');
		$id =& $_POST['id'];
		$pos = strpos($_POST['id'], '/');
		if ($pos) {
			$ctrl = substr($id, 0, $pos);
			$customId = substr($id, $pos+1);
			if ($plugins->Is_active($ctrl)) {
				//plugin is active
				$out['id'] = $id;
				$out['data'] = false;
				$core->Init_hooks('core/editor/load-page/'.$ctrl, array(
					'id'=> $customId,
					'data'=> &$out['data']
				));
			}
		}
	}
}
//update page (in every language)
elseif ($action == "update") {
	$logger->debug('Update action');
	$data = json_decode($_POST['data'], true);
	$rename_only = v($_POST['rename_only'], false);
	//print_pre($data);return;
	
	if ($data and !$page_manager->is_node_accessible(v($data['id']))) {
		$out = array(
			'error' => 'authorization failed for page_node'
		);
		echo json_encode($out);
		exit;
	}
	if (!$data) $out['errors'][] = 'json';
	else if (!(ctype_digit($data['id']) || is_int($data['id'])) || $data['id'] < 1) {
		$id =& $data['id'];
		$pos = strpos($id, '/');
		if ($pos) {
			$ctrl = substr($id, 0, $pos);
			$customId = substr($id, $pos+1);
			if ($plugins->Is_active($ctrl)) {
				//plugin is active
				$out['id'] = $id;
				$out['data'] = false;
				$out['success'] = false;
				$core->Init_hooks('core/editor/save-page/'.$ctrl, array(
					'id'=> $customId,
					'changes'=> $data,
					'data'=> &$out['data'],
					'rename_only' => $rename_only,
					'success'=> &$out['success'],
					'out'=> &$out
				));
			}
			else $out['errors'][] = 'controller_is_inactive';
		}
		else $out['errors'][] = 'id';
	}
	//if (!ctype_digit($data['id'])) $out['errors'][] = 'id';
	else {
		$r = $db->prepare("SELECT * FROM {$cfg['db']['prefix']}pages WHERE id=?");
		$success = $r->execute(array($data['id']));
		if (!$success) $out['errors'][] = 'database';
		else {
			if ($r->rowCount() != 1) $out['errors'][] = 'id';
			else {
				$_page = $r->fetch();
				$shared_sets = '';
				$shared_queryParams = array();
				$errs_occurred = false;
				$core->Init_hooks('before_page_save', array(
					'changes'=> &$data
				));
				$gmt_offset = v($data['gmt_offset'], 0) * 60;
				foreach ($data as $key=>&$value) {
					if ($key == 'id') continue;
					if ($key == 'content') {
						//saves pages in all languages
						if (!is_array($value) || !count($value)) {
							unset($data[$key]);
							continue;
						}
						foreach ($value as $language=>&$content) {
							//check if language exists in this site
							$site_data = $site->Get($_page['site'], true, true);
							if (!$site_data) {
								unset($value[$language]);
								continue;
							}
							if (!isset($site_data['langs'][$language])) {
								unset($value[$language]);
								continue;
							}
							//get current content data
							$r = $db->prepare("SELECT * FROM {$cfg['db']['prefix']}content WHERE pid=:id AND ln=:ln");
							$r->bindParam('id', $_page['id']);
							$r->bindParam('ln', $language);
							$success = $r->execute();
							if (!$success) {
								unset($value[$language]);
								continue;
							}
							if ($r->rowCount() != 1) {
								//create new for the first time
								$r = $db->prepare("INSERT INTO {$cfg['db']['prefix']}content (pid,ln,name,info,info2,info3,title,keywords,description,route,text,last_update,update_by) values(?,?,'','','','','','','','','',?,0)");
								$success = $r->execute(array($_page['id'], $language, date('Y-m-d H:i:s')));
								if (!$success) {
									unset($value[$language]);
									continue;
								}
								$r = $db->prepare("SELECT * FROM {$cfg['db']['prefix']}content WHERE pid=:id AND ln=:ln");
								$r->bindParam('id', $_page['id']);
								$r->bindParam('ln', $language);
								$success = $r->execute();
								if (!$success) {
									unset($value[$language]);
									continue;
								}
								if ($r->rowCount() != 1) {
									unset($value[$language]);
									continue;
								}
							}
							$current_content = $r->fetch();
							//check if the last update was more than 10 minutes ago, if true, then create a backup
							//if (strtotime($current_content['last_update'])+600 < time()) {
							if (true) {
								$r = $db->prepare("INSERT INTO {$cfg['db']['prefix']}content_archive (tree_id,ln,idp,site,user_id,time,data) VALUES(:id,:ln,:idp,:site,:user_id,:now,:data)");
								$r->execute(array(
									'id'=> $_page['id'],
									'ln'=> $language,
									'idp'=> $_page['idp'],
									'site'=> $_page['site'],
									'user_id'=> $_SESSION['auth_data']['id'],
									'now'=> date('Y-m-d H:i:s', time()-1),
									'data'=> base64_encode(gzcompress(json_encode($current_content), 9))
								));
								//keep only latest 15 backups
								//$r = $db->query("SELECT id FROM content_archive WHERE pid={$_page['id']} AND ln='$language' ORDER BY time DESC LIMIT 20,18446744073709551615");
								$query = "SELECT count(id) FROM {$cfg['db']['prefix']}content_archive WHERE tree_id=? AND ln=? ORDER BY time DESC";
								$r = $db->prepare($query);
								$query_params = array($_page['id'], $language);
								//echo $logger->get_debug_query_string($query, $query_params);
								$success = $r->execute($query_params);
								if ($success) {
									$count = $r->fetchColumn()-15;
									if ($count > 0) {
										$r = $db->prepare("DELETE FROM {$cfg['db']['prefix']}content_archive WHERE tree_id=? AND ln=? ORDER BY time ASC LIMIT ".(int)$count);
										$r->execute(array($_page['id'], $language));
									}
								}
							}
							//update
							$sets = "last_update='".date('Y-m-d H:i:s')."'";
							$queryParams = array();
							//if route is locked
							$set_route_type = '';
							
							
							//check if route should automatically update from page name
							$autoRouteUpdate = true;
							if (isset($data['route_lock'])) {
								if ($data['route_lock']) $autoRouteUpdate = false;
							}
							elseif ($_page['route_lock']) $autoRouteUpdate = false;
							//set user specified route
							$routeUpdated = false;
				
							if (empty($content['route'])) {
								unset($content['route']);
							}
							if (isset($content['route'])) {
								if (empty($content['route']) || $content['route'] == $current_content['route']) {
									unset($content['route']);
									//$out['errors'][] = 'update_route_202';
									//$errs_occurred = true;
								}
								else {
									$generated_route = Get_unique_route(Sanitize('route', $content['route']), $language, $current_content['id']);
									if ($generated_route) {
										$content['route'] = $generated_route;
										$routeUpdated = true;
									}
									else {
										unset($content['route']);
										$out['errors'][] = 'update_route_213';
										$errs_occurred = true;
									}
								}
							}

							//automatically update route
							if (!$routeUpdated && $autoRouteUpdate) {
								$name_for_route = '';
								if (isset($content['name'])) {
									$name_for_route = $content['name'];
								}
								if (empty($name_for_route)) {
									$name_for_route = $current_content['name'];
								}
								if (!empty($name_for_route)) {
									$generated_route = Get_unique_route(Sanitize('route', $name_for_route), $language, $current_content['id']);
									if ($generated_route) {
										$content['route'] = $generated_route;
										$routeUpdated = true;
									}
									else {
										unset($content['route']);
										$out['errors'][] = 'update_route_228';
										$errs_occurred = true;
									}
								}
							}
							
							//print_pre($content);
													
							foreach ($content as $content_key => $content_value) {
								if (v($cfg['seo']['max_' . $content_key]) > 0) {
									if (mb_strlen($content[$content_key]) > $cfg['seo']['max_' . $content_key]) {
										$content[$content_key] = mb_substr($content[$content_key], 0, $cfg['seo']['max_' . $content_key]);
									}
								}
							}
							
							
							
							//all properties
							foreach ($content as $field=>$content_value) {
								if (!in_array($field, $cfg['valid_page_fields'])) {
									unset($content[$field]);
									continue;
								}
								if (in_array($field, array('name'))) {
									$content_value = trim($content_value);
								}
								if (in_array($field, array('info','info2','info3','text'))) {
									//match all gallery images in text
									//match example: ="gallery/admin/id/medium/39"
									

									//Remove empty <a></a> tags (except anchors):
									$patterns = array(
										 '/(<(a)(?![^>]*name\s*=)[^>]*>)(\s*)(<\/a>)/ui',
									);
									
									if (strpos($content_value, 'ymaps.Map') === false) {
										$patterns[] = '/<script[^>]*?>[\s\S]*?<\/script>/ui';
										$patterns[] = '/<style[^>]*?>[\s\S]*?<\/style>/ui';
									}

									$replacement = '';
									$content_value = preg_replace($patterns, $replacement, $content_value);

									
									if (preg_match_all("/=\"".$gallery->config['gallery_directory']."\/".$cfg['directories']['admin']."\/id\/(".$gallery->patterns['thumbnail_type']."\/)?([0-9]+)\"/i", urldecode($content_value), $matches)) {
										$gallery->Update_files_in_use($matches[2], $current_content['id'], $field);
									}
									//force images to append alt="" attributes
									preg_match_all("#<img [^>]+>#ui", $content_value, $m);
									foreach ($m[0] as $src) {
										if (!strpos($src, 'alt=')) {
											$new_src = preg_replace("#(/?>)$#", 'alt="" $1', $src);
											$content_value = str_replace($src, $new_src, $content_value);
										}
									}
								}
								$sets .= ",$field=?";
								//$queryParams[] = $field;
								$queryParams[] = $content_value;
							}
							$sets .= ",update_by=?";
							$queryParams[] = $_SESSION['auth_data']['id'];
							$query = "UPDATE {$cfg['db']['prefix']}content SET $sets WHERE id=".$current_content['id'];
							$r = $db->prepare($query);
							$success = $r->execute($queryParams);
							if (!$success) {
								unset($value[$language]);
								$out['errors'][] = 'update_content_query_266';
								$out['error_data'][] = json_encode($db->errorInfo());
								$out['error_data2'][] = json_encode($r->errorInfo());
								$errs_occurred = true;
							}
						}
						unset($content);
					}
					else {
						if (!in_array($key, $cfg['valid_page_fields'])) {
							unset($data[$key]);
							continue;
						}
						if ($key == 'redirect') if (!preg_match("#^(([0-9]+)?(\#.+?)?(\?)?.+?|http://.+)$#", $value)) $value = null;
						if (!empty($shared_sets)) $shared_sets .= ',';
						if ($key == 'date_from' || $key == 'date_to' || $key == 'date') {
							if (empty($value)) {
								$shared_sets .= "$key=null";
								//$shared_queryParams[] = $key;
							} else {
								$logger->debug('string date: ' . $value, 4);
								if (preg_match("#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#", $value)) $value = strtotime($value);
								$logger->debug('strtotime: ' . $value, 4);
								if ($value > 2147483647) $value = 2147483647;
								$shared_sets .= "$key=?";
								//$shared_queryParams[] = $key;
								//if (v($cfg['ignore_time_zone'])) {
								//	$value -= $gmt_offset;
								//}
								$shared_queryParams[] = $value;
							}
						}
						else {
							$set = true;
							if ($key == 'redirect') {
								if ($value == $_page['id']) {
									unset($data[$key]);
									$out['errors'][] = 'update_redirect_295';
									$errs_occurred = true;
									$set = false;
								}
							}
							elseif ($key == 'front') {
								if ($value > 0) {
									$db->query("UPDATE {$cfg['db']['prefix']}pages SET front=0");
								}
								else {
									unset($data[$key]);
									$out['errors'][] = 'update_front_306';
									$errs_occurred = true;
									$set = false;
								}
							}
							if ($set) {
								$shared_sets .= "$key=?";
								//$shared_queryParams[] = $key;
								$shared_queryParams[] = $value;
							}
						}
					}
				}
				unset($value);
				if (!empty($shared_sets)) {
					$shared_queryParams[] = $_page['id'];
					$r = $db->prepare("UPDATE {$cfg['db']['prefix']}pages SET $shared_sets WHERE id=?");
					$success = $r->execute($shared_queryParams);
					if (!$success) {
						foreach ($data as $key=>&$value) if ($key != 'content') unset($data[$key]);
						$out['errors'][] = 'update_page_settings_326';
						$out['error_data'][] = json_encode($db->errorInfo());
						$out['error_data2'][] = json_encode($r->errorInfo());
						$errs_occurred = true;
					}
				}
				$core->Init_hooks('after_page_save', array(
					'success'=> !$errs_occurred,
					'changes'=> $data
				));
				if ($errs_occurred) {
					$out['errors'][] = 'update';
				}
				else {
					if (v($_POST['return_page'])) {
						$out['names'] = array();
						$r = $db->prepare("SELECT *,id pid FROM {$cfg['db']['prefix']}pages WHERE id=?");
						$s = $r->execute(array($data['id']));
						if ($s) $out = $r->fetch();
						$r = $db->prepare("SELECT * FROM {$cfg['db']['prefix']}content WHERE pid=?");
						$s = $r->execute(array($data['id']));
						if ($s) {
							while ($c = $r->fetch()) {
								$out['content'][$c['ln']] = $c;
								if (isset($c['name'])) $out['names'][$c['ln']] = $c['name'];
							}
						}
						$out['success'] = true;
					}
					else $out['success'] = true;
				}
			}
		}
	}
}
//get content archive
elseif ($action == "get_archive") {
	$pid = v($_POST['pid']);
	
	if (!$page_manager->is_node_accessible($pid)) {
		$out = array(
			'error' => 'authorization failed for page_node'
		);
		echo json_encode($out);
		exit;
	}
	
	$language = $_POST['language'];
	if (!isset($pid) || !is_numeric($pid)) {
		$out['errors'][] = 'pid';
	} else {
		$pid = intval($pid);
		$r = $db->prepare("SELECT a.id,username,a.time"
		." FROM {$cfg['db']['prefix']}content_archive a"
		." LEFT JOIN {$cfg['db']['prefix']}auth_users u on u.id=user_id"
		." WHERE tree_id=? and a.ln=?");
		$out['success'] = true;
		$success = $r->execute(array($pid, $language));
		$out['archive'] = array();
		if ($success) {
			while ($data = $r->fetch()) {
				$out['archive'][] = $data;
			}
		}
	}
}
//get path of the tree node
//No need to check permissions here
elseif ($action == "get_path") {
	$id = $_POST['id'];
	$path = ''; //change to  '/'.$id  to show this node in the path
	while ($id != 0) {
		$r = $db->prepare("SELECT idp FROM {$cfg['db']['prefix']}pages WHERE id=?");
		$success = $r->execute(array($id));
		if (!$success) {
			$out['errors'][] = 'database';
			break;
		}
		if (!$r->rowCount()) {
			$out['errors'][] = 'not_found';
			break;
		}
		$id = $r->fetchColumn();
		$path = '/'.$id.$path;
	}
	if (!count(v($out['errors'], array()))) {
		$out['success'] = true;
		$out['path'] = $path;
	}
}
elseif ($action == 'move') {
	$id = $_POST['id'];
	
	if (!$page_manager->is_node_accessible($id)) {
		$out = array(
			'error' => 'authorization failed for page_node being moved'
		);
		echo json_encode($out);
		exit;
	}
	
	$idp = $_POST['idp'];
	
	if ($idp != 0 and !$page_manager->is_node_accessible($idp)) {
		$out = array(
			'error' => 'authorization failed for page_node new parent'
		);
		echo json_encode($out);
		exit;
	}
	
	$from_idp = $_POST['old_idp'];
	// new nodes order (`nr` mass update)
	if (isset($_POST['new_order']) && is_array($_POST['new_order'])) {
		$r_order = $db->prepare("UPDATE {$cfg['db']['prefix']}pages SET nr=:nr, idp=:idp, deleted=0 WHERE id=:id");
		$general_success = true;
		if ($from_idp == -1) {
			$subids = implode(',', $page->Get_subpages_list($id));
			$r = $db->prepare("UPDATE {$cfg['db']['prefix']}pages SET deleted=0 WHERE id in($subids)");
			$s = $r->execute();
		}
		foreach ($_POST['new_order'] as $k=>$v) {
			$s = $r_order->execute(array(
				'nr'=> $k,
				'idp'=> $idp,
				'id'=> $v
			));
			if (!$s) $general_success = $s;
		}
		if ($general_success) {
			$core->Init_hooks('move_page', array(
				'id'=> $id,
				'from_idp'=> $from_idp,
				'to_idp'=> $idp
			));
		}
		$out['success'] = $general_success;
	}
}
elseif ($action == 'delete') {
	$site_id = v($_POST['site']);
	$id = v($_POST['id']);
	
	if (!$page_manager->is_node_accessible($id)) {
		$out = array(
			'error' => 'authorization failed for page_node'
		);
		echo json_encode($out);
		exit;
	}
	
	$from_idp = v($_POST['old_idp']);
	$success = false;
	
	/*recycle bin `nr` update (last deleted on top)
	$r = $db->prepare("SELECT max(pp.nr) FROM {$cfg['db']['prefix']}pages p LEFT JOIN {$cfg['db']['prefix']}pages pp ON pp.site=p.site WHERE p.id=? AND pp.deleted=1");
	$s = $r->execute(array($id));
	$max = $r->fetchColumn();
	if (!$max) $max = 0;
	*/
	$subids = implode(',', $page->Get_subpages_list($id));
	$r = $db->prepare("UPDATE {$cfg['db']['prefix']}pages SET deleted=1 WHERE id in($subids)");
	$s = $r->execute();
	$r = $db->prepare("UPDATE {$cfg['db']['prefix']}pages SET idp=0 WHERE id=?");
	$s = $r->execute(array($id));
	if ($s) {
		$success = true;
		$core->Init_hooks('move_page', array(
			'id'=> $id,
			'from_idp'=> $from_idp,
			'to_idp'=> '-1'
		));
	}

	if (!$auth->Authorize_access_to_site_page($site_id, $id)) {
		$auth->Make_site_page_accessible($site_id, $id);
		$out['message'] = 'Direct permission added for the page';
	}
	
	$out['success'] = $success;
}
elseif ($action == 'empty_trash') {
	$site_id = v($_POST['site_id']);
	$pids = $page->Get_trashed($site_id);
	foreach ($pids as $key => $pid) {
		if (!$auth->Authorize_access_to_site_page($site_id, $pid)) {
			unset($pids[$key]);
		}
	}
	$pids = $page->Get_subpages_list($pids);
	if (count($pids)) {
		$r = $db->query("SELECT "
		.$sql_parser->group_concat("c.id", array('separator'=>','))." routes"
		." FROM {$cfg['db']['prefix']}pages p LEFT JOIN {$cfg['db']['prefix']}content c ON pid=p.id WHERE p.id in(".implode(',', $pids).")");
		$s = $r->execute();
		if ($s) {
			$cids = $r->fetchColumn();
			if (!empty($cids)) $r = $db->query("DELETE FROM {$cfg['db']['prefix']}gallery_files_in_use WHERE content_id in(".$cids.")");
		}
		$r = $db->query("DELETE FROM {$cfg['db']['prefix']}content WHERE pid in(".implode(',', $pids).")");
		$r = $db->query("DELETE FROM {$cfg['db']['prefix']}content_archive WHERE tree_id in(".implode(',', $pids).")");
		$r = $db->query("DELETE FROM {$cfg['db']['prefix']}pages WHERE id in(".implode(',', $pids).")");
		if ($r) $out['success'] = true;
		else $out['success'] = false;
	}
	else $out['success'] = true;
}
else $out['errors'][] = 'unknown_action';
//echo '<pre>'.htmlspecialchars(print_r($out, true)).'</pre>';
echo json_encode($out);

$logger->debug('Ajax output:');
$logger->debug($out);
$logger->file_put_debug($cfg['path']['base'] . 'logs/ajax/page.html');
//echo $logger->get_debug_string();