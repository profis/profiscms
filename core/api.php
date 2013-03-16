<?php
require_once 'path_constants.php';

require("base.php");

$logger = new PC_debug();
$logger->debug = true;
$logger->set_instant_debug_to_file($cfg['path']['logs'] . 'api.html');

error_reporting(E_ALL);

if (isset($site->route[1]) and strlen($site->route[1]) == 2) {
	$site->Identify();	
	if(isset($site->data['languages'][$site->route[1]])) {
		$site->Set_language($site->route[1]);
		$logger->debug('ln first route: Site language now is ' . $site->ln, 1);
		$routes->Shift();
	}
}

if ($routes->Get(1) == 'admin') {
	$ln = v($_GET['ln']);
	if (!empty($ln)) {
		$logger->debug('Setting language to ' . $ln, 1);
		$site->Identify();
		$site->Set_language($ln);
		
		$logger->debug('Site language now is ' . $site->ln, 1);
	}
	$logger->debug('Admin api');
	//administrator API
	$routes->Shift(2);
	require(CMS_ROOT . "admin/admin.php");
	
	require_once CMS_ROOT . 'admin/Admin_api.php';
	$admin_api = new Admin_api();
	
	$api_name = v($routes->Get(1));
	
	if (method_exists($admin_api, $api_name)) {
		$answer = $admin_api->$api_name(v($routes->Get(2)), v($routes->Get(3)), v($routes->Get(4)), v($routes->Get(5)), v($routes->Get(6)));
		if (is_array($answer)) {
			$answer = json_encode($answer);
		}
		echo $answer;
		exit;
	}
	
	switch (v($routes->Get(1))) {
		case 'phpinfo':
			phpinfo();
			break;
		case 'tree':
			$tree = $core->Get_object('PC_database_tree');
			switch (v($routes->Get(2))) {
				case 'debug':
					$params = array(
						'cols'=> array(
							'parent'=> v($routes->Get(4)),
							'name'=> v($routes->Get(5))
						)
					);
					$tree->Debug(v($routes->Get(3)), $params);
					break;
				case 'recalculate':
					$params = array(
						'cols'=> array(
							'parent'=> v($routes->Get(4))
						)
					);
					$tree->Recalculate(v($routes->Get(3)), $params);
					break;
			}
			break;
		case 'plugin':
			$pluginName = str_replace('-', '_', $routes->Get(2));
			if (strpos($pluginName, 'pc_') === 0) {
				$pluginName_up = strtoupper(substr($pluginName, 0, 2)) . substr($pluginName, 2);
			}
			else {
				$pluginName_up = strtoupper(substr($pluginName, 0, 1)) . substr($pluginName, 1);
			}
			
			$logger->debug('Plugin ' . $pluginName, 1);
			if (!empty($pluginName)) {
				if ($plugins->Is_active($pluginName)) {
					$apiPath = $core->Get_path('plugins', 'PC_api_admin.php', $pluginName);
					$routes->Shift(2);
					if (is_file($apiPath)) {
						try {
							require($apiPath);
						}
						catch (exception $e) {
							echo 'Plugin API thrown an uncaught exception: '.$e->getMessage();
						}
					}
					else {
						$plugin_api_path = $cfg['path']['plugins'] . $pluginName . '/admin_api/';
						$plugin_api_path = $core->Get_path('plugins', '', $pluginName) . 'admin_api/';
						
						$class_name = $pluginName_up . '_' . v($routes->Get(1)) . '_admin_api';
						$file_name = $plugin_api_path . "$class_name.php";

						$logger->debug('filename: ' . $file_name, 2);
						
						if (@file_exists($file_name)) {
							$logger->debug('File exists', 3);
							
							require_once $cfg['path']['admin'] . 'classes/Page_manager.php';
							$page_manager = new Page_manager();
							$page_manager->set_debug(true);
							
							require_once $cfg['path']['admin'] . 'classes/PC_plugin_admin_api.php';
							require_once $cfg['path']['admin'] . 'classes/PC_plugin_crud_admin_api.php';
							
							$plugin_common_api_class_path = $plugin_api_path . $pluginName_up . '_admin_api.php';
							$logger->debug('common filename: ' . $plugin_common_api_class_path, 2);
							if (@file_exists($plugin_common_api_class_path)) {
								$logger->debug('File exists', 3);
								@require_once $plugin_common_api_class_path;
							}
							
							require_once($file_name);
							/* @var $api PC_shop_admin_api */ 
							$log_file = $cfg['path']['logs'] . $pluginName.'/admin_'.v($routes->Get(1)) . '_' . v($routes->Get(2)) .'_api.html';
							$logger->debug('Log file: ' . $log_file, 3);
							$api = new $class_name($page_manager);
							$api->debug = true;
							$api->set_instant_debug_to_file($log_file);
							$proccessed = $api->process(v($routes->Get(3)), v($routes->Get(4)), v($routes->Get(5)));

							$api->set_debug_offset(0);
							$api->debug('Page manager debug:');
							$api->debug($page_manager->get_debug_string(), 1);

							$api->debug('api output:');
							$api->debug($api->get_output(), 1);
							
							
							//$api->file_put_debug($log_file);

							if ($proccessed) {
								$out = $api->get_output();
								$logger->debug('Api output:', 5);
								$logger->debug($out);
								//$out['log_file'] = $log_file;
								echo json_encode($out);
								exit;
							}
							else {
								$logger->debug(':( Api could not be processed', 4);
								$out = array();
								$out['success'] = false;
								$out['error'] = 'Invalid action';
								echo json_encode($out);
							}
						}
						echo '<hr /><b>'.$pluginName.'</b> plugin doesn\'t have API';
					}
				}
				else echo 'Plugin is not active';
			}
			else {
				echo 'Plugin name was not filled';
			}
			break;
		case 'clear_cache':
			$cache->flush();
			break;
		default: echo 'Please select API action that you want to execute:<ul><li><a href="'.htmlspecialchars($cfg['url']['base']).'admin/api/phpinfo/">PHPinfo</a></li></ul>';
	}
}
else {
	//public API
	$routes->Shift(1);
	switch($routes->Get(1)) {
		case 'keepalive':
			break;
		case 'texts':
			$site->Identify();
			$ln = v($routes->Get(2));
			if (!empty($ln)) {
				$site->Set_language($ln);
			}
			$list = $page->Get_page();
			?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title>List of all texts in the pages</title>
				<base href="<?php echo htmlspecialchars($cfg['url']['base']); ?>" />
				<?php
				echo $site->Get_seo_html();
				echo $site->Get_stylesheets_html();
				echo $site->Get_scripts_html();
				?>
			</head>
			<body style="padding:20px; background:#eee">
			<center style="font-size:14pt;">
				<?php
				$a=0;
				foreach ($site->Get_languages() as $code=>$ln) {
					$a++;
					if ($a>1) echo ' | ';
					echo '<a href="api/texts/'.$code.'/">'.$ln.'</a>';
				}
				?>
			</center>
			<?php
			if (count($list)) foreach ($list as $p) {
				//print_pre($p);
				echo '<div style=""><h2 style="margin: 8px 0;">'.$p['name']."</h2>\n"
				,"<span style=\"font-size:10pt;color:#888;\"><a href=\"".$site->Get_link($p['route'])."\">žiūrėti svetainėje</a> | puslapio id: ".$p['pid']."</span>\n"
				,"<div style=\"margin: 10px 0;background:#fff;border:1px solid #ddd;padding:10px;\">".$p['text']."</div></div>\n\n";
			}
			?>
			</body></html>
			<?php
			break;
		case 'plugin':
			$pluginName = str_replace('-', '_', $routes->Get(2));
			if (!empty($pluginName)) {
				if ($plugins->Is_active($pluginName)) {
					$apiPath = $core->Get_path('plugins', 'PC_api.php', $pluginName);
					if (is_file($apiPath)) {
						try {
							$routes->Shift(2);
							require($apiPath);
						}
						catch (exception $e) {
							echo 'Plugin API thrown an uncaught exception: '.$e->getMessage();
						}
					}
					else echo '<b>'.$pluginName.'</b> plugin doesn\'t have API';
				}
				else echo 'Plugin is not active';
			}
			else {
				echo 'Plugin name was not filled';
			}
			break;
		case 'sitemap':
			function Get_sub_list($pid) {
				global $page;
				$list = array();
				foreach ($page->Get_submenu($pid) as $p) {
					if (v($p['pid'])) {
						$list[$p['pid']] = $p;
						$list += Get_sub_list($p['pid']);
					}
					
				}
				return $list;
			}
			if (!$site->Identify(true)) {
				echo 'This site is turned off, so you can`t view its` sitemap.';
				exit;
			}
			///*
			header ("Content-Type:text/xml");
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			//*/
			$page->Load_menu();
			$list = array();
			//print_pre($page->menus);
			//break;
			foreach ($page->menus as $menu) {
				foreach ($menu as $p) {
					$list[$p['pid']] = $p;
					$list += Get_sub_list($p['pid']);
				}
			}
			$ids = array();
			foreach ($list as $p) {
				if (v($p['pid'])) {
					$ids[] = $p['pid'];
				}
				continue;
				/*echo '<url>'."\r\n";
				echo '<loc>'.$cfg['url']['base'].$site->Get_link($p['route']).'</loc>'."\r\n";
				echo '<changefreq>weekly</changefreq>'."\r\n";
				if ((int)$p['hot'] > 0) echo '<priority>0.8</priority>'."\r\n";
				echo '</url>'."\r\n";*/
			}
			$query = "SELECT pid,route,ln FROM {$cfg['db']['prefix']}content WHERE pid in(".implode(',', $ids).")";
			$r = $db->query($query);
			if (!$r) {
				header('HTTP/1.1 503 Service Temporarily Unavailable');
				exit;
			}
			
			while ($d = $r->fetch()) {
				echo '<url>'."\r\n";
				echo '<loc>'.$cfg['url']['base'].$site->Get_link($d['route'], $d['ln']).'</loc>'."\r\n";
				echo '<changefreq>weekly</changefreq>'."\r\n";
				if ((int)$list[$d['pid']]['hot'] > 0) echo '<priority>0.8</priority>'."\r\n";
				echo '</url>'."\r\n";
			}
			?>
			</urlset> 
			<?php
			break;
		//case 'get-online-users':
		//case 'page':
		default: echo 'Please select API action that you want to execute:<ul><li><a href="'.htmlspecialchars($cfg['url']['base']).'api/sitemap/">Sitemap</a></li><li><a href="'.htmlspecialchars($cfg['url']['base']).'api/texts/">List of texts</a></li></ul>';
	}
}