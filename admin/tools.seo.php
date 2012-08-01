<?php
require('admin.php');
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo 'ProfisCMS '.PC_VERSION; ?> - Links</title>
	<style type="text/css">
		#container {width:800px;margin:0 auto;}
	</style>
</head>
<body>
<div id="container">
	<ul>
		<li><a href="?action=force_images_to_have_wh">Force images to include default width/height attributes</a></li>
	</ul>
	<br /><br />
	<?php
	switch(v($_GET['action'])) {
		case 'force_images_to_have_wh':
			if (v($_GET['confirm']) == 1) {
				$r = $db->query("SELECT * FROM {$cfg['db']['prefix']}content");
				if (!$r) {
					echo 'Error occurred (E1)<br />';
					break;
				}
				while ($d = $r->fetch()) {
					
				}
			}
			else {
				?>
				Are you sure you want to do this?<br /> (<b>NOTE!</b> You should backup your database first.)<br />
				<a href="?action=force_images_to_have_wh&confirm=1"><b>I already have an backup, lets do this!</b></a>
				<?php
			}
			break;
		default:
			?>
			Choose an action above.
			<?php
	}
	?>
</div>
</body>
</html>