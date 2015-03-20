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
error_reporting(0); //ensure PHP won't output any error data and won't destroy JSON structure
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

$cfg['core']['no_login_form'] = true; //don't output login form if there's no active session

require_once('admin.php'); //ensure the user is authorized, otherwise stop executing this script
if (!$auth->Authorize('core', 'admin') and !$auth->Authorize('core', 'access_admin')) {
	die('No access');
}

$gallery = new PC_gallery;
header('Cache-Control: no-cache');
# Parse gallery actions and return JSON result
$action = isset($_GET['action'])? $_GET['action'] : v($_POST['action']);

if ($action != 'debug_tree') header('Content-Type: application/json');
$output = array(); //otherwise, if no response data for request found, JSON will return nothing
# Categories
if ($action == "get_categories") {
	$parent = $_POST['node'];
	$trashed = (bool)v($_POST['trashed']);
	if (ctype_digit($parent) || $parent == '0') {
		$result = $gallery->Get_categories($parent, $trashed);
		if ($result && count($result['categories'])) {
			foreach ($result['categories'] as $category) {
				$temp = array(
					'trashed'=>(v($category['trashed'])>0?1:0),
					//'leaf'=>($category['rgt']-$category['lft']<=1),
					'id'=>$category['id'],
					'category'=>$category['category'],
					'text'=>$category['category'],
					'size'=>number_format($category['size'] /1024/1024, 2),
					'path'=>$category['path'],
					'iconCls'=>'gallery_folder'
					);
				if ($category['rgt']-$category['lft']<=1) {
					$temp['children'] = array();
					$temp['expanded'] = true;
				}
				$output[] = $temp;
			}
		}
	}
	elseif ($parent != 'bin') {
		$categories = array();
		$result = $gallery->Get_categories();
		if (v($result['success'])) {
			foreach ($result['categories'] as $category) {
				$temp = array(
					'trashed'=>0,
					//'leaf'=>($category['rgt']-$category['lft']<=1),
					'id'=>$category['id'],
					'category'=>$category['category'],
					'text'=>$category['category'],
					'size'=>number_format($category['size'] /1024/1024, 2),
					'path'=>$category['path'],
					'iconCls'=>'gallery_folder'
				);
				if ($category['rgt']-$category['lft']<=1) {
					$temp['children'] = array();
					$temp['expanded'] = true;
				}
				$categories[] = $temp;
			}
		}
		$output[] = array(
			'id'=>'0','category'=>'Gallery','size'=>'',
			'children'=>$categories,'iconCls'=>'gallery-drive-icon',
			'path'=>'',
			'expanded'=>true,'draggable'=>false,
			'editable'=>false
		);
		$output[] = array(
			'id'=>'bin','category'=>'Trash','size'=>'',
			'iconCls'=>'gallery_bin','draggable'=>false,
			'leaf'=>false,'editable'=>false
		);
	}
	else {
		$result = $gallery->Get_categories($parent, true);
		if (isset($result['success']) && $result['success']) {
			foreach ($result['categories'] as $category) {
				$output[] = array(
					//'leaf'=>($category['rgt']-$category['lft']<=1),
					'trashed'=>1,
					'id'=>$category['id'],
					'category'=>$category['category'],
					'text'=>$category['category'],
					'size'=>number_format($category['size'] /1024/1024, 2),
					'path'=>$category['path'],
					'iconCls'=>'gallery_folder'
				);
			}
		}
	}
}
elseif ($action == "create_category") {
	$result = $gallery->Create_category($_POST['category'], $_POST['parent']);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}
elseif ($action == "rename_category") {
	$category_id = $_POST['category_id'];
	$category = $_POST['category'];
	$old_category = $_POST['old_category'];
	$result = $gallery->Rename_category($category_id, $category, $old_category);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}
elseif ($action == "delete_category") {
	$category_id = $_POST['category_id'];
	$result = $gallery->Delete_category($category_id);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}

