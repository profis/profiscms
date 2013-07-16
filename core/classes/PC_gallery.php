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
* Class used to manage images and other types of media.
*/
final class PC_gallery extends PC_base {
	
	const PERM = 0777;
	const UMASK = 0;
	
	public $gallery_dir_chmod;
	public $gallery_file_chmod;
	
	/**
	* Field used as gallery's working path.
	*/
	public $path;
	/**
	* Field used to represent file types which are supported by gallery.
	*/
	public $filetypes;
	/**
	* Field used for gallery configuration.
	*/
	public $config;
	/**
	* Field used for files names patterns used in gallery; AKA files by specific files names could be processed differently.
	*/	
	public $patterns;
	/**
	* Field used to store list of a small images which are related to their original files. Thumbnails are used for representation, sorting etc.
	*/		
	public $thumbnail_types;
	/**
	* Mehthod which sets configuration of current gallery object. Here defined supported files types, supported files names patterns and default images attributes.
	*/
	public function Init() {
		// supported filetypes and extensions
		// filetype: document
		$this->filetypes['doc'] = $this->filetypes['docx'] = $this->filetypes['xls']
		= $this->filetypes['xlsx'] = $this->filetypes['ppt'] = $this->filetypes['pptx']
		= $this->filetypes['csv'] = $this->filetypes['pdf'] = $this->filetypes['txt']
		= $this->filetypes['odt'] = $this->filetypes['cdr'] = $this->filetypes['swf'] = 'document';
		// filetype: archive
		$this->filetypes['zip'] = $this->filetypes['rar'] = $this->filetypes['7z'] = 'archive';
		// filetype: audio
		$this->filetypes['mp3'] = $this->filetypes['wav'] = $this->filetypes['wma'] = 'audio';
		// filetype: video
		$this->filetypes['avi'] = $this->filetypes['mpg'] = $this->filetypes['mpeg'] = $this->filetypes['mov'] = $this->filetypes['mp4']
		= $this->filetypes['wmv'] = $this->filetypes['mkv'] = $this->filetypes['flv'] = 'video';
		// filetype: image
		$this->filetypes['jpg'] = $this->filetypes['jpeg'] = $this->filetypes['gif'] = $this->filetypes['png']
		= $this->filetypes['bmp'] = 'image';
		// filetype: executable
		$this->filetypes['exe'] = 'executable';
		
		$this->filetypes['dwg'] = $this->filetypes['dwf'] = 'autocad';
		
		// root directory for the categories and files to store
		$this->config['gallery_directory'] = $this->cfg['directories']['gallery'];
		// path to the root
		$this->config['gallery_path'] = $this->path['gallery'];
		// config
		$this->config['image_for_croping_max_dimensions'] = 600;
		//$this->config['image_for_croping_max_dimensions_height'] = 450;
		$this->config['max_category_name_length'] = 50;
		$this->config['max_filename_length'] = 255;
		$this->config['max_thumbnail_type_length'] = 20;
		$this->config['disallow_delete_untrashed_category'] = false; // if true then trash album first
		$this->config['max_filesize'] = 167772150; //max value: 167772150 B = 159.9 MB (limited by image database 'image_size' column data type)
		
		// patterns
		$this->patterns['category'] = "[\pL\pN\(\)#$][\pL\pN\-_\(\)&#$\.,\/ ;%]{0,".($this->config['max_category_name_length']-2)."}[\pL\pN\(\)#$%]";
		$this->patterns['extension'] = "[a-z0-9]{1,4}";
		$this->patterns['filename_without_extension'] = "[a-ž0-9_\-,!\.\+\(\)=&^%$#@; \[\]]{1,".$this->config['max_filename_length']."}";
		$this->patterns['filename_without_extension'] = "[\pL\pN_\-,!\.\+\(\)=&^%$#@\'; \[\]]{1,".$this->config['max_filename_length']."}";
		$this->patterns['filename'] = $this->patterns['filename_without_extension']."\.".$this->patterns['extension'];
		$this->patterns['category_path'] = "(".$this->patterns['category']."\/)*".$this->patterns['category']; //unlimited depth category path
		$this->patterns['file_request'] = '('.$this->patterns['category_path'].'\/)?'.$this->patterns['filename']; // path + filename
		$this->patterns['thumbnail_type'] = "[a-z0-9][a-z0-9\-_]{0,".($this->config['max_thumbnail_type_length']-2)."}[a-z0-9]";
		$this->patterns['file_link'] = '('.$this->patterns['category_path'].'\/)?'.'(thumb-('.$this->patterns['thumbnail_type'].')\/|(small|large|thumbnail))?'.$this->patterns['filename']; // path + thumbnail_type + filename
		
		
		$this->debug = true;
		$this->set_instant_debug_to_file($this->cfg['path']['logs'] . 'gallery/pc_gallery.html', false, 5);
		$this->gallery_dir_chmod = v($this->cfg['gallery_dir_chmod'], self::PERM);
		$this->gallery_file_chmod = v($this->cfg['gallery_file_chmod'], self::PERM);
		
	}
	
	public function mkdir($dest) {
		$oldumask = umask(self::UMASK);
		$this->debug("old_umask: $oldumask", 1);
		$this->debug("mkdir($dest, $this->gallery_dir_chmod)", 1);
		$result = mkdir($dest, $this->gallery_dir_chmod);
		umask($oldumask);
		return $result;
	}
	
	public function chmod($dest) {
		$oldumask = umask(self::UMASK);
		$this->debug("old_umask: $oldumask", 1);
		$this->debug("chmod($dest, $this->gallery_dir_chmod)", 1);
		$result = chmod($dest, $this->gallery_dir_chmod);
		umask($oldumask);
		return $result;
	}
	
