<?php
require('admin.php');
?>
<html>
<head>
<title>Gallery debug tool</title>
<style type="text/css">
	#container {width:800px;margin:0 auto;}
</style>
</head>
<body>
<div id="container">
	<a href="?action=debug_tree">Debug tree structure</a> &nbsp;|&nbsp; <a href="?action=find_missing">Find missing files</a><br /><br />
	<?php
	switch(v($_GET['action'])) {
		case 'debug_tree':
			$gallery->Debug_tree();
			break;
		case 'find_missing':
			$r = $db->query("SELECT
			file.*,
			count(files_in_use.file_id) in_use,"
			.$sql_parser->group_concat($sql_parser->concat_ws('░', 'path.lft', 'path.directory'), array('distinct'=>true,'separator'=>'/'))." path,"
			.$sql_parser->group_concat($sql_parser->concat_ws('░', 'path.lft', 'path.category'), array('distinct'=>true,'separator'=>'/'))." category,"
			."sum(path.date_trashed) category_trashed
			FROM {$cfg['db']['prefix']}gallery_files file
			LEFT JOIN {$cfg['db']['prefix']}gallery_files_in_use files_in_use ON files_in_use.file_id=file.id
			LEFT JOIN {$cfg['db']['prefix']}gallery_categories category ON category.id = file.category_id
			LEFT JOIN {$cfg['db']['prefix']}gallery_categories path ON category.lft BETWEEN path.lft and path.rgt
			GROUP BY file.id,file.filename,file.extension, file.category_id,file.size,file.date_added, file.date_modified,file.date_trashed");
			if (!$r) {
				echo 'Database error. Couldn\'t read files list.';
				break;
			}
			$files = array();
			while ($file = $r->fetch()) {
				$gallery->Sort_path($file['path']);
				$gallery->Sort_path($file['category']);
				$file['_path'] = $file['path'].(!empty($file['path'])?'/':'').$file['filename'];
				$file['full_path'] = $cfg['path']['gallery'].$file['_path'];
				$files[] = $file; 
			}
			unset($file);
			//print_pre($files);
			$total_files = count($files);
			$missing_files = 0;
			foreach ($files as &$file) {
				if (!is_file($file['full_path'])) {
					$missing_files++;
					echo $file['_path'],'<br />';
				}
			}
			echo '<hr /><b>'.$missing_files,' files are missing</b>';
			break;
		default:
			?>
			Choose action.
			<?php
	}
	?>
</div>
</body>
</html>