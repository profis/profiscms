<?php
#  ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
# 
#  This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#  
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
require_once 'admin.php';
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Profis CMS <?php echo PC_VERSION; ?></title>
	<base href="<?php echo htmlspecialchars($cfg['url']['base'].$cfg['directories']['admin']); ?>/" />
	<meta http-equiv="X-UA-Compatible" content="IE=7" />
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	<style type="text/css">html,body{height:95%;}#loading-mask {z-index:1000000;position: absolute; top: 0;left: 0;width: 100%;height: 100%;background: #fff url(images/ajax-loader.gif) 49% 49% no-repeat;}</style>
	<!-- Styles -->
	<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="ext/resources/css/debug.css" />
	<!--<link rel="stylesheet" type="text/css" href="ext/resources/css/xtheme-gray.css" />-->
	<link rel="stylesheet" type="text/css" href="css/pack.styles.php" />
</head>
<body>
	<div id="loading-mask"></div>
	<!-- Scripts -->
	<script type="text/javascript" src="ext/adapter/ext/ext-base.js"></script>
	<?php
	//local ip testing instead of false:
	if (isset($_GET['debug'])) {
	?>
<!--		<script type="text/javascript" src="ext/ext-all-debug.js"></script>-->
		<script type="text/javascript" src="ext/ext-all-debug-w-comments.js"></script>
		<script type="text/javascript" src="ext/src/debug.js"></script>
	<?php
	} else {
	?>
		<script type="text/javascript" src="ext/ext-all.js"></script>
	<?php
	}
	?>
	<script type="text/javascript" src="locale/<?php echo $admin_ln; ?>/"></script>
	<?php
	$settings = array();
	$settings['BASE_URL'] = $cfg['url']['base'];
	$settings['ADMIN_DIR'] = $cfg['directories']['admin'];
	$settings['db_flds'] = $cfg['valid_page_fields'];
	$settings['permissions']['admin'] = $auth->Authorize('core', 'admin');
	$settings['permissions']['pages'] = $auth->Authorize('core', 'pages');
	//if ($settings['permissions']['admin']) {
		$settings['SITES'] = array();
		$sites = $site->Get_all();
		foreach ($sites as $k=>$v) {
			$tmp = array();
			if (isset($v['langs']))
				foreach ($v['langs'] as $k1=>$v1) {
					array_unshift($v1, $k1);
					$tmp[] = $v1;
					//$tmp[] = array($k1, $v1);
				}
					
			$settings['SITES'][] = array($k, $v['name'], $v['theme'], $tmp, null, $v['editor_width'], $v['editor_background'], $v['mask'], $v['active']);
		}
		$settings['site'] = $settings['SITES'][0][0];
		$settings['themes'] = get_themes();
	//}
	/*if ($site->Is_loaded()) {
		$settings['site'] = $site->data['id'];
	}
	else {
		$settings['site'] = array_shift($sites);
	}*/
	$settings['admin_languages'] = $admin_languages;
	$settings['ln'] = $settings['SITES'][0][3][0][0];
	$settings['pid'] = 0;
	if (isset($_GET['edit'])) {
		$settings['edit_id'] = intval($_GET['edit']);
	}
	$settings['tree_ln'] = $settings['SITES'][0][3][0][0];
	$settings['user'] = $_SESSION['auth_data']['user'];
	$settings['admin_ln'] = $admin_ln;
	$settings['tree_pages'] = null;
	$settings['keymap'] = null;
	$settings['plugins_panel'] = null;
	$settings['site_select'] = null;
	$settings['ln_select'] = null;
	$settings['ignore_time_zone'] = v($cfg['ignore_time_zone'], false);
	$settings['directories'] = $cfg['directories'];
	$settings['cfg'] =  PC_utils::filterArray(array('map_lat', 'map_lng'), $cfg) ;
	//if ($settings['permissions']['admin']) {
		$settings['plugins'] = $plugins->Get_for_output();
		$settings['controllers'] = $plugins->Get_controllers_for_output();
		$settings['editor'] = $core->Get_editor();
	//}
	$settings['session.gc_maxlifetime'] = ini_get('session.gc_maxlifetime');
	$settings['session.name'] = session_name();
	?>
	<script type="text/javascript">
	Ext.ns('PC');
	PC.global = <?php echo json_encode($settings) ?>;
	PC.version = '<?php echo PC_VERSION; ?>';
	</script>
	<script type="text/javascript" src="tiny_mce/tiny_mce_src.js"></script>
<!--	<script type="text/javascript" src="tinymce/tinymce.js"></script>-->
	<script type="text/javascript" src="js/pack.scripts.php"></script>
</body>
</html>