# Files
elseif ($action == "get_files") {
	$category_id = v($_POST['category_id']);
	$trashed = (bool)v($_POST['trashed']);
	$filter = v($_POST['filter']);
	if (!empty($filter) || $category_id != 'bin' && !$trashed) {
		$result = $gallery->Get_files($category_id, $filter);
	}
	else {
		$result = $gallery->Get_trashed_files($category_id);
	}
	if (isset($result['success']) && $result['success']) {
		//print_pre($result);
		if (count($result['files'])) {
			if ($category_id != 'bin' && !$trashed) {
				//echo 'not trashed ';
				$size_in_bytes_max_length = 20;
				$size_in_bytes_prefix = '000000000000000000000000000000000';
				foreach ($result['files'] as &$file) {
					//echo '<pre>'; print_r($file); echo '</pre>';
					if (!empty($file['path'])) $file['path'] .= '/';
					else $file['path'] = '';
					$filetype = $gallery->filetypes[$file['extension']];
					$size_in_bytes = v($file['size']);
					$size_in_bytes_length = strlen($size_in_bytes);
					if ($size_in_bytes_length < $size_in_bytes_max_length) {
						$size_in_bytes = substr($size_in_bytes_prefix, 0, $size_in_bytes_max_length - $size_in_bytes_length) . $size_in_bytes;
					} 
					$output[] = array(
						'id'=>$file['id'],
						'name'=>$file['filename'],
						'extension'=>$file['extension'],
						'filetype'=>$filetype,
						'path'=>$file['path'],
						'category'=>v($file['category']),
						'size'=>($file['size']<307200?number_format($file['size'] /1024).' KB':number_format($file['size'] /1024/1024, 2).' MB'),
						'size_in_bytes'=>$size_in_bytes,
						'modified'=>date('Y-m-d H:i', $file['date_modified']),
						'in_use'=>($file['in_use']>0)
					);
				}
			}
			else {
				foreach ($result['files'] as $key => &$file) {
					if (!$file['id']) {
						unset($result['files'][$key]);
						continue;
					}
					if (!empty($file['path'])) $file['path'] .= '/';
					else $file['path'] = '';
					$file_type = '';
					if (isset($gallery->filetypes) and isset($file['extension'])) {
						$file_type = $gallery->filetypes[$file['extension']];
					}
					$file['filetype'] = $file_type;
					$file['name'] = $file['filename'];
					$file['size'] = ($file['size']<307200?number_format($file['size'] /1024).' KB':number_format($file['size'] /1024/1024, 2).' MB');
					$file['modified'] = date('Y-m-d H:i', $file['date_modified']);
					$file['in_use'] = ($file['in_use']>0);
				}
				$output = $result['files'];
			}
		}
	}
	else $output = $result;
	//print_r($output);
}
elseif ($action == "upload_file") {
	$category_id = v($_POST['category_id']);
	if (empty($category_id)) {
		$category_id = v($_GET['category_id']);
	}
	$result = $gallery->Upload_file($category_id, $_FILES['Filedata']);
	if (!v($result['success'])) {
		$output['success'] = false;
		// return last error from the list
		$output['error'] = $result['errors'][count($result['errors'])-1];
	}
	else $output = $result;
}
elseif ($action == "delete_file") {
	$file_id = $_POST['file_id'];
	$result = $gallery->Delete_file($file_id);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}
elseif ($action == "delete_files") {
	$file_ids = $_POST['file_ids'];
	$files = explode(',', $file_ids);
	$succeeded = 0;
	$failed = 0;
	for ($a=0; isset($files[$a]); $a++) {
		$result = $gallery->Delete_file($files[$a]);
		if (v($result['success'])) {
			$succeeded++;
			$output['results']['items'][] = array('succeeded'=> true, 'title'=> $result['filename']);
		}
		else {
			$failed++;
			$output['results']['items'][] = array('succeeded'=> false, 'title'=> 'file id '.$images[$a], 'errors'=> $result['errors']);
		}
	}
	$output['results']['succeeded'] = $succeeded;
	$output['results']['failed'] = $failed;
	$output['success'] = true;
}
elseif ($action == 'crop_image') {
	$result = $gallery->Crop_thumbnail($_POST['image_id'], $_POST['thumbnail_type'], $_POST['start_x'], $_POST['start_y'], $_POST['width'], $_POST['height']);
	$output = $result;
}
elseif ($action == 'rename_file') {
	$file_id = $_POST['file_id'];
	$filename = $_POST['filename'];
	$result = $gallery->Rename_file($file_id, $filename, false);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}
# Trash
elseif ($action == "trash_category") {
	$category_id = $_POST['category_id'];
	$result = $gallery->Trash_category($category_id);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}
elseif ($action == "restore_category") {
	$category_id = $_POST['category_id'];
	$result = $gallery->Restore_category($category_id);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}
