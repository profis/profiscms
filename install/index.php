<?php
error_reporting(0);



require_once '../core/path_constants.php';

define('PC_INSTALL_SEQUENCE', true);
define('PC_INSTALL_VERSION', '4.4.9');
define('PC_CONFIG_FILE', CMS_ROOT . 'config.php');
define('PC_INSTALL_FOLDER', 'install/');
define('PC_INSTALL_DIR', CMS_ROOT . PC_INSTALL_FOLDER);
define('PC_DEFAULT_ADMIN_USER', 'admin');
define('PC_DEFAULT_ADMIN_PASSWORD', 'admin');



$ln = '';
session_start();
if (isset($_GET['ln'])) {
	$_SESSION['ln'] = $_GET['ln'];
}
if (isset($_SESSION['ln'])) {
	$ln = $_SESSION['ln'];
}

if (!in_array($ln, array('en', 'ru_', 'lt'))) {
	$ln = 'en';
}

global $t;
include 'ln/' . 'install_'.$ln.'.php';

$t = $titles;

require_once 'PC_installer.php';
$installer = new PC_installer();
$is_installed = $installer->is_installed();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title><?php echo str_replace('{version}', PC_INSTALL_VERSION, (!$is_installed)?$t['meta_title']:$t['meta_title_requirements'])?></title>
    <link href="css/bootstrap.css" media="screen" rel="Stylesheet" type="text/css" />
	<link href="css/install.css" media="screen" rel="Stylesheet" type="text/css" />
</head>
<body>
<div class="container">

	<div class="masthead" style="margin-top: 20px;">
		<ul class="nav nav-pills pull-right" style="margin-top: 5px;">
			<li class="<?php echo ($ln=='en'?'active':'') ?>"><a href="?ln=en">English</a></li>
<!--			<li class="<?php echo ($ln=='ru'?'active':'') ?>"><a href="?ln=ru">Русский</a></li>-->
			<li class="<?php echo ($ln=='lt'?'active':'') ?>"><a href="?ln=lt">Lietuvių</a></li>
		</ul>
	</div>

	<h1><?php echo str_replace('{version}', PC_INSTALL_VERSION, (!$is_installed)?$t['title']:$t['title_requirements']) ?></h1>
	
<?php

if (isset($_POST['install']) and $is_installed) {
	header('Location: ../'); exit();
}
elseif (isset($_POST['install']) && !isset($_POST['commit'])) {
    require_once 'install_form.php';
}
else if (isset($_POST['install']) && isset($_POST['commit']) && isset($_POST['config'])) {
    $config = $_POST['config'];
    require_once 'install.php';
}
else {
    require_once 'requirements.php';
}
?>
		

 </div> <!-- /container -->

      

</body>
</html>