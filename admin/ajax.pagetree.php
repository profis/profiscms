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
$cfg['core']['no_login_form'] = true;
require_once 'admin.php';
if (!$auth->Authorize('core', 'admin')) {
	die('No access');
}
header('Content-Type: application/json');
header('Cache-Control: no-cache');
$out = array();
// ****************************************************************
// PAGES: GET SUBNODES
// input:
//     node: ID (0 = root, -1 = recycle bin)
//     site: current site (defaults to default site)
// output: array of subnodes, each subnode has:
//     id: ID
//     _names: array of ln=>name
//     _empty: 1 if no children
//     ...
if (isset($_POST['node'])) {
	$deleted = v($_POST['deleted'], false);
	if ($deleted == 'false') $deleted = false;
	$deleted = (boolean)$deleted;
	$id = v($_POST['node']);
	$plugin = v($_POST['controller']);
	$site_id = v($_POST['site']);
	$search = v($_POST['searchString'], null);
	$additional = v($_POST['additional']);
	if (!($additional = json_decode($additional, true))) {
		$additional = array();
	}
	//search tree
	if (!empty($search)) {
		$site->Load($site_id);
		$r = Get_tree_childs($id, $site_id, false, $search);
		if ($r) $out = $r;
		//$r = $core->Search($search);
		//---
		/*if (count($r)) {
			foreach ($r as $data) {
				$ids[] = $data['pid'];
				print_pre($data['pid']);
				//$out[] = Parse_page_to_node($data);
			}
		}*/
	}
	//normal load
	else {
		//check if given id belongs to `page` or `plugin specific renderer`
		if (preg_match("#^(".$cfg['patterns']['plugin_name'].")/(.+)$#i", $id, $m)) {
			//given id is with plugin prefix, what means that we need to generate tree items using that plugin tree renderer
			if (empty($plugin)) $plugin = $m[1];
			$id = $m[2];
		}
		$rendered = false;
		if (!empty($plugin)) {
			if ($core->Count_hooks('core/tree/get-childs/'.$plugin) >= 1) {
				$rendered = true;
				//init renderer hooks to generate output results
				$core->Init_hooks('core/tree/get-childs/'.$plugin, array(
					'id'=> $id,
					'additional'=> &$additional,
					'data'=> &$out
				));
			}
		}
		if (!$rendered) if (is_numeric($id) && (int)$id == $id) {
			//given id is page id
			//recycle bin subnodes
			if ($id == -1) {
				$out = Get_tree_childs($id, $site_id, true);
				$out = array_reverse($out); // show last deleted first
			}
			else {
				// ***** NORMAL MODE *****
				$out = Get_tree_childs($id, $site_id, $deleted);
				//if node is root, we need to append static top level nodes (home page, bin, search page and others)
				if ($id == 0) {
					//"Create new page" node
					$i = array(
						'id' => 'create',
						'cls' => 'cms-tree-node-add',
						'draggable' => false,
						'_nosel' => 1,
						'leaf' => true
					);
					$out[] = $i;
					//recycle bin
					$i = array(
						'id' => -1,
						'cls' => 'cms-tree-node-trash',
						'draggable' => false,
						'_nosel' => 1
					);
					$r = $db->prepare("SELECT count(*) FROM {$cfg['db']['prefix']}pages WHERE deleted=1 and site=?");
					$success = $r->execute(array($site_id));
					if ($success) {
						$count = $r->fetchColumn();
						if ($count == 0) {
							$i['_empty'] = 1;
							$i['expandable'] = false;
						}
					}
					$out[] = $i;
				}
			}
		}
	}
	echo json_encode($out);
	return;
}

