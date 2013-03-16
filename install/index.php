<?php
error_reporting(0);

require_once '../core/path_constants.php';

define('PC_INSTALL_SEQUENCE', true);
define('PC_CONFIG_FILE', CMS_ROOT . 'config.php');
define('PC_INSTALL_DIR', CMS_ROOT . 'install/');
define('PC_DEFAULT_ADMIN_USER', 'admin');
define('PC_DEFAULT_ADMIN_PASSWORD', 'admin');

require_once 'PC_installer.php';
$installer = new PC_installer();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title>Profis CMS - Install routine</title>
    <link href="css/bootstrap.css" media="screen" rel="Stylesheet" type="text/css" />
	<link href="css/install.css" media="screen" rel="Stylesheet" type="text/css" />
</head>
<body>
<div class="container">

	<div class="masthead">
		<h3 class="muted">Profis CMS installation</h3>
	</div>

<?php
if ($installer->is_installed()) {
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
		
		<footer>
		<p>Powered by <a target="_blank" href="http://www.profiscms.com/">Profis CMS</a></p>
      </footer>	

 </div> <!-- /container -->

      

</body>
</html>