elseif ($action == "trash_file") {
	$file_id = $_POST['file_id'];
	$result = $gallery->Trash_file($file_id);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}
elseif ($action == "trash_files") {
	$file_ids = $_POST['file_ids'];
	$files = explode(',', $file_ids);
	$succeeded = 0;
	$failed = 0;
	for ($a=0; isset($files[$a]); $a++) {
		$result = $gallery->Trash_file($files[$a]);
		if ($result && v($result['success'])) {
			$succeeded++;
			$output['results']['items'][] = array('succeeded'=> true, 'title'=> 'file id '.$files[$a]);
		}
		else {
			$failed++;
			$output['results']['items'][] = array('succeeded'=> false, 'title'=> 'file id '.$files[$a], 'errors'=> $result['errors']);
		}
	}
	$output['results']['succeeded'] = $succeeded;
	$output['results']['failed'] = $failed;
	$output['success'] = true;
}
elseif ($action == "restore_file") {
	$file_id = $_POST['file_id'];
	$result = $gallery->Restore_file($file_id);
	if (!v($result['success'])) {
		$output['success'] = false;
		$output['errors'] = $result['errors'];
	}
	else $output = $result;
}
elseif ($action == "restore_files") {
	$file_ids = $_POST['file_ids'];
	$cid = intval($_POST['category']);
	$ids = explode(',', $file_ids);
	foreach ($ids as &$id) {
		$id = intval($id);
	}
	if ($cid == 'bin') {
		if (preg_match("/([0-9]+,?)+/", $file_ids)) {
			$r = $db->query("UPDATE {$cfg['db']['prefix']}gallery_files SET date_trashed=0 WHERE id IN(".implode(',', $ids).")");
			if (!$r) {
				$output['success'] = false;
				$output['errors'][] = 'database';
			}
			else $output['success'] = true;
		}
		else {
			$output['success'] = false;
			$output['errors'][] = 'file_ids';
		}
	}
	else {
		$query = "SELECT c.*,sum(p.date_trashed) trashed"
			." FROM {$cfg['db']['prefix']}gallery_categories c"
			." LEFT JOIN {$cfg['db']['prefix']}gallery_categories p ON c.lft between p.lft and p.rgt"
			." WHERE c.id=$cid"
			." GROUP BY c.id,c.category,c.directory,c.lft,c.rgt,c.parent,c.author,c.date_created,c.date_trashed";
		$r = $db->query($query);
		if (!$r) {
			$output['success'] = false;
			$output['errors'][] = 'database';
		}
		else {
			if ($r->rowCount() == 0) {
				$output['success'] = false;
				$output['errors'][] = 'not_found';
			}
			else {
				//$data = mysql_fetch_assoc($r);
				$data = $r->fetch();
				if ($data['trashed'] > 0) {
					$r = $db->prepare("UPDATE {$cfg['db']['prefix']}gallery_files SET date_trashed=0 WHERE id IN(?)");
					$r->execute(array($file_ids));
					foreach (explode(',', $file_ids) as $fid) {
						$gallery->Move_file($fid, 0);
					}
					$output['success'] = true;
				}
				else {
					$r = $db->prepare("UPDATE {$cfg['db']['prefix']}gallery_files SET date_trashed=0 WHERE id IN(?)");
					$r->execute(array($file_ids));
					$output['success'] = true;
				}
			}
		}
	}
}
elseif ($action == "get_trash") {
	$r = $gallery->Get_trash();
	if ($r['success']) {
		if (count($r['trash']) > 0)
		$output = $r['trash'];
	}
}
elseif ($action == "empty_trash") {
	$r = $gallery->Empty_trash();
	if (!$r['success']) {
		$output['success'] = false;
		$output['errors'] = $r['errors'];
	}
	else $output = array('success' => true);
}
elseif ($action == "get_thumbnail_types") {
	$r = $gallery->Get_thumbnail_types();
	if ($r) {
		$default = array();
		$custom = array();
		foreach ($r as $type) {
			if (in_array($type['thumbnail_type'], array("thumbnail","small","large"))) {
				$type['group'] = 'default';
				$default[] = $type;
			}
			else {
				$type['group'] = 'custom';
				$custom[] = $type;
			}
		}
		$output = $default;
		while ($type = array_shift($custom)) {
			$output[] = $type;
		}
	}
}
elseif ($action == "create_thumbnail_type") {
	$changes = json_decode($_POST['changes']);
	$result = $gallery->Create_thumbnail_type(v($changes->type), v($changes->thumbnail_max_w), v($changes->thumbnail_max_h), v($changes->thumbnail_quality), v($changes->use_adaptive_resize));
	$output = $result;
}
elseif ($action == "edit_thumbnail_type") {
	$thumbnail_type = $_POST['thumbnail_type'];
	$changes = json_decode($_POST['changes']);
	$result = $gallery->Edit_thumbnail_type($thumbnail_type, v($changes->type), v($changes->thumbnail_max_w), v($changes->thumbnail_max_h), v($changes->thumbnail_quality), v($changes->use_adaptive_resize));
	$output = $result;
}