// ****************************************************************
// TREE: INSERT NEW NODE
// input:
//     new: new node parent ID (0 = root)
//     site: current site
// output:
//     id: new ID
//     _names: empty array
if (isset($_POST['new']) && isset($_POST['site'])) {
	$site_data = $site->Get($_POST['site'], true, true);
	if ($site_data) {
		$r = $db->prepare("SELECT max(nr),front FROM {$cfg['db']['prefix']}pages WHERE idp=? GROUP BY front");
		$r->execute(array($_POST['new']));
		$f = $r->fetchColumn();
		//if ($f[1] > 0) return;
		$r = $db->prepare("INSERT INTO {$cfg['db']['prefix']}pages (site,idp,nr,redirect,published,nomenu,controller,reference_id) values(:site,:idp,:nr,null,1,0,'','')");
		$r->execute(array(
			'site'=> $_POST['site'],
			'idp'=> $_POST['new'],
			'nr'=> $f+1
		));
		$id = $db->lastInsertId($sql_parser->Get_sequence('pages'));
		$out = array(
			'id'=> $id,
			'hot'=> 0,
			'nomenu'=> 0,
			'published'=> 1,
			'controller'=> ''
		);
		$core->Init_hooks('create_page', array(
			'site'=> $_POST['site'],
			'idp'=> $_POST['new'],
			'nr'=> $f+1,
			'id'=> $id
		));
		$out['_names'] = array();
		echo json_encode($out);
		return;
	}
}

// ****************************************************************
// PAGES: DELETE NODE
// input:
//     del: node ID to delete
// output: empty array
if (isset($_POST['del'])) {
	$r = $db->prepare("DELETE FROM {$cfg['db']['prefix']}content WHERE pid=?");
	$r->execute(array($_POST['del']));
	$gallery = new PC_gallery;
	$gallery->Purge_files_in_use(intval($_POST['del']));
	$r= $db->prepare("DELETE FROM {$cfg['db']['prefix']}pages WHERE id=?");
	$r->execute(array($_POST['del']));
	if ($r->rowCount() == 1) {
		echo json_encode($out);
		return;
	}
}

// ****************************************************************
// ARCHIVE: GET
// input:
//     archive: ID to read
// output:
//     ungzipped data field

if (isset($_POST['archive']) && is_numeric($_POST['archive'])) {
	$r = $db->prepare("SELECT * FROM {$cfg['db']['prefix']}content_archive WHERE id=? LIMIT 1");
	$r->execute(array($_POST['archive']));
	if ($cfg['db']['type'] == 'pgsql') $r->bindColumn('data', $archive_data, PDO::PARAM_STR);
	if ($f = $r->fetch()) {
		if ($cfg['db']['type'] == 'pgsql') $f['data'] = preg_replace("#[0-9a-f]{2}#ie", 'chr(hexdec("$0"))', substr($archive_data, 1));
		echo gzuncompress(base64_decode($f['data']));
		return;
	}
}

// ****************************************************************
// ARCHIVE: DELETE
// input:
//     archive_del: JSON array of IDs to delete
//     id: tree id
//     ln: language
// output: empty array

if (isset($_POST['archive_delete'])) {
	$todel = array();
	if ($js = json_decode($_POST['archive_delete']))
		if (is_array($js))
			foreach ($js as $f)
				if (is_numeric($f))
					$todel[] = intval($f);
	if (count($todel)) {
		$where = "tree_id=:id AND ln=:ln";
		$r = $db->prepare("DELETE FROM {$cfg['db']['prefix']}content_archive WHERE $where AND id IN (".implode($todel).")");
		$params = array(
			'id'=> $_POST['id'],
			'ln'=> $_POST['ln']
		);
		$r->execute($params);
		$r = $db->prepare("SELECT a.id,username,a.time"
		." FROM {$cfg['db']['prefix']}content_archive a"
		." LEFT JOIN {$cfg['db']['prefix']}auth_users u on u.id=user_id"
		." WHERE $where");
		$r->execute($params);
		if ($r) {
			while ($f = $r->fetch()) $out[] = array($f['id'], $f['time'], $f['username']);
		}
		echo json_encode($out);
		return;
	}
}