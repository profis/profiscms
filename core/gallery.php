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

error_reporting(0); //ensure PHP won't output any error data and won't destroy file output
//$cfg['core']['no_gzip'] = true;
$cache_time = 3600;
$etag = time()-$cache_time;
if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] < time()-$cache_time)
	|| (isset($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) < $etag)) {
	header('HTTP/1.1 304 Not Modified');
    exit();
}
// Output file for admin
$galleryRequest = (isset($_GET['r'])?$_GET['r']:'');

if (substr($galleryRequest, 0, 6) == 'admin/') {
	$cfg['core']['no_login_form'] = true;//don't output login form if there's no active session
	require_once('../admin/admin.php'); //ensure the user is authorized, otherwise stop executing this script
	$galleryRequest = substr($galleryRequest, 6);
	if (substr($galleryRequest, 0, 3) == 'id/') {
		$galleryRequest = substr($galleryRequest, 3);
		$request_items = explode('/', $galleryRequest);
		$total_items = count($request_items);
		if (preg_match("/^(thumbnail|small|large|(thumb-(".$gallery->patterns['thumbnail_type'].")))$/", $request_items[0], $matches)
			&& !preg_match("/^thumb-(thumbnail|small|large)$/", $request_items[0])) {
			$thumbnail_type = isset($matches[3])?$matches[3]:$matches[0];
			$file_id = $request_items[1];
		} else {
			$thumbnail_type = '';
			$file_id = $request_items[0];
		}
		$result = $gallery->Get_file_by_id($file_id);
		if (!v($result['success'])) { //image was not found or other error occurred
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
		}
		$result = $gallery->Output_file($result['filedata']['filename'], $result['filedata']['path'],
										$thumbnail_type, $result['filedata']);
		if (!v($result['success'])) {
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		}
		
	} else {
		if (substr($galleryRequest, 0, 7) == 'cropper') { //show image for cropping
			$galleryRequest = substr($galleryRequest, 8);
			$parsing_result = $gallery->Parse_file_request($galleryRequest);
			
			if (!v($parsing_result['success'])) { //invalid request
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
			}
			//parsing result data: filename, category_path, thumbnail_type
			if (!empty($parsing_result['thumbnail_type'])) { //there's no need to choose thumbnail type while cropping always starts from resized original
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
			}
			$result = $gallery->Get_file($parsing_result['filename'], $parsing_result['category_path']);
			if (!v($result['success'])) { //image was not found or other error occurred
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
			}
			//result data: filedata[extension, size, date_trashed, category_trashed]
			if ($gallery->filetypes[$result['filedata']['extension']] != 'image') {
				// file is not an image!
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
			}
			$result = $gallery->Output_image_for_cropping($parsing_result['filename'], $parsing_result['category_path']);
			if (!v($result['success'])) {
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			}
			
		}
		else { //just get the file even if it's trashed or private
			$parsing_result = $gallery->Parse_file_request($galleryRequest);
			//print_pre($parsing_result); die();
			if (!v($parsing_result['success'])) { //invalid request
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
			}
			//result data: filename, category_path, thumbnail_type
			$result = $gallery->Get_file($parsing_result['filename'], $parsing_result['category_path']);
			if (!v($result['success'])) { //image was not found or other error occurred
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
			}
			//result data: filedata[extension, size, date_trashed, category_trashed]
			$result = $gallery->Output_file($parsing_result['filename'], $parsing_result['category_path'],
											$parsing_result['thumbnail_type'], $result['filedata']);
			if (!v($result['success'])) {
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			}
		}
	}
// Output file for a guest / normal user
}
else {
	require_once 'path_constants.php';
	require_once 'base.php';

	if ($galleryRequest == 'index.php') {
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
	}
	$parsing_result = $gallery->Parse_file_request($galleryRequest);
		
	if (!v($parsing_result['success'])) { //invalid request
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
	}
	
	//parsing data: filename, category_path, thumbnail_type
	//print_pre($parsing_result);
	$result = $gallery->Get_file($parsing_result['filename'], $parsing_result['category_path']);
	//print_pre($result);
	
	
	
	if (!v($result['success'])) { //image was not found or other error occurred
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
	}
	//result data: filedata[extension, size, date_trashed, category_trashed]
	if ($result['filedata']['date_trashed'] || v($result['filedata']['category_trashed'])) {
		//it's trashed or private - deny access
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); return;
	}
	header('Expires: '.gmdate('D, d M Y H:i:s', time()+$cache_time).'GMT');
	header('Cache-Control: max-age='.$cache_time.', must-revalidate');
	header('Last-Modified: '.time());
	header("Etag: ".$etag);
		
	$result = $gallery->Output_file($parsing_result['filename'], $parsing_result['category_path'],
								$parsing_result['thumbnail_type'], $result['filedata']);
	
	if (!v($result['success'])) {
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	}
}
/* Optimize code pro tip:
do {
	do {
		...
		if (bad1) break;
		...
		if (bad2) break;
		...
		if (bad3) break;
		
		// OK
		
		break 2;
	} while(0);
	// BAD
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
} while(0);
*/
?>