elseif ($action == "delete_thumbnail_type") {
	$thumbnail_type = $_POST['thumbnail_type'];
	$result = $gallery->Delete_thumbnail_type($thumbnail_type);
	$output = $result;
}
elseif ($action == "clear_thumb_cache") {
	$category_id = v($_POST['category_id'], 0);
	$file_ids = v($_POST['file_ids'], '');
	$file_ids = trim($file_ids);
	if (!empty($file_ids)) {
		$file_ids = explode(',', $file_ids);
	}
	else {
		$file_ids = array();
	}
	$result = $gallery->Delete_thumbnails($category_id, $file_ids);
	$output = $result;
}
elseif ($action == "move_files") {
	$files = $_POST['files'];
	$target = $_POST['target'];
	if (ctype_digit($target)) {
		foreach (explode(',', $files) as $id) {
			$result = $gallery->Move_file($id, $target);
		}
		$output['success'] = true;
	}
	else {
		$output['success'] = false;
		$output['errors'][] = 'target';
	}
}
elseif ($action == "move_category") {
	$category = $_POST['category'];
	$target = $_POST['target'];
	$position = $_POST['position'];
	if ($position < 0) $position = 0;
	$output = $gallery->Move_category($category, $target, $position);
}
elseif ($action == "get_resize_ratio_for_cropping_image") {
	$image_path = $gallery->config['gallery_path'].$_POST['image_path'];
	$image_name = $_POST['image_name'];
	$original_thumbnail_type = $thumbnail_type = $_POST['thumbnail_type'];
	if (!preg_match('/^'.$gallery->patterns['thumbnail_type'].'$/', $thumbnail_type)) {
		$output['errors'][] = 'thumbnail_type';
	} else {
		$thumbnail_type = 'thumb-'.$thumbnail_type;
		//$output['img_path'] = $image_path.$image_name;
		if (is_file($image_path.$image_name)) {
			$size = getimagesize($image_path.$image_name);
			$output['size'] = serialize($size);
			if ($size) {
				$ratio = $gallery->config['image_for_croping_max_dimensions'] / max($size[0], $size[1]);
				$crop_data_path = $image_path.$thumbnail_type.'/'.$image_name.'.txt';
				//$output['crop_data_path'] = $crop_data_path;
				if (is_file($crop_data_path)) {
					$crop_data = file_get_contents($crop_data_path);
					$output['crop_data'] = explode('|', $crop_data);
				}
				else {
					$output['crop_data'] = $gallery->Get_crop_data($image_path . '/'.$image_name, $original_thumbnail_type);
					if ($output['crop_data']) {
						$output['crop_data'] = array_values($output['crop_data']);
					}
				}
				if ($ratio > 1) $ratio = 1;
				$output['success'] = true;
				$output['ratio'] = $ratio;
				$output['original_size'] = array("width"=>$size[0],"height"=>$size[1]);
			}
		}
	}
}
elseif ($action == 'get_file') {
	$output = $gallery->Get_file_by_id($_POST['id']);
}
elseif ($action == 'sync_category') {
	$id = v($_POST['id']);
	$output['success'] = $gallery->Sync_category($id);
}
elseif ($action == 'get_flash_size') {
	$path = v($_POST['path']);
	$data = $gallery->Parse_file_request($path);
	if (v($data['success'])) {
		$size = getimagesize($cfg['path']['gallery'].$path);
		if ($size) {
			$output['success'] = true;
			$output['width'] = $size[0];
			$output['height'] = $size[1];
		}
	}
}
else {
	$output['errors'][] = 'unknown_action';
}
echo json_encode($output);
?>