	// uncategorized methods
	/**
	* Method used for removing special chars from given string and setting it's encoding to UTF-8. Cyrillic chars are with latin chars by as-it-spells. 
	* Method  specifically used for formating URN.
	* @param string $name given string to remove special chars and form nice URL-friendly string.
	* @return string URL-friendly string.
	*/
	private function Format_name_for_link($name) {
		//translit cyrillic chars
		$cyrillic = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я');
		$latin = array('a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','shсh','','y','','eh','yu','ya','A','B','V','G','D','E','Yo','Zh','Z','I','J','K','L','M','N','O','P','R','S','T','U','F','H','C','Ch','Sh','Shсh','','Y','','Eh','Yu','Ya');
		$name = str_replace($cyrillic, $latin, $name);
		$name = remove_utf8_accents($name);
		// transliteration
		$name = strtolower(@iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $name));
		if (false and $converted_name) {
			$name = $converted_name;
		}
		// character/string blacklist (could also be censorship)
		$blacklist = array('#','$','&','(',')',',','.',':','%','@','/','[',']','_','?','!','+','=','^');
		$name = trim(str_replace($blacklist, '-', $name));
		// normalize structure for seo reasons
		$patterns[] = '/( *\- *| +)/'; $replacements[] = '-';
		$patterns[] = '/(--+)/'; $replacements[] = '-';
		$patterns[] = '/^-(.+)$/'; $replacements[] = '$1';
		$patterns[] = '/^(.+)-$/'; $replacements[] = '$1';
		$name = preg_replace($patterns, $replacements, $name);
		return $name;
	}
	
	public function get_original_file_path($file_path) {
		$file_path_parts = pathinfo($file_path);
		return  $file_path_parts['dirname'] . '/' . $file_path_parts['filename'] . '._pc_original_.' . $file_path_parts['extension'];
	}	
	
	/**
	* Method used to copy given file or folder recursivelly to given file or folder.
	* @param string $source given source file or folder.
	* @param string $dest given destination file or folder.
	* @return bool TRUE.
	*/
	private function Copy_directory($source, $dest) {
		// Simple copy for a file
		if (is_file($source)) {
			$c = copy($source, $dest);
			$this->chmod($dest);
			return $c;
		}
		// Make destination directory
		if (!is_dir($dest)) {
			$this->mkdir($dest);
		}
		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == "." || $entry == "..") {
				continue;
			}
			// Deep copy directories
			if ($dest !== "$source/$entry") {
				$this->Copy_directory("$source/$entry", "$dest/$entry");
			}
		}
		// Clean up
		$dir->close();
		return true;
	}
	/**
	* Method used to delete recurcivelly given file or folder.
	* @param string $directory given source file or folder to be deleted.
	* @return bool result of the function rmdir() given folder to be deleted.
	*/
	private function Remove_directory($directory) {
		// Sanity check
		if (!file_exists($directory)) {
			return false;
		}
		// Simple delete for a file
		if (is_file($directory)) {
			return @unlink($directory);
		}
		// Loop through the folder
		$dir = dir($directory);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			// Recurse
			$this->Remove_directory($directory.'/'.$entry);
		}
		// Clean up
		$dir->close();
		return rmdir($directory);
	}
	/**
	* Method used to copy given source file/folder to given destination file/folder and delete given source file/folder recurcivelly. In this method is used 
	* other methods of this instance for copy and remove given source and target.
	* @param string $source given source file or folder.
	* @param string $dest given destination file or folder.
	* @return bool FALSE if copying fails; TRUE otherwise. It's important to mention, what if given source is deleted anyway.
	* @see PC_gallery::Remove_directory().
	* @see PC_gallery::Copy_directory().
	*/
	private function Move_directory($source, $dest) {
		if (!$this->Copy_directory($source, $dest)) {
			$this->Remove_directory($dest);
			return false;
		}
		$this->Remove_directory($source);
		// return true even if source directory was not removed successfully to prevent partially moved directories
		// in future this could also return warning that source directory was not removed completely
		return true;
	}
	/**
	* Method used to delete files names records from the appropriate DB table by given content id.
	* @param mixed $content_id given content identificator by which deletion will be performed.
	* @return mixed array with "success" key in it set to TRUE; and array with key "errors" set to "content_id" otherwise.
	*/
	public function Purge_files_in_use($content_id) {
		$content_id = (int)$content_id;
		if ($content_id < 1) {
			$response['errors'][] = "content_id";
			return $response;
		}
		global $db;
		$r = $db->prepare("DELETE FROM {$this->db_prefix}gallery_files_in_use WHERE content_id=?");
		$r->execute(array($content_id));
		return array('succes'=> true);
	}
	/**
	* Method used to update currently used files records in the appropriate DB table by given files ids.
	* @param mixed $file_ids given files identificators array.
	* @param mixed $content_id given content identificator by which update will be performed.
	* @param mixed $content_block given content block by which update will be performed.
	* @return mixed array with key "errors" set to "content_id" or "content_block" on failure; or TRUE otherwise.
	*/
	public function Update_files_in_use($file_ids, $content_id, $content_block) {
		$content_id = (int)$content_id;
		if ($content_id < 1)
			$response['errors'][] = "content_id";
		if (!in_array($content_block, array('info','info2','info3','text')))
			$response['errors'][] = "content_block";
		if (count(v($response['errors'])) != 0) return $response;
		global $db;
		$r = $db->prepare("DELETE FROM {$this->db_prefix}gallery_files_in_use WHERE content_id=:id and content_block=:block");
		$r->execute(array(
			'id'=> $content_id,
			'block'=> $content_block
		));
		//remove duplicates
		$file_ids = array_values(array_unique($file_ids, SORT_NUMERIC));
		//
		foreach ($file_ids as &$id) {
			$id = "(".$id.",".$content_id.",'".$content_block."')";
		}
		$db->query("INSERT INTO {$this->db_prefix}gallery_files_in_use VALUES".implode(',', $file_ids));
		return true;
	}
	//public function Get_album_id_by_path($path) {}
	/**
	* Method used to get file id by given url. In this method used other method of this instance.
	* @param string $url given URL to search for file id by.
	* @return mixed array with key "errors" on failure; or array with keys "success" and "id" otherwise.
	* @see PC_gallery::Parse_file_request();
	*/
	public function Get_file_id_by_url($url) {
		$r = $this->Parse_file_request($url);
		if (!$r['success'])
			return $r;
		$category_path = $r['category_path'];
		$filename = $r['filename'];
		if (!empty($category_path)) {
			if (!preg_match('/^'.$this->patterns['category_path'].'$/ui', $category_path))
				$response['errors'][] = "category_path";
			$glob = glob($this->config['gallery_path'].$category_path.'/category_id_*');
			if (!isset($glob[0]))
				$response['errors'][] = "category_path";
		}
		if (count(v($response['errors'])) != 0) return $response;
		if (!empty($category_path)) {
			$category_id = substr($glob[0], strrpos($glob[0], '_')+1);
		}
		else $category_id = 0;
		global $db;
		$r = $db->prepare("SELECT id FROM {$this->db_prefix}gallery_files WHERE filename=? and category_id=?");
		$success = $r->execute(array($filename, $category_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() != 1) {
			$response['errors'][] = "file_not_found";
			return $response;
		}
		return array(
			'success'=> true,
			'id'=> $r->fetchColumn()
		);
	}
	//categories
	/**
	* Method used to create directory for given category to the given path.  Method effects filesystem given category directory and it's
	* referenced category in DB tables. In this method used other methods of this instance.
	* @param string $category given category name to be created.
	* @param string $path given path where category will be created.
	* @return mixed array with key "errors" on failure; or array with keys "success" and "directory" otherwise.
	* @see PC_gallery::Generate_unique_category_directory();
	*/
	private function Create_directory($category, $path) {
		$this->debug("<u>Create_directory($category, $path)</u>");
		if (strlen($category) < 2) {
			$response['errors'][] = "category";
			return $response;
		}
		$full_path = $this->config['gallery_path'].$path;
		$r = $this->Generate_unique_category_directory($category, $full_path);
		if (!$r['success']) {
			$response['errors'][] = "generate_directory";
			return $response;
		}
		$directory = $r['directory'];
		if (!$this->mkdir($full_path.$directory)) {
			$response['errors'][] = "create_directory";
			return $response;
		}
		
		return array("success"=>true,"directory"=>$directory);
	}
	/**
	* Method used to create unique directory name for given category. In this method used other methods of this instance.
	* @param string $category given category name to be made unique.
	* @param string $path given path where category will be created.
	* @return mixed array with key "errors" on failure; or array with keys "success" and "directory" otherwise.
	* @see PC_gallery::Format_name_for_link();
	*/
	private function Generate_unique_category_directory($category, $path) {
		if (strlen($category) < 2) {
			$response['errors'][] = "category";
			return $response;
		}
		$directory = $this->Format_name_for_link($category);
		# Check if specified path exists
		if (!is_dir($path)) {
			$response['errors'][] = "path";
			return $response;
		}
		# Choose unique directory name that doesn't exist in the path specified
		$prefix = '';
		if (is_dir($path.$directory)) {
			$prefix = date('Y').'-';
			if (is_dir($path.$prefix.$directory)) {
				$prefix .= date('n').'-';
				if (is_dir($path.$prefix.$directory)) {
					$prefix .= date('j').'-';
					if (is_dir($path.$prefix.$directory)) {
						for ($a=1; is_dir($path.$prefix.$directory.'-'.$a); $a++) {}
						return array("success"=>true,"directory"=>$prefix.$directory.'-'.$a);
					}
				}
			}
		}
		return array("success"=>true,"directory"=>$prefix.$directory);
	}
	/**
	* Method used to get deleted categories from appropriate DB tables. In this method used other method of this instance.
	* @return mixed array with key "errors" on failure; or array with keys "success" and "categories" otherwise.
	* @see PC_gallery::Sort_path();
	*/
	public function Get_trashed_categories() {
		global $db;
		$r = $db->query("SELECT
		c.id, c.category, c.lft, c.rgt,
		sum(file.size) as size,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
		." FROM {$this->db_prefix}gallery_categories c
		LEFT JOIN {$this->db_prefix}gallery_categories categories ON categories.lft between c.lft and c.rgt
		LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
		and categories.date_trashed=0
		LEFT JOIN {$this->db_prefix}gallery_files file ON file.category_id = categories.id
		and file.date_trashed=0
		WHERE c.date_trashed>0
		GROUP BY c.id,c.category,c.lft,c.rgt");
		if (!$r) {
			$response['errors'][] = "database";
			return $response;
		}
		while ($data = $r->fetch()) {
			$this->Sort_path($data['path']);
			$categories[] = $data;
		}
		return array("success"=>true,"categories"=>$categories);
	}
	
	/**
	* Method used to retrieve categories list from appropriate DB tables.
	* @param mixed $parent given parent category id to retrieve it's children.
	* @param bool $trashed given indication if retrieved list should contain only deleted categories.
	* @return mixed array with key "errors" on failure; or array with keys "success" and "categories" otherwise.
	*/
	public function Get_categories($parent=0, $trashed=false) {
		$categories = array();
		if ($parent != 'bin') {
			$parent = (int)$parent;
			if ($parent < 0)
				$r['errors'][] = "parent";
		}
		if (count(v($r['errors'])) != 0) return $r;
		global $db;
		//get bin root nodes
		if ($parent === 'bin') {
			$r = $db->query("SELECT
			c.id, c.category, c.lft, c.rgt, sum(distinct file.size) as size,"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
			."sum(distinct path.date_trashed)-c.date_trashed parent_trashed
			FROM {$this->db_prefix}gallery_categories c
			LEFT JOIN {$this->db_prefix}gallery_categories categories ON categories.lft between c.lft and c.rgt
			LEFT JOIN {$this->db_prefix}gallery_files file ON file.category_id = categories.id
			LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
			WHERE c.date_trashed>0
			GROUP BY c.id,c.category,c.lft,c.rgt,c.date_trashed");
			if (!$r) {
			$response['errors'][] = "database";
			return $response;
			}
			while ($data = $r->fetch()) {
				if ($data['parent_trashed'] == 0) {
					$this->Sort_path($data['path']);
					$categories[] = $data;
				}
			}
		}
		//get subnodes in the bin
		elseif ($trashed) {
			$r = $db->prepare("SELECT
			c.id, c.category, c.lft, c.rgt,
			sum(distinct file.size) as size, max(path.lft),"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path, 1 trashed"
			." FROM {$this->db_prefix}gallery_categories c
			LEFT JOIN {$this->db_prefix}gallery_categories categories ON categories.lft between c.lft and c.rgt
			LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
			LEFT JOIN {$this->db_prefix}gallery_files file ON file.category_id = categories.id
			WHERE c.parent=?
			GROUP BY c.id,c.category,c.lft,c.rgt");
			$success = $r->execute(array($parent));
			if (!$success) {
				$r['errors'][] = "database";
				return $r;
			}
			while ($data = $r->fetch()) {
				$this->Sort_path($data['path']);
				$categories[] = $data;
			}
		}
		//get untrashed categories
		else {
			$r = $db->prepare("SELECT
			c.id, c.category, c.lft, c.rgt,
			sum(distinct file.size) size,"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
			." FROM {$this->db_prefix}gallery_categories c
			LEFT JOIN {$this->db_prefix}gallery_categories categories ON categories.lft between c.lft and c.rgt and categories.date_trashed=0
			LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
			and categories.date_trashed=0
			LEFT JOIN {$this->db_prefix}gallery_files file ON file.category_id = categories.id
			and file.date_trashed=0
			WHERE c.parent=?
			and c.date_trashed=0
			GROUP BY c.id,c.category,c.lft,c.rgt");
			$success = $r->execute(array($parent));
			//print_pre($this->db->errorInfo());
			if (!$success) {
				$response['errors'][] = "database";
				return $response;
			}
			$categories = array();
			while ($data = $r->fetch()) {
				$this->Sort_path($data['path']);
				$categories[] = $data;
			}
		}
		return array("success"=>true,"categories"=>$categories);
	}
	
	/**
	* Method used to form URL-friendly path from stored in DB path. Method takes an reference variable.
	* @param string $path given path to be formed as URL-friendly string.
	*/
	public function Sort_path(&$path) {
		if (!empty($path)) {
			if (strpos($path, '░')) {
				//print_pre($path);
				$parts = explode('/', $path);
				//print_pre($parts);
				$path = array();
				foreach ($parts as $part) {
					$part = explode('░', $part);
					$path[$part[0]] = $part[1];
				}
				ksort($path);
				$path = implode('/', $path);
			}
		}
	}
	
	/**
	* Method used to create category by given name and as given parents' child. Category is created in appropriate DB tables and in filesystem as well.
	* @param string $category given parent category name to create.
	* @param int $parent given parent category id.
	* @param int $position given category folder position identifier.
	* @return mixed array with key "errors" on failure; or array with keys "success" and "id" otherwise.
	*/
	public function Create_category($category, $parent=0, $position=0) {
		$position = 0; // temporary allow album creation only in the end of the root/parent
		if (!preg_match('/^'.$this->patterns['category'].'$/ui', $category))
			$response['errors'][] = "category";
		$parent = (int)$parent;
		if ($parent < 0)
			$response['errors'][] = "parent";
		$position = (int)$position;
		if ($position < -1)
			$response['errors'][] = "position";
		if (count(v($response['errors'])) != 0) return $response;
		global $db;
		if ($parent == 0) {
			if ($position == 0) {
				// create category at the end of the root
				$r = $db->query("SELECT max(rgt) FROM {$this->db_prefix}gallery_categories");
				if (!$r) {
					$response['errors'][] = "database";
					return $response;
				}
				$left = $r->fetchColumn()+1;
				$right = $left + 1;
				// create directory in the gallery root
				$r = $this->Create_directory($category, '');
				if (!$r['success']) {
					$response['errors'][] = "create_directory";
					return $response;
				}
				$directory = $r['directory'];
				$author = 1; //current user id
				$now = time();
				$r = $db->prepare("INSERT INTO {$this->db_prefix}gallery_categories (category,directory,lft,rgt,parent,author,date_created,date_trashed) VALUES(?,?,?,?,?,?,?,0)");
				$success = $r->execute(array($category, $directory, $left, $right, $parent, $author, $now));
				if (!$success) {
					rmdir($this->config['gallery_path'].$directory);
					$response['errors'][] = "database";
					return $response;
				}
				$id = $db->lastInsertId($this->sql_parser->Get_sequence('gallery_categories'));
				# Create category_id_{id} file in the category path (file used when retrieving file by {path}/{filename})
				fclose(fopen($this->config['gallery_path'].$directory.'/category_id_'.$id, 'a'));
				return array("success"=>true,"id"=>$id);
			}
			elseif ($position == -1) {
				//create category at the beginning of the root
				$left = 1; $right = 2;
				//create directory in the gallery root
				$r = $this->Create_directory($category, '');
				if (!$r['success']) {
					$response['errors'][] = "create_directory";
					return $response;
				}
				$directory = $r['directory'];
				$author = 1; //current user id
				$now = time();
				$r = $db->prepare("INSERT INTO {$this->db_prefix}gallery_categories (category,directory,lft,rgt,parent,author,date_created,date_trashed) VALUES(?,?,?,?,?,?,?,0)");
				$success = $r->execute(array($category, $directory, $left, $right, $parent, $author, $now));
				if (!$success) {
					rmdir($this->config['gallery_path'].$directory);
					$response['errors'][] = "database";
					return $response;
				}
				$id = $db->lastInsertId($this->sql_parser->Get_sequence('gallery_categories'));
				# Create category_id_{id} file in the category path (file used when retrieving file by {path}/{filename})
				fclose(fopen($this->config['gallery_path'].$directory.'/category_id_'.$id,'a'));
				$r = $db->prepare("UPDATE {$this->db_prefix}gallery_categories SET lft=lft+2, rgt=rgt+2 WHERE id!=?");
				$r->execute(array($id));
				return array("success"=>true,"id"=>$id);
			}
			else {
				//create category after specified position in the root
				//$db->query("SELECT min(lft), max(rgt) FROM {$this->db_prefix}gallery_categories WHERE parent=$parent");
			}
		}
		else {
			if ($position == 0) {
				//create category  as the last parents' child
				$r = $db->prepare("SELECT category.rgt,"
				.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
				." FROM {$this->db_prefix}gallery_categories category
				LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft between path.lft and path.rgt
				WHERE category.id=? GROUP BY category.id,category.rgt LIMIT 1");
				$success = $r->execute(array($parent));
				if (!$success) {
					$r['errors'][] = "database";
					return $r;
				}
				$data = $r->fetch();
				if (empty($data['path'])) {
					$r['errors'][] = "parent";
					return $r;
				}
				else $this->Sort_path($data['path']);
				$left = $data['rgt'];
				$right = $left + 1;
				//create directory in the path
				$r = $this->Create_directory($category, $data['path'].'/');
				if (!$r['success']) {
					$r['errors'][] = "create_directory";
					return $r;
				}
				$directory = $r['directory'];
				$author = 1; //current user id
				$now = time();
				$r = $db->prepare("INSERT INTO {$this->db_prefix}gallery_categories (category,directory,lft,rgt,parent,author,date_created,date_trashed) VALUES(?,?,?,?,?,?,?,0)");
				$success = $r->execute(array($category, $directory, $left, $right, $parent, $author, $now));
				if (!$success) {
					rmdir($this->config['gallery_path'].$data['path'].'/'.$directory);
					$r['errors'][] = "database";
					return $r;
				}
				$id = $db->lastInsertId($this->sql_parser->Get_sequence('gallery_categories'));
				# Create category_id_{id} file in the category path (file used when retrieving file by {path}/{filename})
				fclose(fopen($this->config['gallery_path'].$data['path'].'/'.$directory.'/category_id_'.$id,'a'));
				error_reporting(E_ALL);
				$db->query("UPDATE {$this->db_prefix}gallery_categories
				SET rgt=rgt+2
				WHERE rgt>".($left-1)." and id!=$id");
				$r = $db->prepare("UPDATE {$this->db_prefix}gallery_categories
				SET lft=lft+2
				WHERE lft>? and id!=?");
				$r->execute(array($left, $id));
				return array("success"=>true,"id"=>$id);
			}
			elseif ($position == -1) {
				//create category as the first parents' child
			}
			else {
				//create category after specified position in the parents' nodes
			}
		}
	}
	
	/**
	* Method used to move given category to given category. Method effects given category directory in filesystem and it's referenced category in DB tables.
	* @param int $id given category id to be moved.
	* @param int $parent_id given parent category id to be moved in.
	* @param int $position given category folder position identifier.
	* @return mixed array with key "errors" on failure; or array with key "success" otherwise.
	* @see PC_gallery::Trash_category();
	* @see PC_gallery::Sort_path();
	* @see PC_gallery::Move_directory();
	*/
	public function Move_category($id, $parent_id, $position=0) {
		$id = (int)$id;
		if ($id < 1) $response['errors'][] = "category_id";
		if ($parent_id == 'bin') return $this->Trash_category($id);
		$parent_id = (int)$parent_id;
		if ($parent_id < 0) $response['errors'][] = "parent_id";
		$position = (int)$position;
		if ($position < 0) $response['errors'][] = "position";
		if (count(v($response['errors'])) != 0) return $response;
		global $db;
		// select category
		$r = $db->prepare("SELECT c.directory, c.parent, c.lft, c.rgt, c.date_trashed,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
		.$this->sql_parser->group_concat('ids.id', array('separator'=>','))." ids"
		." FROM {$this->db_prefix}gallery_categories c
		LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
		LEFT JOIN {$this->db_prefix}gallery_categories ids ON ids.lft between c.lft and c.rgt
		WHERE c.id=? GROUP BY c.id,c.directory,c.parent,c.lft,c.rgt,c.date_trashed LIMIT 1");
		$success = $r->execute(array($id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		$data = $r->fetch();
		if (empty($data['path'])) {
			$response['errors'][] = 'category_not_found';
			return $response;
		}
		else $this->Sort_path($data['path']);
		$category = $data;
		$category['path'] = $this->config['gallery_path'].$category['path'];
		//get parent path and tree side value after which the node should be dropped
		$parent = array();
		if ($parent_id == 0) {
			$parent['path'] = $this->config['gallery_path'].'/'.$category['directory'];
			if ($position == 0) {
				$r = $db->query("SELECT max(rgt) FROM {$this->db_prefix}gallery_categories");
				if (!$r) {
					$response['errors'][] = "database";
					return $response;
				}
				$move_after = $r->fetchColumn();
			} else {
				$r = $db->prepare("SELECT lft FROM {$this->db_prefix}gallery_categories WHERE parent=0 ORDER BY lft LIMIT ?,1");
				$success = $r->execute(array(($position-1)));
				if (!$success) {
					$response['errors'][] = "database";
					return $response;
				}
				if ($r->rowCount() == 1) {
					$move_after = $r->fetchColumn()-1;
				} else {
					$r = $db->query("SELECT max(rgt) FROM {$this->db_prefix}gallery_categories");
					if (!$r) {
						$response['errors'][] = "database";
						return $response;
					}
					$move_after = $r->fetchColumn();
				}
			}
		} else {
			$r = $db->prepare("SELECT c.lft, c.rgt,"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
			." FROM {$this->db_prefix}gallery_categories c
			LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
			WHERE c.id=? GROUP BY c.id,c.lft,c.rgt LIMIT 1");
			$success = $r->execute(array($parent_id));
			if (!$success) {
				$response['errors'][] = "database";
				return $response;
			}
			$data = $r->fetch();
			if (empty($data['path'])) {
				$response['errors'][] = 'parent_not_found';
				return $response;
			}
			else $this->Sort_path($data['path']);
			$parent = $data;
			if ($parent['lft'] > $category['lft'] && $parent['rgt'] < $category['rgt']) {
				$response['errors'][] = 'invalid_parent';
				return $response;
			}
			$parent['path'] = $this->config['gallery_path'].$parent['path'].'/'.$category['directory'];
			if ($parent['rgt']-$parent['lft'] == 1) {
				$move_after = $parent['lft'];
			} else {
				if ($position == 0) {
					$r = $db->prepare("SELECT max(rgt) FROM {$this->db_prefix}gallery_categories WHERE lft between ? and ?");
					$success = $r->execute(array(($parent['lft']+1), ($parent['rgt']-1)));
					if (!$success) {
						$response['errors'][] = "database";
						return $response;
					}
					$move_after = $r->fetchColumn();
				} else {
					$r = $db->prepare("SELECT lft FROM {$this->db_prefix}gallery_categories WHERE parent=? ORDER BY lft LIMIT ?,1");
					$success = $r->execute(array($parent_id, ($position-1)));
					if (!$success) {
						$response['errors'][] = "database";
						return $response;
					}
					if ($r->rowCount() == 1) {
						$move_after = $r->fetchColumn()-1;
					} else {
						$move_after = $parent['rgt']-1;
					}
				}
			}
		}
		//if there's already a category with the same directory name in the target - stop execution
		if ($category['parent'] != $parent_id) if (is_dir($parent['path'].$category['directory'])) {
			$response['errors'][] = 'directory_exists_in_parent';
			return $response;
		}
		//is there really anything to move?
		if ($move_after-1 == $category['lft']) {
			//untrash category
			if ($category['date_trashed'] > 0) {
				$r = $db->prepare("UPDATE {$this->db_prefix}gallery_categories SET date_trashed=0 WHERE id=?");
				$r->execute(array($id));
				return array('success'=>true);
			}
			$response['errors'][] = "no_changes";
			return $response;
		}
		$gap = $category['rgt']-$category['lft']+1;
		//delete the gap left after the node will be moved
		$db->prepare("UPDATE {$this->db_prefix}gallery_categories SET lft=lft-? WHERE lft>? and id NOT IN({$category['ids']})")
		->execute(array($gap, $category['lft']));
		//recalculate side value cause it could be changed after deleting the gap
		if ($move_after > $category['lft']) $move_after -= $gap;
		$db->prepare("UPDATE {$this->db_prefix}gallery_categories SET rgt=rgt-? WHERE rgt>? and id NOT IN({$category['ids']})")
		->execute(array($gap, $category['lft']));
		//create the gap for the node to move in
		$db->prepare("UPDATE {$this->db_prefix}gallery_categories SET lft=lft+? WHERE lft>? and id NOT IN({$category['ids']})")
		->execute(array($gap, $move_after));
		$db->prepare("UPDATE {$this->db_prefix}gallery_categories SET rgt=rgt+? WHERE rgt>? and id NOT IN({$category['ids']})")
		->execute(array($gap, $move_after));
		//calculate the difference between old and new node position
		$difference = $category['lft']-$move_after-1;
		//move the node
		$db->prepare("UPDATE {$this->db_prefix}gallery_categories SET lft=lft-?, rgt=rgt-? WHERE id IN({$category['ids']})")
		->execute(array($difference, $difference));
		$db->prepare("UPDATE {$this->db_prefix}gallery_categories SET parent=? WHERE id=?")
		->execute(array($parent_id, $id));
		//move this category physically (only if parent has changed)
		if ($category['parent'] != $parent_id) if (!$this->Move_directory($category['path'], $parent['path'])) {
			$response['errors'][] = 'move_directory';
			return $response;
		}
		//untrash category
		if ($category['date_trashed'] > 0) {
			$db->prepare("UPDATE {$this->db_prefix}gallery_categories SET date_trashed=0 WHERE id=?")->execute(array($id));
		}
		return array('success'=>true);
	}
	/**
	* Method used to change given category name to given one. Method effects given category directory in filesystem and it's referenced category in DB tables.
	* @param int $category_id given category id to be renamed.
	* @param string $category given category name to be applied.
	* @return mixed array with key "errors" on failure; or array with keys "success" and "directory" otherwise.
	* @see PC_gallery::Format_name_for_link();
	*/
	public function Rename_category($category_id, $category, $old_category) {
		$category_id = (int)$category_id;
		if ($category_id < 1) $response['errors'][] = "category_id";
		if (!preg_match('/^'.$this->patterns['category'].'$/ui', $category))
			$response['errors'][] = "category";
		if (count(v($response['errors'])) != 0) return $response;
		global $db;
		$r = $db->prepare("SELECT c.directory,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true, 'separator'=>'/'))." path"
		." FROM {$this->db_prefix}gallery_categories c
		LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
		WHERE c.id=? GROUP BY c.id,c.directory LIMIT 1");
		$success = $r->execute(array($category_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		$data = $r->fetch();
		if (empty($data['path'])) {
			$response['errors'][] = 'category_not_found';
			return $response;
		}
		else $this->Sort_path($data['path']);
		$current_path = $this->config['gallery_path'].$data['path'].'/';
		$new_category_directory = $this->Format_name_for_link($category);
		$new_path = $this->config['gallery_path'].$data['path'].'/../'.$new_category_directory;
		if (strcasecmp($category, $old_category) != 0) {
			if (is_dir($new_path)) {
				$response['errors'][] = "directory_exists";
				return $response;
			}
		}
		
		if (!rename($current_path, $new_path)) {
			$response['errors'][] = "rename_directory";
			return $response;
		}
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_categories SET category=?, directory=? WHERE id=?");
		$success = $r->execute(array($category, $new_category_directory, $category_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		// update gallery links in content
		$path = $this->config['gallery_directory'].'/'.$data['path'];
		$new_path = $this->config['gallery_directory'].'/'.substr($data['path'], 0, -(strlen($data['directory'])+1)).$new_category_directory;
		$r = $db->prepare("UPDATE {$this->db_prefix}content SET"
		." text = replace(text, ?, ?), info = replace(info, ?, ?),"
		." info2 = replace(info2, ?, ?), info3 = replace(info3, ?, ?)");
		$success = $r->execute(array($path, $new_path, $path, $new_path,$path, $new_path, $path, $new_path));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		return array("success"=>true,'directory'=>$new_category_directory);
	}
	
	/**
	* Method used to delete given category by given category id. Method effects given category directory in filesystem and it's referenced category in DB tables.
	* @param int $category_id given category id to be deleted.
	* @return mixed array with key "errors" on failure; or array with key "success" otherwise.
	* @see PC_gallery::Trash_category();
	* @see PC_gallery::Remove_directory();
	*/
	public function Delete_category($category_id) {
		$category_id = (int)$category_id;
		if ($category_id < 1) {
			$response['errors'][] = "category_id";
			return $response;
		}
		global $db;
		$r = $db->prepare("SELECT c.lft, c.rgt, c.date_trashed,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
		." FROM {$this->db_prefix}gallery_categories c
		LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
		WHERE c.id=? GROUP BY c.id,c.lft,c.rgt,c.date_trashed");
		$success = $r->execute(array($category_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		$data = $r->fetch();
		if (empty($data['path'])) {
			$response['errors'][] = "category_not_found";
			return $response;
		}
		else $this->Sort_path($data['path']);
		if ($data['date_trashed'] == 0 && $this->config['disallow_delete_untrashed_album']) {
			return $this->Trash_category($category_id);
		}
		
		
		$success = false;
		
		$r_cat = $db->prepare("SELECT category.*
			FROM {$this->db_prefix}gallery_categories category
			WHERE category.lft between ? and ?");
		$res_cat = $r_cat->execute(array($data['lft'], $data['rgt']));
		
		if ($res_cat) {
			while ($cat_data = $r_cat->fetch()) {
				$r_files = $db->prepare("SELECT *
					FROM {$this->db_prefix}gallery_files
					WHERE category_id = ?");

				$res_files = $r_files->execute(array($cat_data['id']));
				if ($res_files) {
					while ($file_data = $r_files->fetch()) {
						$r_use = $db->prepare("DELETE
							FROM {$this->db_prefix}gallery_files_in_use
							WHERE file_id = ?");
						$r_use->execute(array($file_data['id']));
					}
					$r_files_delete = $db->prepare("DELETE
						FROM {$this->db_prefix}gallery_files
						WHERE category_id = ?");
					$r_files_delete->execute(array($cat_data['id']));
				}
			}
			$r_cat_delete = $db->prepare("DELETE 
				FROM {$this->db_prefix}gallery_categories
				WHERE lft between ? and ?");
			$success = $r_cat_delete->execute(array($data['lft'], $data['rgt']));
		}
		
			
		//needs addition: first copy directory to the Temp, if error occurs - restore it.
		if ($success) {
			$this->Remove_directory($this->config['gallery_path'].$data['path'].'/');
		}
		/*
		$delete_query = "DELETE category.*, files.*, gallery_files_in_use.*
			FROM {$this->db_prefix}gallery_categories category
			LEFT JOIN {$this->db_prefix}gallery_files files ON files.category_id = category.id
			LEFT JOIN {$this->db_prefix}gallery_files_in_use ON file_id = files.id
			WHERE category.lft between ? and ?";
		$delete_query_for_debug = $delete_query;
		$delete_query_for_debug = preg_replace('/\?/', $data['lft'], $delete_query_for_debug, 1);
		$delete_query_for_debug = preg_replace('/\?/', $data['rgt'], $delete_query_for_debug, 1);
		echo $delete_query_for_debug;
		$r = $db->prepare($delete_query);
		$success = $r->execute(array($data['lft'], $data['rgt']));
		*/
		
		if (!$success || $r_cat_delete->rowCount() < 1) {
			$response['errors'][] = "database";
			return $response;
		}
		$decrease = $data['rgt']-$data['lft']+1;
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_categories SET lft=lft-?, rgt=rgt-? WHERE lft>?");
		$r->execute(array($decrease, $decrease, $data['rgt']));
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_categories SET rgt=rgt-? WHERE rgt>? and lft<?");
		$r->execute(array($decrease, $data['rgt'], $data['lft']));
		return array("success"=>true);
	}
	
	/**
	* Method used to set status of category as 'deleted' in the DB tables. This method does not effects any filesystem folders, etc.
	* @param int $category_id given category id to be deleted.
	* @return mixed array with key "errors" on failure; or array with key "success" otherwise.
	*/
	public function Trash_category($category_id) {
		$category_id = (int)$category_id;
		if ($category_id < 1) {
			$response['errors'][] = "category_id";
			return $response;
		}
		global $db;
		$now = time();
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_categories SET date_trashed=? WHERE id=? and date_trashed=0");
		$success = $r->execute(array($now, $category_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() != 1) {
			$r = $db->prepare("SELECT count(*) FROM {$this->db_prefix}gallery_categories WHERE id=?");
			$r->execute(array($category_id));
			if ($r->fetchColumn() == 1) {
				$response['errors'][] = "already_trashed";
				return $response;
			}
			else {
				$response['errors'][] = "category_not_found";
				return $response;
			}
		}
		return array("success"=>true);
	}
	
	/**
	* Method used to set status of category as 'not deleted' in the DB tables. This method does not effects any filesystem folders, etc.
	* @param int $category_id given category id to be undeleted.
	* @return mixed array with key "errors" on failure; or array with key "success" otherwise.
	*/
	public function Restore_category($category_id) {
		$category_id = (int)$category_id;
		if ($category_id < 1) {
			$response['errors'][] = "category_id";
			return $response;
		}
		global $db;
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_categories SET date_trashed=0 WHERE id=?");
		$success = $r->execute(array($category_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() != 1) {
			$r = $db->prepare("SELECT count(*) FROM {$this->db_prefix}gallery_categories WHERE id=?");
			$r->execute(array($category_id));
			if ($r->fetchColumn() == 1) {
				$response['errors'][] = "category_not_in_a_trash";
				return $response;
			}
			else {
				$response['errors'][] = "category_not_found";
				return $response;
			}
		}
		return array("success"=>true);
	}
	
	/**
	* Method used to retrieve category details by given category id. Other method of this instance is used.
	* @param int $id given category id to be retrieved.
	* @return mixed array current category details or FALSE otherwise.
	* @see PC_gallery::Sort_path();
	*/
	public function Get_category($id) {
		$r = $this->prepare("SELECT c.directory, c.parent, c.lft, c.rgt, c.date_trashed,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
		.$this->sql_parser->group_concat('ids.id', array('separator'=>','))." ids"
		." FROM {$this->db_prefix}gallery_categories c
		LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft between path.lft and path.rgt
		LEFT JOIN {$this->db_prefix}gallery_categories ids ON ids.lft between c.lft and c.rgt
		WHERE c.id=? GROUP BY c.id,c.directory,c.parent,c.lft,c.rgt,c.date_trashed LIMIT 1");
		$s = $r->execute(array($id));
		if (!$s)  return false;
		$d = $r->fetch();
		if (empty($d['path'])) return false;
		else $this->Sort_path($d['path']);
		return $d;
	}
	
	/**
	* Method used to update given category details by details in filesystem. Other method of this instance is used.
	* @param int $id given category id to be retrieved.
	* @return bool FALSE if update failed or TRUE otherwise.
	* @see PC_gallery::Get_category();
	*/
	public function Sync_category($id) {
		$path = $this->config['gallery_path'];
		if ($id != 0) {
			$category = $this->Get_category($id);
			if (!$category) return false;
			$path .= $category['path'];
		}
		if ($handle = opendir($path)) {
			$file_s = $this->prepare("SELECT id FROM {$this->db_prefix}gallery_files WHERE filename=? and category_id=? LIMIT 1");
			$insert_file_s = $this->prepare("INSERT INTO {$this->db_prefix}gallery_files (filename,extension,category_id,size,date_added,date_modified,date_trashed) VALUES(?,?,?,?,?,?,0)");
			while (false !== ($file = readdir($handle))) {
				if (preg_match("#^(category_id_.+|Thumbs.db|\..+)$#ui", $file)) continue;
				$file_path = $path.'/'.$file;
				if (!is_file($file_path)) continue;
				$s = $file_s->execute(array($file, $id));
				if (!$s) return false;
				if ($file_s->rowCount()) continue;
				//validate file extension
				$info = pathinfo($file_path);
				if (!preg_match('/^'.$this->patterns['filename'].'$/ui', $info['basename'])) continue;
				$ext = strtolower($info['extension']);
				if (!isset($this->filetypes[strtolower($ext)])) continue;
				//validate file size
				$size = filesize($file_path);
				//if ($size > $this->config['max_filesize']) continue;
				//add file
				$now = time();
				$insert_file_s->execute(array($file, $ext, $id, $size, $now, $now));
			}
			closedir($handle);
			return true;
		}
		return false;
	}
	
	/**
	* Method used to get directory id by given path.
	* @param string $path given category path to get id from.
	* @return mixed FALSE there is no categories by given path, or category id otherwise.
	*/
	public function Get_category_id_by_path($path) {
		if (!is_dir($this->config['gallery_path'].$path)) return false;
		$r = glob($this->config['gallery_path'].$path.'/category_id_*');
		if (!count($r)) return false;
		return substr($r[0], strrpos($r[0], '_')+1);
	}
	// files
		
	/**
	* Method used to get file name by given request.
	* @param string $request given request to fetch file name from.
	* @return mixed $for_cropper array with key "errors" on failure, or requested file otherwise.
	* @see PC_gallery::Parse_file_request()
	* @see PC_gallery::Get_file()
	* @see PC_gallery::Show_cropper_image()
	* @see PC_gallery::Represent_file()
	*/
	public function Get_file_by_request($request, $for_cropper=false) {
		$r = $this->Parse_file_request($request);
		if (!$r['success'])
			return $r;
		$get_result = $this->Get_file($r['filename'], $r['category_path']);
		if (!$get_result['success']) {
			$response['errors'] = $get_result['errors'];
			return $response;
		}
		if ($for_cropper) {
			$r = $this->Show_cropper_image($r['filename'], $r['category_path'], $r['thumbnail_type'], $get_result['filedata']);
			if (!$r['success']) {
				$response['errors'] = $r['errors'];
				return $response;
			}
			return $r;
		}
		else {
			$r = $this->Represent_file($r['filename'], $r['category_path'], $r['thumbnail_type'], $get_result['filedata']);
			if (!$r['success']) {
				$response['errors'] = $r['errors'];
				return $response;
			}
			return $r;
		}
	}
	
	/**
	* Method used resize and store images to  given category path. This method uses PhpThumbFactory.
	* @param string $filename given file name to create thumbnails.
	* @param string $category_path given category path where thumbnails will be stored.
	* @return mixed allways array with key "success"
	* @see PhpThumbFactory::create()
	* @see PhpThumbFactory::show()
	*/
	public function Output_image_for_cropping($filename, $category_path) {
		$this->debug("Output_image_for_cropping($filename, $category_path)");
		try {
			$file_path = '';
			if (!empty($category_path))
				$file_path = $category_path.'/';
			$file_path .= $filename;
			$thumb = PhpThumbFactory::create($this->cfg['path']['gallery'] . $file_path, array('jpegQuality'=>40));
			/*$currentDimensions = $thumb->getCurrentDimensions();
			//print_r($currentDimensions); return;
			echo ($ratio = round($currentDimensions['width'] / 600, 1)).'<br />';
			echo $currentDimensions['width'] * $ratio.'<br />';
			return;*/
			//$ratio = max($currentDimensions['width'], $currentDimensions['height']) / $this->config['image_for_croping_max_dimensions'];
			$this->debug($this->config);
			$thumb->resize($this->config['image_for_croping_max_dimensions'], $this->config['image_for_croping_max_dimensions']);
			$thumb->show();
			return array("success"=>true);
		}
		catch (Exception $e) {
			header($_SERVER["SERVER_PROTOCOL"]." 503 Service Temporarily Unavailable");
			//$response['errors'][] = "read_image"; return $response;
			return array("success"=>true);
		}
	}
	
	/**
	* Method used to get file details by request.
	* @param string $request given request to fetch file details from
	* @return mixed array with keys "success", "filename", "category_path" and "thumbnail_type" on success, or array with key "errors" otherwise.
	*/
	public function Parse_file_request($request) {
		//echo $request;
		if (!preg_match('/^'.$this->patterns['file_request'].'$/ui', $request)) {
			$response['errors'][] = "request";
			return $response;
		}
		
		$request_items = explode('/', $request);
		$total_items = count($request_items);
		$filename = $request_items[$total_items-1];
		//echo preg_match("/^(thumbnail|small|large|(thumb-(".$this->patterns['thumbnail_type'].")))$/", $request_items[$total_items-($total_items>1?2:1)], $matches);
		//echo $request_items[$total_items-2];
		//echo !preg_match("/^thumb-(thumbnail|small|large)$/", $request_items[$total_items-2]);
		if (preg_match("/^(thumbnail|small|large|(thumb-(".$this->patterns['thumbnail_type'].")))$/", $request_items[$total_items-($total_items>1?2:1)], $matches)
		/*&& !preg_match("/^thumb-(thumbnail|small|large)$/", $request_items[$total_items-2])*/) {
			$thumbnail_type = isset($matches[3])?$matches[3]:$matches[0];
			if ($total_items > 2) {
				if ($total_items > 3) {
					$category_path = substr($request, 0, -(strlen($request_items[$total_items-2])+strlen($request_items[$total_items-1])+2));
				}
				else $category_path = $request_items[0];
			}
		}
		else {
			if ($total_items > 1) {
				if ($total_items > 2) {
					$category_path = substr($request, 0, -(strlen($request_items[$total_items-1])+1));
				}
				else $category_path = $request_items[0];
			}
		}
		/*
		print_pre(array(
			'success'=>true,
			'filename'=>$filename,
			'category_path'=>v($category_path),
			'thumbnail_type'=>v($thumbnail_type),
		));
		exit;
		*/
		
		return array(
			'success'=>true,
			'filename'=>$filename,
			'category_path'=>v($category_path),
			'thumbnail_type'=>v($thumbnail_type),
		);
	}
	
	/**
	* Method used to get information about given file from appropriate DB tables.
	* @param int $id given file to id to get
	* @return mixed array with keys "success", "filename", "filedata" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path()
	*/
	public function Get_file_by_id($id, $logger = null) {
		$returnOne = false;
		$queryParams = array();
		if (is_array($id)) {
			if (empty($id)) {
				$response['errors'][] = "file_id";
				return $response;
			}
			$queryParams = array_merge($queryParams, $id);
		}
		else {
			$id = (int)$id;
			if ($id < 1) {
				$response['errors'][] = "file_id";
				return $response;
			}
			$queryParams[] = $id;
			$returnOne = true;
		}
		$query = "SELECT f.id, size, extension, filename, f.category_id,count(files_in_use.file_id) in_use,"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.id'), array('distinct'=>true,'separator'=>'/'))." path_id"
			." FROM {$this->db_prefix}gallery_files f
			LEFT JOIN {$this->db_prefix}gallery_categories c ON c.id = category_id
			LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft BETWEEN path.lft AND path.rgt
			LEFT JOIN {$this->db_prefix}gallery_files_in_use files_in_use ON files_in_use.file_id=f.id
			WHERE f.id".(is_array($id)?' '.$this->sql_parser->in($id):'=?')
			."GROUP BY f.id,f.size,f.extension,f.filename,f.category_id";
		$r = $this->prepare($query);
		if (!is_null($logger)) {
			$logger->debug('Get_file_by_id query:');
			$logger->debug_query($query, $queryParams);
		}
		
		$success = $r->execute($queryParams);
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() < 1) {
			$response['errors'][] = "file_not_found";
			return $response;
		}
		$list = array();
		while ($d = $r->fetch()) {
			$this->Sort_path($d['path']);
			$this->Sort_path($d['path_id']);
			if (!empty($d['path'])) $d['path'] .= '/';
			$d['link'] = $d['path'].$d['filename'];
			$d['type'] = $this->filetypes[$d['extension']];
			$list[] = $d;
		}
		$r = array("success"=>true);
		if ($returnOne) {
			if (!count($list)) return false;
			$r["filedata"] = $list[0];
			return $r;
		}
		else return $list;
	}
	
	/**
	* Method used to get information about given file from appropriate DB tables by given category path.
	* @param string $filename given file to name to get.
	* @param string $category_path given category path to file for.
	* @return mixed array with keys "success", "filename", "filedata" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path()
	*/
	public function Get_file($filename, $category_path) {
		/** Already checked when parsed the request
		 * if (!preg_match('/^'.$this->patterns['filename'].'$/ui', $filename))
		 *	$response['errors'][] = "filename";
		 */
		if (!empty($category_path)) {
			/** Already checked when parsed the request
			 * if (!preg_match('/^'.$this->patterns['category_path'].'$/ui', $category_path))
			 *	$response['errors'][] = "category_path";
			 */
			//CMS_ROOT . 'gallery' . DS .
			$glob = glob($this->cfg['path']['gallery'] . $category_path.'/category_id_*');
			if (!isset($glob[0]))
				$response['errors'][] = "category_path";
		}
		if (count(v($response['errors']))) return $response;
		global $db;
		if (!empty($category_path)) {
			$category_id = substr($glob[0], strrpos($glob[0], '_')+1);
			$query = "SELECT
			file.extension,
			file.size,
			file.date_trashed,
			sum(path.date_trashed) category_trashed
			FROM {$this->db_prefix}gallery_files file
			LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id = file.category_id
			LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft between path.lft and path.rgt
			WHERE filename=?
			and file.category_id=?
			GROUP BY filename,file.extension,file.size,file.date_trashed LIMIT 1";
			$r = $db->prepare($query);
			$query_params = array($filename, $category_id);
			//print_pre($query_params);
			$success = $r->execute($query_params);
		}
		else {
			$r = $db->prepare("SELECT size,extension,date_trashed FROM {$this->db_prefix}gallery_files WHERE filename=? and category_id=0");
			$success = $r->execute(array($filename));
		}
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() != 1) {
			$response['errors'][] = "file_not_found";
			return $response;
		}
		$file = $r->fetch();
		if (empty($file['extension'])) {
			$response['errors'][] = "file_not_found";
			return $response;
		}
		/*if ($file['date_trashed'] > 0 || $file['category_trashed'] > 0) {
			$response['errors'][] = "file_is_trashed";
			return $response;
		}*/
		return array(
			"success"=> true,
			"filedata"=> $file
		);
	}
	
	public function Get_crop_data($file_path, $thumb_type) {
		$this->debug("Get_crop_data($file_path, ");
		$this->debug($thumb_type, 1);
		if (is_array($thumb_type)) {
			$type = $thumb_type;
		}
		else {
			$type = $this->Get_thumbnail_type($thumb_type);
		}
		
		$this->debug($type, 1);
		$this->debug('creating: ' . $file_path, 1);
		$this->last_thumb = $thumb = PhpThumbFactory::create($file_path, array('resizeUp' => true, 'jpegQuality'=>$type['thumbnail_quality']));
		$this->debug('Current dimensions before resizing:', 3);
		$this->debug($thumb->currentDimensions, 4);
		if ($type['thumbnail_type'] == "thumbnail" || $type['use_adaptive_resize']) {
			$resize_to_w = $type['thumbnail_max_w'];
			$resize_to_h = $type['thumbnail_max_h'];
			if ($resize_to_w > $thumb->currentDimensions['width']) {
				$this->debug('Original width is smaller. Reducing resize_to dimmensions:', 3);
				$ratio = $resize_to_w / $thumb->currentDimensions['width'];
				$this->debug("Resize ratio: $resize_to_w / {$thumb->currentDimensions['width']} = " . $ratio, 4);
				$resize_to_h = round($resize_to_h / $ratio);
				$resize_to_w = $thumb->currentDimensions['width'];
				$dimensions_were_reduced = true;
				$this->debug("New resize to dimmensions: $resize_to_w and $resize_to_h", 3);
			}
			if ($resize_to_h > $thumb->currentDimensions['height']) {
				$this->debug('Original height is smaller. Reducing resize_to dimensions:', 3);
				$ratio = $resize_to_h / $thumb->currentDimensions['height'];
				$this->debug("Resize ratio: $resize_to_h / {$thumb->currentDimensions['height']} = " . $ratio, 4);
				$resize_to_w = round($resize_to_w / $ratio);
				$resize_to_h = $thumb->currentDimensions['height'];
				$this->debug("New resize to dimmensions: $resize_to_w and $resize_to_h", 3);
			}
			$this->debug("thumb->adaptiveResize($resize_to_w, $resize_to_h)", 2);
			$thumb->adaptiveResize($resize_to_w, $resize_to_h);
		}
		else {
			$resize_to_w = $type['thumbnail_max_w'];
			$resize_to_h = $type['thumbnail_max_h'];
			if ($resize_to_w > $thumb->currentDimensions['width']) {
				$this->debug('Original width is smaller. Reducing resize_to dimmensions:', 3);
				$resize_to_w = $thumb->currentDimensions['width'];
				$this->debug("New resize to dimmensions: $resize_to_w and $resize_to_h", 3);
			}
			if ($resize_to_h > $thumb->currentDimensions['height']) {
				$this->debug('Original height is smaller. Reducing resize_to dimensions:', 3);
				$resize_to_h = $thumb->currentDimensions['height'];
				$this->debug("New resize to dimmensions: $resize_to_w and $resize_to_h", 3);
			}
			$this->debug("thumb->resize($resize_to_w, $resize_to_h)", 2);
			$thumb->resize($resize_to_w, $resize_to_h);
		}
		$crop_data['x'] = ($thumb->originalImageInfo[0]/2)-($thumb->currentDimensions['width']/2);
		$crop_data['y'] = ($thumb->originalImageInfo[1]/2)-($thumb->currentDimensions['height']/2);
		$crop_data['w'] = $thumb->currentDimensions['width'];
		$crop_data['h'] = $thumb->currentDimensions['height'];
		
		$this->debug('$crop_data:', 3);
		$this->debug($crop_data, 4);
		
		$this->debug('newDimensions:', 3);
		$this->debug($thumb->getNewDimensions(), 4);

		$this->debug('Current dimensions after resizing:', 3);
		$this->debug($thumb->currentDimensions, 4);
		$this->debug('imagecopyresampled_params:', 3);
		//$this->debug($thumb->imagecopyresampled_params, 4);
		/*if ($thumbnail_type == "thumbnail" || $thumbnail_type == "large") {
			if ($type['use_adaptive_resize']) {
				$thumb->adaptiveResize($type['thumbnail_max_w'], $type['thumbnail_max_h']);
			}
			else $thumb->resize($type['thumbnail_max_w'], $type['thumbnail_max_h']);
			//$crop_data['x'] = $thumb->originalImageInfo[0]/$thumb->currentDimensions['width'];
			$crop_data['x'] = ($thumb->originalImageInfo[0]/2)-($thumb->currentDimensions['width']/2);
			//$crop_data['y'] = $thumb->originalImageInfo[1]/$thumb->currentDimensions['height'];
			$crop_data['y'] = ($thumb->originalImageInfo[1]/2)-($thumb->currentDimensions['height']/2);
			$crop_data['w'] = $thumb->currentDimensions['width'];
			$crop_data['h'] = $thumb->currentDimensions['height'];

		}
		else {
			$thumb->adaptiveResize($type['thumbnail_max_w'], $type['thumbnail_max_h']);
			$crop_data['x'] = 0;
			$crop_data['y'] = 0;
			$crop_data['w'] = $thumb->originalImageInfo[0];
			$crop_data['h'] = $thumb->originalImageInfo[1];
		}
		*/
		/*print_pre($crop_data);
		print_pre($thumb->originalImageInfo);
		print_pre($thumb->currentDimensions);
		die;*/
		return $crop_data;
	}
	
	
	/**
	* Method  used to output given file to request side. This method uses PhpThumbFactory class.
	* @param string $filename given file to name to get.
	* @param string $category_path given category path to render file from.
	* @param string $thumbnail_type given thumbnail type to get.
	* @return mixed array with key "success" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path()
	* @see PC_gallery::Get_thumbnail_types()
	*/
	public function Output_file($filename, $category_path, $thumbnail_type='', $file, $file_data = false) {
		$this->debug("Output_file($filename, $category_path, $thumbnail_type)");
		$this->debug($file, 1);
		$set = ini_set('memory_limit', '512M');
		if (!empty($thumbnail_type) && !preg_match('/^'.$this->patterns['thumbnail_type'].'$/', $thumbnail_type)) {
			$response['errors'][] = "thumbnail_type";
			return $response;
		}
		if ($this->filetypes[$file['extension']] == "image") {
			if (!empty($thumbnail_type)) {
				$thumbnail_path = $this->cfg['path']['gallery'];
				if (!empty($category_path) && $category_path != '/') {
					$thumbnail_path .= $category_path.'/';
				}
				$thumbnail_path .= 'thumb-'.$thumbnail_type;
				if (is_file($thumbnail_path.'/'.$filename)) {
					try {
						$thumb = PhpThumbFactory::create($thumbnail_path.'/'.$filename);
						$thumb->show();
						return array("success"=>true);
					}
					catch (Exception $e) {
						header($_SERVER["SERVER_PROTOCOL"]." 503 Service Temporarily Unavailable");
						//$response['errors'][] = "read_image"; return $response;
						return array("success"=>true);
					}
				}
				else {
					$thumbnail_types = $this->Get_thumbnail_types();
					if (!isset($thumbnail_types[$thumbnail_type])) {
						$response['errors'][] = "thumbnail_not_found";
						return $response;
					}
					$type =& $thumbnail_types[$thumbnail_type];
					if (!is_dir($thumbnail_path)) if (!$this->mkdir($thumbnail_path)) {
						$response['errors'][] = "create_thumbnail_directory";
						return $response;
					}
					try {
						$file_path = $this->cfg['path']['gallery'];
						if (!empty($category_path) && $category_path != '/') {
							$file_path .= $category_path.'/';
						}
						$file_dir = $file_path;	
						$file_path .= $filename;
						
						$crop_data_path = $file_dir.'thumb-'.$thumbnail_type.'/'.$filename.'.txt';
						$this->debug('$crop_data_path: ' . $crop_data_path, 2);
						//$output['crop_data_path'] = $crop_data_path;
						if (is_array($file_data) and is_file($crop_data_path)) {
							$this->debug(':) file exists!', 3);
							$crop_data = file_get_contents($crop_data_path);
							$crop_data_n = explode('|', $crop_data);
							$crop_data = array();
							$crop_data['x'] = $crop_data_n[0];
							$crop_data['y'] = $crop_data_n[1];
							$crop_data['w'] = $crop_data_n[2];
							$crop_data['h'] = $crop_data_n[3];
							
							$this->Crop_thumbnail($file_data, $thumbnail_type, $crop_data['x'], $crop_data['y'], $crop_data['w'], $crop_data['h']);
						}
						else {
							$crop_data = $this->Get_crop_data($file_path, $type);
						}
						
						$this->debug("crop data:", 2);
						$this->debug($crop_data, 3);
						
						$thumb = $this->last_thumb;
						
						$thumb_path = $thumbnail_path.'/'.$filename;
						
						
						$old_umask = umask(self::UMASK);
						$this->debug("old_umask: $old_umask");
						
						$thumb->save($thumb_path);
						umask($old_umask);
						
						$crop_data = $crop_data['x'].'|'.$crop_data['y'].'|'.$crop_data['w'].'|'.$crop_data['h'];
						$crop_data_file = $thumb_path.'.txt';
						file_put_contents($crop_data_file, $crop_data);
						$thumb->show();
						
						$rr = $this->chmod($crop_data_file);
						$rr = $this->chmod($thumb_path);
												
						return array("success"=>true);
					}
					catch (Exception $e) {
						header($_SERVER["SERVER_PROTOCOL"]." 503 Service Temporarily Unavailable");
						//$response['errors'][] = "read_image"; return $response;
						return array("success"=>true);
					}
				}
			}
			else {
				try {
					$file_path = $this->cfg['path']['gallery'];
					if (!empty($category_path) && $category_path != '/')
						$file_path .= $category_path.'/';
					$file_path .= $filename;
					//echo $file_path;
					if ($file['extension']=='gif') {
						header('Content-type: image/gif', true); 
						echo file_get_contents($file_path);
						return array("success"=>true);
					}
					$thumb = PhpThumbFactory::create($file_path);
					$thumb->show();
					return array("success"=>true);
				}
				catch (Exception $e) {
					header($_SERVER["SERVER_PROTOCOL"]." 503 Service Temporarily Unavailable");
					//$response['errors'][] = "read_image"; return $response;
					return array("success"=>true);
				}
			}
		}
		else {
			if (!empty($thumbnail_type)) {
				if (!empty($category_path) && $category_path != '/') $category_path .= '/'; 
				$location = $this->cfg['url']['base'].$this->config['gallery_directory'].'/'.$category_path.$filename;
				//moved permanently
				header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
				header('Location: '.$location);
				return array("success"=>true);
			}
			$file_path = $this->cfg['path']['gallery'];
			if (!empty($category_path) && $category_path != '/')
				$file_path .= $category_path.'/';
			$file_path .= $filename;
			if (!is_file($file_path)) {
				header($_SERVER["SERVER_PROTOCOL"]." 503 Service Temporarily Unavailable");
				//$response['errors'][] = "file_doesnt_exist"; return $response;
				return array("success"=>true);
			}
			function get_file_extension($file) {
				$file_parts = explode('.',$file);
				return array_pop($file_parts);
			}
			function get_mimetype($value='') {
				$ct['flv'] = 'video/x-flv';
				$ct['htm'] = 'text/html';
				$ct['html'] = 'text/html';
				$ct['txt'] = 'text/plain';
				$ct['asc'] = 'text/plain';
				$ct['bmp'] = 'image/bmp';
				$ct['gif'] = 'image/gif';
				$ct['jpeg'] = 'image/jpeg';
				$ct['jpg'] = 'image/jpeg';
				$ct['jpe'] = 'image/jpeg';
				$ct['png'] = 'image/png';
				$ct['ico'] = 'image/vnd.microsoft.icon';
				$ct['mpeg'] = 'video/mpeg';
				$ct['mpg'] = 'video/mpeg';
				$ct['mpe'] = 'video/mpeg';
				$ct['qt'] = 'video/quicktime';
				$ct['mov'] = 'video/quicktime';
				$ct['avi']  = 'video/x-msvideo';
				$ct['wmv'] = 'video/x-ms-wmv';
				$ct['mp2'] = 'audio/mpeg';
				$ct['mp3'] = 'audio/mpeg';
				$ct['rm'] = 'audio/x-pn-realaudio';
				$ct['ram'] = 'audio/x-pn-realaudio';
				$ct['rpm'] = 'audio/x-pn-realaudio-plugin';
				$ct['ra'] = 'audio/x-realaudio';
				$ct['wav'] = 'audio/x-wav';
				$ct['css'] = 'text/css';
				$ct['zip'] = 'application/zip';
				$ct['7z'] = 'application/x-7z-compressed';
				$ct['rar'] = 'application/x-rar-compressed';
				$ct['pdf'] = 'application/pdf';
				$ct['doc'] = 'application/msword';
				$ct['bin'] = 'application/octet-stream';
				$ct['exe'] = 'application/octet-stream';
				$ct['class']= 'application/octet-stream';
				$ct['dll'] = 'application/octet-stream';
				$ct['xls'] = 'application/vnd.ms-excel';
				$ct['ppt'] = 'application/vnd.ms-powerpoint';
				$ct['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
				
				$ct['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
				$ct['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
				
				$ct['wbxml']= 'application/vnd.wap.wbxml';
				$ct['wmlc'] = 'application/vnd.wap.wmlc';
				$ct['wmlsc']= 'application/vnd.wap.wmlscriptc';
				$ct['dvi'] = 'application/x-dvi';
				$ct['spl'] = 'application/x-futuresplash';
				$ct['gtar'] = 'application/x-gtar';
				$ct['gzip'] = 'application/x-gzip';
				$ct['js'] = 'application/x-javascript';
				$ct['swf'] = 'application/x-shockwave-flash';
				$ct['tar'] = 'application/x-tar';
				$ct['xhtml']= 'application/xhtml+xml';
				$ct['au'] = 'audio/basic';
				$ct['snd'] = 'audio/basic';
				$ct['midi'] = 'audio/midi';
				$ct['mid'] = 'audio/midi';
				$ct['m3u'] = 'audio/x-mpegurl';
				$ct['tiff'] = 'image/tiff';
				$ct['tif'] = 'image/tiff';
				$ct['rtf'] = 'text/rtf';
				$ct['wml'] = 'text/vnd.wap.wml';
				$ct['wmls'] = 'text/vnd.wap.wmlscript';
				$ct['xsl'] = 'text/xml';
				$ct['xml'] = 'text/xml';
				$ct['cdr'] = 'application/x-cdr';
				$extension = get_file_extension($value);
				if (!$type = v($ct[strtolower($extension)])) {
					$type = 'text/html';
				}
				return $type;
			}
			// set headers
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public");
			header("Content-Description: File Transfer");
			header("Content-Type: ".get_mimetype($filename));
			//force browser to show 'Save as' window
			//header("Content-Disposition: attachment; filename=\"$filename\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$file['size']);
			// download
			// @readfile($file_path);
			$file_contents = @fopen($file_path,"rb");
			if (!$file_contents) {
				header($_SERVER["SERVER_PROTOCOL"]." 503 Service Temporarily Unavailable");
				//$response['errors'][] = "read_file"; return $response;
				return array("success"=>true);
			}
			while(!feof($file_contents)) {
				print(fread($file_contents, 1024*8));
				flush();
				if (connection_status()!=0) {
					@fclose($file_contents);
				}
			}
			@fclose($file_contents);
			return array("success"=>true);
		}
	}
	
	/**
	* Method used to generate unique file name for given file.
	* @param string $filename given file to use as base name.
	* @param string $path given path where the will be stored.
	* @return mixed array with keys "success" and "filename" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Format_name_for_link()
	*/
	private function Generate_unique_filename($filename, $path) {
		if (strlen($filename) < 3)
			$response['errors'][] = "filename";
		//format new image name
		$extension_start = strrpos($filename, '.');
		$image_extension = substr($filename, $extension_start);
		$image_name_no_extension = substr($filename, 0, -strlen($image_extension));
		$filename = $this->Format_name_for_link($image_name_no_extension).$image_extension;
		# Check if specified path exists
		if (!is_dir($path))
			$response['errors'][] = "path";
		if (count(v($response['errors'])) != 0) return $response;
		$prefix = '';
		if (is_file($path.$filename)) {
			$prefix = date('Y').'-';
			if (is_file($path.$prefix.$filename)) {
				$prefix .= date('n').'-';
				if (is_file($path.$prefix.$filename)) {
					$prefix .= date('j').'-';
					if (is_file($path.$prefix.$filename)) {
						for ($a=1; is_file($path.$prefix.$a.'-'.$filename); $a++) {}
						return array("success"=>true,"filename"=>$prefix.$a.'-'.$filename);
					}
				}
			}
		}
		return array("success"=>true,"filename"=>$prefix.$filename);
	}
	
	/**
	* Method used to get files which are marked as deleted in appropriate DB tables.
	* @param int $category_id given category id to find files by.
	* @return mixed array with keys "success" and "files" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path()
	*/
	public function Get_trashed_files($category_id=null) {
		$in_category = false;
		if (!empty($category_id) && $category_id>0) {
			$in_category = true;
			$category_id = (int)$category_id;
			if ($category_id < 1) $response['errors'][] = "category_id";
		}
		global $db;
		$r = $db->prepare("SELECT files.*,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
		."sum(path.date_trashed) category_trashed,
		count(files_in_use.file_id) in_use "
		.($in_category?
			"FROM {$this->db_prefix}gallery_categories category LEFT JOIN {$this->db_prefix}gallery_files files ON files.category_id=category.id"
			:"FROM {$this->db_prefix}gallery_files files LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id=files.category_id"
		)
		." LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
		LEFT JOIN {$this->db_prefix}gallery_files_in_use files_in_use ON files_in_use.file_id=files.id
		WHERE ".($in_category?"category.id=? ":'files.date_trashed>0 ')
		."GROUP BY files.id,files.filename,files.extension,files.category_id,files.size,files.date_added,files.date_modified,files.date_trashed");
		if ($in_category) $success = $r->execute(array($category_id));
		else $success = $r->execute();
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		$files = array();
		while ($data = $r->fetch()) {
			$this->Sort_path($data['path']);
			$files[] = $data;
		}
		return array("success"=>true,"files"=>$files);
	}
	
	/**
	* Method used to get files by category id and filter.
	* @param int $category_id given category id to  find files by.
	* @param mixed $filter given files filter to remove files from returned collection.
	* @param int $tree given tree level to obtain files. Not used on 2012-02-06.
	* @return mixed array with keys "success" and "files" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path()
	*/
	public function Get_files($category_id=0, $filter='', $tree=0) {
		//$tree = 0; //temporary disallow tree
		$file_ids = array();
		$file_ids_clause = '';
		if (is_array($category_id)) {
			$file_ids = $category_id;
			$category_id = 0;
			$file_ids_clause = ' AND f.id ' . $this->sql_parser->in($file_ids) . ' ';
		}
		$category_id = (int)$category_id;
		if ($category_id < 0) $response['errors'][] = "category_id";
		if ($tree != 0 && $tree != 1)
			$response['errors'][] = "bool_tree";
		if (count(v($response['errors']))) return $response;
		global $db;
		if (strlen($filter) > 0) {
			$r = $db->prepare("SELECT
			file.*,
			count(files_in_use.file_id) in_use,"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.category'), array('distinct'=>true,'separator'=>'/'))." category,"
			."sum(path.date_trashed) category_trashed
			FROM {$this->db_prefix}gallery_files file
			LEFT JOIN {$this->db_prefix}gallery_files_in_use files_in_use ON files_in_use.file_id=file.id
			LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id = file.category_id
			LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
			WHERE file.date_trashed=0
			and filename like ?
			GROUP BY file.id,file.filename,file.extension, file.category_id,file.size,file.date_added, file.date_modified,file.date_trashed");
			$success = $r->execute(array('%'.$filter.'%'));
		}
		else {
			//future edit: check if category exists
			if ($category_id == 0) {
				$cat_where = '';
				if ($tree == 0) {
					$cat_where = ' category_id=0 and ';
					$query = "SELECT
						files.*,
						count(files_in_use.file_id) in_use
						FROM {$this->db_prefix}gallery_files files
						LEFT JOIN {$this->db_prefix}gallery_files_in_use files_in_use ON files_in_use.file_id=files.id
						WHERE $cat_where date_trashed=0
						GROUP BY files.id,files.filename,files.extension,files.category_id,files.size,files.date_added,files.date_modified,files.date_trashed";
					$r = $db->prepare($query);
						/*$r = $db->query("SELECT
						gallery_images.*,
						#group_concat(distinct path.album_directory order by path.album_lft separator '/') path,#
						sum(path.album_date_trashed) album_trashed
						FROM gallery_images
						LEFT JOIN {$this->db_prefix}gallery_albums album ON album.category_id = image_album_id
						LEFT JOIN {$this->db_prefix}gallery_albums path ON album.album_lft BETWEEN path.album_lft and path.album_rgt
						WHERE image_date_trashed=0$where
						GROUP BY image_id
						ORDER BY album.album_lft
						LIMIT 30");*/
					//echo $this->get_debug_query_string($query, array());
					$success = $r->execute();
				}
				else {
					$query = "SELECT f.*,"
						.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
						." sum(path.date_trashed) category_trashed,
						count(files_in_use.file_id) in_use
						FROM {$this->db_prefix}gallery_categories category
						LEFT JOIN {$this->db_prefix}gallery_files f ON f.category_id = category.id
						LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
						LEFT JOIN {$this->db_prefix}gallery_files_in_use files_in_use ON files_in_use.file_id=f.id
						WHERE $cat_where f.date_trashed=0 $file_ids_clause
						GROUP BY f.id,f.filename,f.extension,f.category_id,f.size,f.date_added,f.date_modified,f.date_trashed";
					$r = $db->prepare($query);
					//echo $this->get_debug_query_string($query, $file_ids);
					$success = $r->execute($file_ids);
				}
				
			}
			else {
				$cat_where = '';
				$cat_params = array();
				if ($tree == 0) {
					$cat_where = 'f.category_id=? and ';
					$cat_params[] = $category_id;
				}
				else {
					$category_model = new PC_gallery_category_model();
					$category_model->debug = true;
					$category_data = $category_model->get_data($category_id);
					//echo $category_model->get_debug_string();
				
					if ($category_data) {
						$cat_where = ' category.lft >= ? AND category.rgt <= ? AND ';
						$cat_params[] = $category_data['lft'];
						$cat_params[] = $category_data['rgt'];
					}
				}
				$query = "SELECT f.*,"
					.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
					." sum(path.date_trashed) category_trashed,
					count(files_in_use.file_id) in_use
					FROM {$this->db_prefix}gallery_categories category
					LEFT JOIN {$this->db_prefix}gallery_files f ON f.category_id = category.id
					LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
					LEFT JOIN {$this->db_prefix}gallery_files_in_use files_in_use ON files_in_use.file_id=f.id
					WHERE $cat_where f.date_trashed=0
					GROUP BY f.id,f.filename,f.extension,f.category_id,f.size,f.date_added,f.date_modified,f.date_trashed";
				$r = $db->prepare($query);
				//echo $this->get_debug_query_string($query, $cat_params);
				$success = $r->execute($cat_params);
			}
		}
		//print_pre($this->db->errorInfo());
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		$files = array();
		while ($data = $r->fetch()) {
			if (v($data['category_trashed']) < 1) {
				$this->Sort_path($data['path']);
				if (isset($data['category'])) $this->Sort_path($data['category']);
				$files[] = $data;
			}
		}
		return array("success"=>true,"files"=>$files);
	}
	
	public function get_category_data($category_id) {
		global $db;
		$query = "SELECT ".$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
			." FROM {$this->db_prefix}gallery_categories category
			LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft between path.lft and path.rgt
			WHERE category.id=? LIMIT 1";
		$query_params = array($category_id);
		//echo $this->get_debug_query_string($query, $query_params);
		$r = $db->prepare($query);
		$success = $r->execute($query_params);
		if (!$success) {
			return false;
		}
		$data = false;
		if ($category_id > 0) {
			$data = $r->fetch();
		}
		return $data;
	}
	
	public function get_category_watermark($data) {
		$watermark = '';
		$path_parts = array();
		if ($data) {
			$path_parts = explode('/', $data['path']);
		}
		array_unshift($path_parts, '');
		foreach ($path_parts as $path_part) {
			if (!empty($path_part)) {
				$path_part .= '/';
			}
			$full_path = $this->config['gallery_path'] . $path_part;
			if (file_exists($full_path . 'watermark.png')) {
				$watermark = $full_path . 'watermark.png';
			}
			elseif (file_exists($full_path . 'watermark.jpg')) {
				$watermark = $full_path . 'watermark.jpg';
			}
			elseif (file_exists($full_path . 'watermark.gif')) {
				$watermark = $full_path . 'watermark.gif';
			}
			elseif (file_exists($full_path . 'watermark.bmp')) {
				$watermark = $full_path . 'watermark.bmp';
			}
			if (!empty($watermark)) {
				$this->debug('Watermark in gallery: ' . $watermark, 2);
				continue;
			}
		}
		return $watermark;
	}
	
	public function add_watermark($file_path, $watermark) {
		if (!empty($watermark)) {
			$this->debug('Watermark: ' . $watermark, 2);
			$this->debug('Will put watermark on ' . $file_path, 2);
			$thumb = PhpThumbFactory::create($file_path);
			$watermark_obj = PhpThumbFactory::create($watermark);
			$thumb->addWatermark($watermark_obj, 'center', 100, 0, 0);
			$thumb->save($file_path);
		}
	}
	
	/**
	* Method used to save file already stored in temporary directory to the appropriate location. Method stores file in the filesystem as well in the 
	* referenced DB tables.
	* @param int $category_id given category id to upload given file to.
	* @param string $temp_file given file to upload.
	* @return mixed array with keys "success" and "id" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Generate_unique_filename()
	* @see PC_gallery::Sort_path()
	*/
	public function Upload_file($category_id, &$temp_file) {
		$this->debug("Upload_file()");
		$this->debug($temp_file, 1);
		$this->debug($_POST, 1);
		$category_id = (int)$category_id;
		if ($category_id < 0)
			$response['errors'][] = "category_id";
		if (empty($_FILES)) {
			$response['errors'][] = "files_empty";
		}
		elseif (!@is_uploaded_file($temp_file['tmp_name']))
			$response['errors'][] = "file_not_found";
		else {
			//validate file extension
			$pathinfo = pathinfo('a' . $temp_file['name']);
			$pathinfo['basename'] = substr($pathinfo["basename"], 1);
			$pathinfo['filename'] = substr($pathinfo["filename"], 1);
			if (!preg_match('/^'.$this->patterns['filename'].'$/ui', $pathinfo['basename'])) {
				$this->debug(' :( filename did not passed pattern ' . $this->patterns['filename'], 4);
				$response['errors'][] = "filename";
			}
				
			$extension = strtolower($pathinfo['extension']);
			if (!isset($this->filetypes[strtolower($pathinfo['extension'])]))
				$response['errors'][] = "unallowed_file";
			//validate file size
			$size = filesize($temp_file['tmp_name']);
			//if ($size > $this->config['max_filesize'])
				//$response['errors'][] = "filesize";
		}
		if (count(v($response['errors'])) != 0) return $response;
		global $db;
		$r = $db->prepare("SELECT ".$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
		." FROM {$this->db_prefix}gallery_categories category
		LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft between path.lft and path.rgt
		WHERE category.id=? LIMIT 1");
		$success = $r->execute(array($category_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		$data = false;
		if ($category_id > 0) {
			$data = $r->fetch();
			if (empty($data['path'])) {
				$response['errors'][] = "category_not_found";
				return $response;
			}
			else $this->Sort_path($data['path']);
			$file_path = $this->config['gallery_path'].$data['path'].'/';
		}
		else {
			$file_path = $this->config['gallery_path'];
		}
		$r = $this->Generate_unique_filename($temp_file['name'], $file_path);
		
		if (!$r['success']) {
			$this->debug(' :( Could not generate unique file name ', 4);
			$response['errors'][] = "filename";
			return $response;
		}
		$filename = $r['filename'];
		if (!move_uploaded_file($temp_file['tmp_name'], $file_path.$filename)) {
			$response['errors'][] = "move_uploaded_file";
			return $response;
		}
		if ($this->filetypes[strtolower($pathinfo['extension'])] == 'image') {
			$uploaded_file_path = $file_path.$filename;
			$this->chmod($uploaded_file_path);
			$watermark = '';
			$this->core->Init_hooks('core/gallery/upload/watermark', array(
				'dialog_type'=> v($_POST['dialog_type']),
				'watermark'=> &$watermark,
			));

			if (empty($watermark)) {
				$watermark = $this->get_category_watermark($data);
			}

			$file_path_parts = pathinfo($uploaded_file_path);
			if ($file_path_parts['filename'] == 'watermark') {
				$watermark = '';
			}
			$original_file_path =  $file_path_parts['dirname'] . '/' . $file_path_parts['filename'] . '._pc_original_.' . $file_path_parts['extension'];
			$this->debug("making original: copy('$uploaded_file_path', '$original_file_path')", 2);
			copy($uploaded_file_path, $original_file_path);
			$this->chmod($original_file_path);
			if (!empty($watermark)) {
				$oldumask = umask(self::UMASK);
				$this->add_watermark($uploaded_file_path, $watermark);
				$this->chmod($uploaded_file_path);
				umask($oldumask);
			}
		}
			
		$now = time();
		$r = $db->prepare("INSERT INTO {$this->db_prefix}gallery_files (filename,extension,category_id,size,date_added,date_modified,date_trashed) VALUES(?,?,?,?,?,?,0)");
		$success = $r->execute(array($filename, $extension, $category_id, $size, $now, $now));
		//print_pre($this->db->errorInfo());
		//print_pre($r->errorInfo());
		if (!$success) {
			@unlink($file_path.$filename);
			$response['errors'][] = "database";
			return $response;
		}
		return array("success"=>true,"id"=>$db->lastInsertId($this->sql_parser->Get_sequence('gallery_files')));
	}
	
	/**
	* Method used to delete file stored in filesystem and appropriate DB tables.
	* @param int $file_id given file id to be deleted.
	* @return mixed array with keys "success" and "filename" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path()
	*/
	public function Delete_file($file_id) {
		$file_id = (int)$file_id;
		if ($file_id < 1) {
			$response['errors'][] = "file_id";
			return $response;
		}
		global $db;
		$r = $db->prepare("SELECT filename, extension,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true, 'separator'=>'/'))." path"
		." FROM {$this->db_prefix}gallery_files f
		LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id=category_id
		LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
		WHERE f.id=? GROUP BY f.filename,f.extension LIMIT 1");
		$success = $r->execute(array($file_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		$data = $r->fetch();
		if (empty($data['filename'])) {
			$response['errors'][] = "file_not_found";
			return $response;
		}
		$this->Sort_path($data['path']);
		$full_path = $this->config['gallery_path'].$data['path'];
		if (isset($data['path'])) $full_path .= '/';
		/** This is commented in order to allow deletion of image thumbnails and database index even if original image
		 ** is already deleted from hard disk
		if (!is_file($full_path.$data['filename']) {
			$response['errors'][] = "database";
			return $response;
		}
		if (!unlink($full_path.$data['filename'])) {
			$response['errors'][] = "delete";
			return $response;
		}*/
		@unlink($full_path.$data['filename']);
		
		$file_path_parts = pathinfo($full_path.$data['filename']);
		$orig_full_path =  $file_path_parts['dirname'] . '/' . $file_path_parts['filename'] . '._pc_original_.' . $file_path_parts['extension'];
		
		
		@unlink($orig_full_path);
		
		if ($this->filetypes[$data['extension']] == 'image') {
			//delete all image thumbs found in the album path directory named thumb-*
			//this enables to delete image with all its' thumbs that doesn't exist in the db anymore
			foreach (glob($full_path.'thumb-*') as $thumbnail_path) {
				if (is_dir($thumbnail_path)) {
					//delete thumb
					@unlink($thumbnail_path.'/'.$data['filename']);
					//delete thumb crop data
					@unlink($thumbnail_path.'/'.$data['filename'].'.txt');
				}
			}
		}
		//old thumbnail deletion by type list from the database (doesn't delete thumbs that doesn't exist in the db)
		/*foreach ($this->Get_thumbnail_types() as $thumbnail_type) {
			$thumbnail_path = $full_path.'thumb-'.$thumbnail_type['thumbnail_type'].'/'.$data['filename'];
			@unlink($thumbnail_path);
		}*/
		$r = $db->prepare("DELETE FROM {$this->db_prefix}gallery_files WHERE id=?");
		$r->execute(array($file_id));
		$r = $db->prepare("DELETE FROM {$this->db_prefix}gallery_files_in_use WHERE file_id=?");
		$r->execute(array($file_id));
		return array("success"=>true, "filename"=>$data['filename']);
	}
	
	public function Delete_thumbnails($category_id = 0, $file_ids = array()) {
		if (!empty($file_ids) and is_array($file_ids)) {
			$category_id = $file_ids;
		}
		$files = $this->Get_files($category_id, '', true);
		$thumb_types = $this->Get_thumbnail_types();
		
		$category_watermarks = array();
		
		//print_r($thumb_types);
		//print_r($files);
		//print_r($this->filetypes);
		foreach ($files['files'] as $data) {
			if ($this->filetypes[$data['extension']] == 'image') {
				if (!isset($category_watermarks[$data['category_id']])) {
					$category_watermarks[$data['category_id']] = $this->get_category_watermark($this->get_category_data($data['category_id']));
				}
				
				
				$full_path = $this->config['gallery_path'].$data['path'];
				$file_full_path = $full_path . '/' . $data['filename'];
				$orig_file_full_path = $this->get_original_file_path($file_full_path);
				
				if (file_exists($orig_file_full_path)) {
					@unlink($file_full_path);
					$oldumask = umask(self::UMASK);
					copy($orig_file_full_path, $file_full_path);
					$this->add_watermark($file_full_path, $category_watermarks[$data['category_id']]);
					$this->chmod($file_full_path);
					umask($oldumask);
				}
				
				if (isset($data['path'])) $full_path .= '/';

				//delete all image thumbs found in the album path directory named thumb-*
				foreach (glob($full_path.'thumb-*') as $thumbnail_path) {
					if (is_dir($thumbnail_path)) {
						//delete thumb
						$delete_thumb_path = $thumbnail_path.'/'.$data['filename'];
						//echo "\n unlink(".$delete_thumb_path.")";
						@unlink($delete_thumb_path);

					}
				}
			}
		}
		return array("success"=>true);
	}
	
	/**
	* Method used to change given file state to "deleted" on the appropriate DB tables. Given file on the filesystem is not modified.
	* @param int $file_id given file id to be deleted.
	* @return mixed array with key "success" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path()
	*/
	public function Trash_file($file_id) {
		$file_id = (int)$file_id;
		if ($file_id < 1) {
			$response['errors'][] = "file_id";
			return $response;
		}
		global $db;
		$now = time();
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_files SET date_trashed=? WHERE id=? and date_trashed=0");
		$success = $r->execute(array($now, $file_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() != 1) {
			$r = $db->prepare("SELECT count(*) FROM {$this->db_prefix}gallery_files WHERE id=?");
			$r->execute(array($file_id));
			if ($r->fetchColumn() == 1) {
				$response['errors'][] = "already_trashed";
				return $response;
			}
			else {
				$response['errors'][] = "file_not_found";
				return $response;
			}
		}
		return array("success"=>true);
	}
	
	/**
	* Method used to change given file state to "not deleted" on the appropriate DB tables. Given file on the filesystem is not modified.
	* @param int $file_id given file id to be deleted.
	* @return mixed array with key "success" on success, or array with key "errors" otherwise.
	*/
	public function Restore_file($file_id) {
		$file_id = (int)$file_id;
		if ($file_id < 1) {
			$response['errors'][] = "file_id";
			return $response;
		}
		global $db;
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_files SET date_trashed=0 WHERE id=?");
		$success = $r->execute(array($file_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($db->rowCount() != 1) {
			$r = $db->prepare("SELECT count(*) FROM {$this->db_prefix}gallery_files WHERE id=?");
			$r->execute(array($file_id));
			if ($r->fetchColumn() == 1) {
				$response['errors'][] = "file_not_in_a_trash";
				return $response;
			}
			else {
				$response['errors'][] = "file_not_found";
				return $response;
			}
		}
		return array("success"=>true);
	}
	
	/**
	* Method used to move given file to given category. Changes  are made on appropriate DB tables and on the file on the filesystem.
	* @param int $file_id given file id to be moved.
	* @param int $category_id given category id where the file will be placed after execution.
	* @return mixed array with key "success" on success, or array with key "errors" otherwise.
	*/
	public function Move_file($file_id, $category_id) {
		$this->debug($file_id, $category_id);
		$file_id = (int)$file_id;
		if ($file_id < 1) $response['errors'][] = "file_id";
		$category_id = (int)$category_id;
		if ($category_id < 0) $response['errors'][] = "category_id";
		if (count(v($response['errors']))) return $response;
		global $db;
		if ($category_id == 0) {
			$new_path = $this->config['gallery_path'];
		} else {
			$r = $db->prepare("SELECT ".$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
			." FROM {$this->db_prefix}gallery_categories category"
			//path
			." LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt"
			." WHERE category.id=?");
			$r->execute(array($category_id));
			$data = $r->fetch();
			if (empty($data['path'])) {
				$r = $db->prepare("SELECT count(*) FROM {$this->db_prefix}gallery_categories WHERE id=?");
				$r->execute(array($category_id));
				if ($r->fetchColumn() != 1) {
					$response['errors'][] = "category_not_found";
				}
				else $response['errors'][] = "database";
				return $response;
			}
			else $this->Sort_path($data['path']);
			$new_path = $this->config['gallery_path'].$data['path'].'/';
		}
		$r = $db->prepare("SELECT filename,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true, 'separator'=>'/'))." path"
		." FROM {$this->db_prefix}gallery_files f
		LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id = category_id
		LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
		WHERE f.id=? GROUP BY f.id, f.filename LIMIT 1");
		$success = $r->execute(array($file_id));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() != 1) {
			$response['errors'][] = "file_not_found";
			return $response;
		}
		$data = $r->fetch();
		$filename = $data['filename'];
		$source_path = $this->config['gallery_path'];
		if (!empty($data['path'])) {
			$this->Sort_path($data['path']);
			$source_path .= $data['path'].'/';
		}
		if (is_file($new_path.$filename)) {
			$response['errors'][] = "file_exists_in_target";
			return $response;
		}
		// Move original image
		if (!copy($source_path.$filename, $new_path.$filename)) {
			$response['errors'][] = "move_image";
			return $response;
		}
		$this->chmod($new_path.$filename);
		// List of file paths to unlink when operation completed successfully
		$unlink_after_success = array();
		$unlink_after_success[] = $source_path.$filename;
		// List of file paths to unlink when operation was not successful
		$created = array();
		$created[] = $new_path.$filename;
		// Move all generated thumbnails
		foreach (glob($source_path.'thumb-*') as $thumbnail_path) {
			if (is_file($thumbnail_path.'/'.$filename)) {
				$base_thumb_path = basename($thumbnail_path);
				if (!is_dir($new_path.$base_thumb_path)) {
					$this->mkdir($new_path.$base_thumb_path);
				}
				$copied_path = $new_path.$base_thumb_path.'/'.$filename;
				if (copy($thumbnail_path.'/'.$filename, $copied_path)) {
					$this->chmod($copied_path);
					$created[] = $copied_path;
				}
				$unlink_after_success[] = $thumbnail_path.'/'.$filename;
			}
		}
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_files SET category_id=? WHERE id=?");
		$success = $r->execute(array($category_id, $file_id));
		if (!$success) {
			foreach ($created as $fpath) @unlink($fpath);
			$response['errors'][] = "move_image";
			return $response;
		}
		//need to update image links used in the content!!!
		foreach ($unlink_after_success as $fpath) @unlink($fpath);
		return array("success"=>true);
	}
	
	/**
	* Method used to rename given file to given new name. Changes  are made on appropriate DB tables and on the file on the filesystem.
	* @param int $id given file id to to be renamed.
	* @param string $name given new file name to be renamed.
	* @param bool $extension_specified given indication if given file extension is specified.
	* @return mixed array with keys "success" and "name" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path()
	*/
	public function Rename_file($id, $name, $extension_specified=true) {
		$id = (int)$id;
		if ($id < 0) $r['errors'][] = "id";
		if ($extension_specified) $name = substr($name, 0, strrpos($name, '.'));
		$name = Sanitize('route', $name);
		if (!preg_match('/^'.$this->patterns['filename_without_extension'].'$/ui', $name))
			$r['errors'][] = "filename";
		if (count(v($r['errors']))) return $r;
		global $db;
		$r = $db->prepare("SELECT filename,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path"
		." FROM {$this->db_prefix}gallery_files f
		LEFT JOIN {$this->db_prefix}gallery_categories c ON c.id=f.category_id
		LEFT JOIN {$this->db_prefix}gallery_categories path ON c.lft BETWEEN path.lft and path.rgt
		WHERE f.id=? GROUP BY f.id,f.filename LIMIT 1");
		$success = $r->execute(array($id));
		if (!$success) {
			$r['errors'][] = "database";
			return $r;
		}
		if ($r->rowCount() != 1) {
			$r['errors'][] = "file_not_found";
			return $r;
		}
		$data = $r->fetch();
		$this->Sort_path($data['path']);
		unset($r);
		$old_name = $data['filename'];
		$extension = substr($data['filename'], strrpos($data['filename'], '.')+1);
		$name .= '.'.$extension;
		if ($name == $old_name) {
			$r['errors'][] = "no_changes";
			return $r;
		}
		if (isset($data['path'])) $data['path'] .= '/';
		$old_full_path = $this->config['gallery_path'].$data['path'].$old_name;
		$full_path = $this->config['gallery_path'].$data['path'].$name;
		//check if file with new name already exists
		if (is_file($full_path)) {
			$r['errors'][] = "file_already_exists";
			return $r;
		}
		//debug: echo $old_full_path."\n\n"; echo $full_path; return;
		if (!@rename($old_full_path, $full_path)) {
			$r['errors'][] = "rename_file";
			return $r;
		}
		$now = time();
		$r = $db->prepare("UPDATE {$this->db_prefix}gallery_files SET filename=?, date_modified=? WHERE id=?");
		$success = $r->execute(array($name, $now, $id));
		if (!$success) {
			@rename($full_path, $old_full_path);
			$r['errors'][] = "database";
			return $r;
		}
		if ($r->rowCount() != 1) {
			@rename($full_path, $old_full_path);
			$r['errors'][] = "database";
			return $r;
		}
		return array('success'=>true,'name'=>$name);
	}
	// thumbnails
	
	/**
	* Method used to retrieve supported thumbnails types by the gallery. Method simply returns this instance field "thumbnail_types", if required, sets this field. 
	* @param mixed $refresh given indication if overwrite currently existting set of thumbnails.
	* @return mixed array containing thumbnails types.
	* @see PC_gallery::thumbnail_types
	*/
	public function Get_thumbnail_types($refresh=0) {
		global $db;
		if (!isset($this->thumbnail_types) || $refresh) {
			unset($this->thumbnail_types);
			$r = $db->query("SELECT * FROM {$this->db_prefix}gallery_thumbnail_types");
			while ($data = $r->fetch()) {
				$this->thumbnail_types[$data['thumbnail_type']] = $data;
			}
		}
		return $this->thumbnail_types;
	}
	
	public function Get_thumbnail_type($type, $refresh=0) {
		$this->Get_thumbnail_types($refresh);
		if (isset($this->thumbnail_types) and isset($this->thumbnail_types[$type])) {
			return $this->thumbnail_types[$type];
		}
		return false;
		
	}
	
	/**
	* Method used to create new thumbnail type by given name and properties. Method saves new thumbnail type to the appropriate table.
	* @param string $thumbnail_type given name for new thunbnail type.
	* @param int $max_w given maximum width for new thumbnail type.
	* @param int $max_h given maximum height for new thumbnail type.
	* @param int $quality given quality for  new thumbnail type.
	* @return mixed array with keys "success" and "type" on success, or array with key "errors" otherwise.
	*/
	public function Create_thumbnail_type($thumbnail_type, $max_w, $max_h, $quality=76, $use_adaptive_resize) {
		$use_adaptive_resize = (int)$use_adaptive_resize;
		$thumbnail_type = strtolower($thumbnail_type);
		if (!preg_match('/^'.$this->patterns['thumbnail_type'].'$/', $thumbnail_type))
			$response['errors'][] = "thumbnail_type"; 
		if ($max_w < 1) $response['errors'][] = "max_w";
		if ($max_h < 1) $response['errors'][] = "max_h"; 
		if ($quality < 10 || $quality > 100) $response['errors'][] = "quality";
		if (count(v($response['errors'])) != 0) return $response;
		global $db;
		$r = $db->prepare("SELECT count(*) FROM {$this->db_prefix}gallery_thumbnail_types WHERE thumbnail_type=?");
		$r->execute(array($thumbnail_type));
		if ($r->fetchColumn() != 0) {
			$response['errors'][] = "thumbnail_type_exists";
			return $response;
		}
		$r = $db->prepare("INSERT INTO {$this->db_prefix}gallery_thumbnail_types VALUES(?,?,?,?,?)");
		$success = $r->execute(array($thumbnail_type, $max_w, $max_h, $quality, $use_adaptive_resize));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		return array("success"=>true, 'type'=>$thumbnail_type);
	}
	
	/**
	* Method used to delete existing thumbnail type by given name. Method deletes thumbnail from the appropriate table.
	* @param string $type given name to delete thumbnail type by.
	* @return mixed array with key "success" on success, or array with key "errors" otherwise.
	*/
	public function Delete_thumbnail_type($type) {
		if (!preg_match('/^'.$this->patterns['thumbnail_type'].'$/', $type)) {
			$response['errors'][] = "thumbnail_type"; 
			return $response;
		}
		if (in_array($type, array('thumbnail','small','large'))) {
			$response['errors'][] = "thumbnail_type"; 
			return $response;
		}
		global $db;
		# Select all album pathes and delete thumbnail type folders in them...
		$r = $db->prepare("DELETE FROM {$this->db_prefix}gallery_thumbnail_types WHERE thumbnail_type=?");
		$success = $r->execute(array($type));
		if (!$success) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() != 1) {
			$response['errors'][] = "thumbnail_type_not_found";
			return $response;
		}
		return array('success'=>true);
	}
	
	/**
	* Method used to create new thumbnail by given file name, thumbnail type and other properties. Method saves new thumbnail files to appropriate
	* location.
	* @param int $file_id given file id to crop thumbnail from.
	* @param string $thumbnail_type given type for using when cropping.
	* @param int $x_start given coordinate on x axis to start crop
	* @param int $y_start given coordinate on y axis to start crop 
	* @param int $width given width of the new thumbnail
	* @param int $height given height of the new thumbnail
	* @return mixed array with key "success" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Get_thumbnail_types()
	* @see PhpThumbFactory
	*/
	public function Crop_thumbnail($file_id, $thumbnail_type, $x_start, $y_start, $width, $height) {
		$this->debug = true;
		$this->set_instant_debug_to_file($this->cfg['path']['logs'] . 'gallery/pc_gallery_crop.html', false, 5);
		$this->debug("Crop_thumbnail($file_id, $thumbnail_type, $x_start, $y_start, $width, $height)");
		
		if (!preg_match('/^'.$this->patterns['thumbnail_type'].'$/', $thumbnail_type))
			$response['errors'][] = "thumbnail_type";
		$x_start = (int)$x_start;
		if ($x_start < 0) $response['errors'][] = "x_start";
		$y_start = (int)$y_start;
		if ($y_start < 0) $response['errors'][] = "y_start";
		$width = (int)$width;
		if ($width < 5) $response['errors'][] = "width";
		$height = (int)$height;
		if ($width < 5) $response['errors'][] = "height";
		$thumbnail_types = $this->Get_thumbnail_types();
		if (!isset($thumbnail_types[$thumbnail_type])) {
			$response['errors'][] = "thumbnail_not_found";
			return $response;
		}
		
		if (!is_array($file_id)) {
			$file_id = (int)$file_id;
			if ($file_id < 1)
				$response['errors'][] = "file_id";

			if (count(v($response['errors'])) != 0) return $response;
			global $db;
			$r = $db->prepare("SELECT filename, extension,"
			.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true, 'separator'=>'/'))." path"
			." FROM {$this->db_prefix}gallery_files f
			LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id=category_id
			LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
			WHERE f.id=? GROUP BY f.id,f.filename,f.extension LIMIT 1");
			$success = $r->execute(array($file_id));
			if (!$success) {
				$response['errors'][] = "database";
				return $response;
			}
			$data = $r->fetch();
			$this->Sort_path($data['path']);
			if (empty($data['filename'])) {
				$response['errors'][] = "file_not_found";
				return $response;
			}
			if ($this->filetypes[$data['extension']] != 'image') {
				$response['errors'][] = "file_is_not_an_image";
				return $response;
			}
		}
		else {
			$data = $file_id;
			//print_pre($data);
			//exit;
		}
		
		
		$full_path = $this->config['gallery_path'].$data['path'];
		if (isset($data['path'])) $full_path .= '/';
		//crop image
		try {
			$set = ini_set('memory_limit', '512M');
			$this->last_thumb = $thumb = PhpThumbFactory::create($full_path.$data['filename'], array('jpegQuality'=>$thumbnail_types[$thumbnail_type]['thumbnail_quality']));
			$currentDimensions = $thumb->getCurrentDimensions();
			//echo 'Current dimensions: '; print_r($currentDimensions);
			//echo "\n\n".'Crop from x'.$x_start.' y'. $y_start. ' '.$width.'x'.$height;
			$thumb->crop($x_start, $y_start, $width, $height);
			$thumb->resize($thumbnail_types[$thumbnail_type]['thumbnail_max_w'], $thumbnail_types[$thumbnail_type]['thumbnail_max_h']);
			if (!is_dir($full_path.'thumb-'.$thumbnail_type)) {
				if (!$this->mkdir($full_path.'thumb-'.$thumbnail_type)) {
					$response['errors'][] = "create_thumbnail_dir";
					return $response;
				}
			}
			$thumb_path = $full_path.'thumb-'.$thumbnail_type.'/'.$data['filename'];
			$thumb->save($thumb_path);
			//create thumbnail same as small
			if ($thumbnail_type == 'small') {
				$small_thumb = PhpThumbFactory::create($full_path.$data['filename'], array('jpegQuality'=>$thumbnail_types['thumbnail']['thumbnail_quality']));
				$small_thumb->crop($x_start, $y_start, $width, $height);
				$small_thumb->resize($thumbnail_types['thumbnail']['thumbnail_max_w'], $thumbnail_types['thumbnail']['thumbnail_max_h']);
				$stop_save = false;
				if (!is_dir($full_path.'thumb-thumbnail')) {
					if (!$this->mkdir($full_path.'thumb-thumbnail')) {
						$stop_save = true;
					}
				}
				if (!$stop_save) {
					$small_thumb_path = $full_path.'thumb-thumbnail/'.$data['filename'];
					if ($small_thumb->save($small_thumb_path)) {
						$crop_data = $x_start.'|'.$y_start.'|'.$width.'|'.$height;
						file_put_contents($small_thumb_path.'.txt', $crop_data);
					}
				}
			}
			//save crop data
			$crop_data = $x_start.'|'.$y_start.'|'.$width.'|'.$height;
			file_put_contents($thumb_path.'.txt', $crop_data);
			return array('success'=>true);
		}
		catch (Exception $e) {
			$response['errors'][] = "crop_thumbnail";
			return $response;
		}
	}
	
	/**
	* Method used to get thumbnail by given file id and thumbnail type. Not used.
	@todo implement or remove this method.
	*/
	public function Get_thumbnail($image_id, $thumbnail_type) {}
	
	/**
	* Method used to change properties of existing thumbnail by given thumbnail. Method changes only data on appropriate DB tables.
	* @param string $type given thumbnail type to change the details for.
	* @param string $new_type given new thumbnail type name to change.
	* @param int $max_w given maximum new maximum width of this thumbnail type.
	* @param int $max_h given maximum new maximum height of this thumbnail type.
	* @param int $quality given new quality of this thumbnail type.
	* @return mixed array with keys "success" and "type" on success, or array with key "errors" otherwise.
	*/
	public function Edit_thumbnail_type($type, $new_type='', $max_w='', $max_h='', $quality='', $use_adaptive_resize=null) {
		$type = strtolower($type);
		if (!preg_match('/^'.$this->patterns['thumbnail_type'].'$/', $type))
			$response['errors'][] = "thumbnail_type";
		if (isset($new_type)) {
			if (in_array($type, array('thumbnail','small','large')))
				$response['errors'][] = "cannot_change_default_type";
			if (!preg_match('/^'.$this->patterns['thumbnail_type'].'$/', $new_type))
				$response['errors'][] = "thumbnail_type";
		}
		if (isset($max_w)) {
			$max_w = (int)$max_w;
			if ($max_w < 5 || $max_w > 2000)
				$response['errors'][] = "max_width";
		}
		if (isset($max_h)) {
			$max_h = (int)$max_h;
			if ($max_h < 5 || $max_h > 2000)
				$response['errors'][] = "max_height";
		}
		if (isset($quality)) {
			$quality = (int)$quality;
			if ($quality < 5 || $quality > 100)
				$response['errors'][] = "quality";
		}
		if (isset($use_adaptive_resize)) {
			$use_adaptive_resize = (int)$use_adaptive_resize;
			if (!in_array($use_adaptive_resize, array(0, 1, 2)))
				$response['errors'][] = "use_adaptive_resize";
		}
		if (empty($max_w) && empty($max_h) && empty($quality) && $type == $new_type && is_null($use_adaptive_resize)) {
			$response['errors'][] = "no_changes";
		}
		if (count(v($response['errors']))) return $response;
		global $db;
		$r = $db->prepare("SELECT count(*) FROM {$this->db_prefix}gallery_thumbnail_types WHERE thumbnail_type=?");
		$s = $r->execute(array($type));
		if (!$s) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->fetchColumn() != 1) {
			$response['errors'][] = "thumbnail_not_found";
			return $response;
		}
		$query = "UPDATE {$this->db_prefix}gallery_thumbnail_types SET ";
		$set = '';
		if (isset($new_type))
			$set .= "thumbnail_type='$new_type', ";
		if (isset($max_w))
			$set .= "thumbnail_max_w=$max_w, ";
		if (isset($max_h))
			$set .= "thumbnail_max_h=$max_h, ";
		if (isset($quality))
			$set .= "thumbnail_quality=$quality, ";
		if (!is_null($use_adaptive_resize))
			$set .= "use_adaptive_resize=$use_adaptive_resize, ";
		$set = substr($set, 0, -2);
		$query .= $set;
		/*if (!empty($where)) {
			$where = substr($where, 0, -2);
			$query .= ' WHERE '.$where;
		}*/
		$query .= " WHERE thumbnail_type='$type'";
		$r = $db->query($query);
		if (!$r) {
			$response['errors'][] = "database";
			return $response;
		}
		if ($r->rowCount() != 1) {
			$response['errors'][] = "database";
			return $response;
		}
		return array("success"=>true, 'type'=>$type);
	}
	
	/**
	* Method used to get categories and files in these categories which has indication as "deleted" in the appropriate DB tables.
	* @return mixed array with keys "success" and "files" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Sort_path().
	*/
	public function Get_trash() {
		global $db;
		$r = $db->query("SELECT c.id, c.category, c.date_trashed, count(file.id) files,
		count(distinct categories.id) categories, sum(file.size) as size
		FROM {$this->db_prefix}gallery_categories c
		LEFT JOIN {$this->db_prefix}gallery_categories categories ON categories.lft between c.lft and c.rgt
		LEFT JOIN {$this->db_prefix}gallery_files file ON category_id = categories.id
		WHERE c.date_trashed != 0
		GROUP BY c.id,c.category,c.date_trashed");
		if (!$r) {
			$response['errors'][] = "database";
			return $response;
		}
		while ($data = $r->fetch()) {
			$trash[] = $data;
			continue;
			$trash[] = array(
				'type'=> 'category',
				'category_id'=> $data['id'],
				'title'=> $data['category'],
				'date_trashed'=> date('Y.m.d H:i', $data['date_trashed']),
				'categories'=> $data['categories'],
				'files'=> $data['files'],
				'size'=> ($data['size']<307200?number_format($data['size'] /1024).' KB':number_format($data['size'] /1024/1024, 2).' MB')
			);
		}
		$r = $db->query("SELECT f.id, filename, extension, f.date_trashed, size,"
		.$this->sql_parser->group_concat($this->sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true, 'separator'=>'/'))." path"
		." FROM {$this->db_prefix}gallery_files f
		LEFT JOIN {$this->db_prefix}gallery_categories category ON category.id = f.category_id
		LEFT JOIN {$this->db_prefix}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
		WHERE f.date_trashed != 0 group by f.id,f.filename,f.extension,f.date_trashed,f.size");
		if (!$r) {
			$response['errors'][] = "database";
			return $response;
		}
		while ($data = $r->fetch()) {
			$this->Sort_path($data['path']);
			$trash[] = array(
				'type'=> 'file',
				'file_id'=> $data['id'],
				'title'=> $data['filename'],
				'date_trashed'=> date('Y.m.d H:i', $data['date_trashed']),
				'size'=> ($data['size']<307200?number_format($data['size'] /1024).' KB':number_format($data['size'] /1024/1024, 2).' MB')
			);
		}
		return array("success"=>true,"files"=>$trash);
	}
	
	/**
	* Method used to delete categories and files from the filesystem which has indication as "deleted" in appropriate DB tables.
	* @return mixed array with key "success" on success, or array with key "errors" otherwise.
	* @see PC_gallery::Delete_category().
	* @see PC_gallery::Delete_file().
	*/
	public function Empty_trash() {
		global $db;
		$r = $db->query("SELECT id FROM {$this->db_prefix}gallery_categories WHERE date_trashed>0");
		if (!$r) {
			$response['errors'][] = "database";
			return $response;
		}
		while ($data = $r->fetch()) {
			$this->Delete_category($data['id']);
		}
		$r = $db->query("SELECT id FROM {$this->db_prefix}gallery_files WHERE date_trashed>0");
		while ($data = $r->fetch()) {
			$this->Delete_file($data['id']);
		}
		return array("success"=>true);
	}
	
	/**
	* Method used to extract files names from HTML markup in given text.
	* @param string $text given text to look for <img> tags.
	* @param mixed $return_thumbnail_types given thumbnail types to be returned; if variable omitted - all types returned.
	* @return mixed array with found files names in the given text.
	* @see PC_gallery::Parse_file_request().
	* @see PC_gallery::Replace_thumbnail_types().
	*/
	public function Extract_files_from_text($text, $return_thumbnail_types=null) {
		/*if (is_array($return_thumbnail_types)) if (count($return_thumbnail_types)) {
			$this->Replace_thumbnail_types($text, $return_thumbnail_types);
		}*/
		//echo htmlspecialchars('#'.$this->patterns['file_link'].'#i');
		//preg_match_all('#'.$this->patterns['file_link'].'#i', $text, $m);
		preg_match_all('#<img[^>]+src="([^"]+)"[^>]*>#i', $text, $m);
		if (!is_null($return_thumbnail_types)) {
			if (!is_array($return_thumbnail_types)) $return_thumbnail_types = array((string)$return_thumbnail_types);
			if (count($return_thumbnail_types)) {
				//$this->Replace_thumbnail_types($text, $return_thumbnail_types);
				$files = array();
				foreach ($m[1] as $src) {
					$r = $this->Parse_file_request($src);
					if (isset($r['errors'])) if (count($r['errors'])) continue;
					$file = array();
					foreach ($return_thumbnail_types as $type) {
						$clean_type = $type;
						if (!empty($type)) {
							$type .= '/';
						}
						$file[$clean_type] = $r['category_path'] . '/' . $type . $r['filename'];
					}
					$files[] = $file;
				}
				return $files;
			}
		}
		return $m[1];
	}
	public function Get_image_thumbnail_type($src, $type=null) {
		$r = $this->Parse_file_request($src);
		print_pre($r);
		if (isset($r['errors'])) if (count($r['errors'])) return $src;
		return (!empty($r['category_path'])?$r['category_path'].'/':'').(is_null($type)?'':$type.'/').$r['filename'];
	}
	public function Get_image_thumbnail($src, $type=null) {
		$r = $this->Parse_file_request($src);
		if (isset($r['errors'])) if (count($r['errors'])) return $src;
		return (!empty($r['category_path'])?$r['category_path'].'/':'').(is_null($type)?'':$type.'/').$r['filename'];
	}
	
	/**
	* Method used to replace in the given text to the given thumbnail type.
	* @todo: implement or remove this method.
	*/
	public function Replace_thumbnail_types(&$text, $type) {
		//preg_replace('#(src|href)="([^"]+)"[^>]*>#i', $text, $m);
	}
}
?>
