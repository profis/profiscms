<?php
	error_reporting(E_ALL); //ensure PHP won't output any error data and won't destroy JSON structure
	$cfg['core']['no_login_form'] = true; //don't output login form if there's no active session
	require_once('admin.php'); //ensure the user is authorized, otherwise stop executing this script
	
	if(isset($_GET["export"])) {
		@header("Content-Type: json/dump");
		@header("Content-Disposition: attachment; filename=vars-dump.json");
		
		$r = $core->db->prepare("SELECT * FROM pc_variables");
		$r->execute();
		echo json_encode($r->fetchAll());
		die();
	}

	@header("Content-Type: text/html; charset=utf-8");
	
	if(isset($_POST["import"]) && isset($_FILES["file"])) {
		
		if( is_uploaded_file($_FILES["file"]["tmp_name"]) ) {
			$filename = $cfg["path"]["gallery"] . uniqid("vd-", true);
			if( move_uploaded_file($_FILES["file"]["tmp_name"], $filename) ) {
				$arr = json_decode(file_get_contents($filename), true);
				foreach($arr as $vinfo) {
					$r = $core->db->prepare("SELECT value FROM pc_variables WHERE controller=? AND ln=? AND site=? and vkey=?");
					$r->execute($idarr = Array($vinfo["controller"], $vinfo["ln"], $vinfo["site"], $vinfo["vkey"]));
					if( ($curval = $r->fetchColumn()) != "" || $vinfo["value"] == "" ) {
						if( $curval != $vinfo["value"] )
							echo "<li>" . print_r($idarr, true) . " - $vinfo[value] -&gt; $curval</li>";
					}
					else {
						echo "<li>" . print_r($idarr, true) . " - IMPORT</li>";
						$r = $core->db->prepare("INSERT INTO pc_variables (controller, ln, site, vkey, value) VALUES (?, ?, ?, ?, ?)");
						$r->execute(Array($vinfo["controller"], $vinfo["ln"], $vinfo["site"], $vinfo["vkey"], $vinfo["value"]));
					}
				}
				@unlink($filename);
			}
		}
	}
?>
<hr />
<h1>Export</h1>
<a href="<?php echo basename(__FILE__); ?>?export=1">Download vars in file</a><br />
<hr />
<h1>Import</h1>
<form action="<?php echo basename(__FILE__); ?>" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="import" value="1" />
	<input type="file" name="file" />
	<input type="submit" value="Import" />
</form>