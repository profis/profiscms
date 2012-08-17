<?php
require("base.php");
error_reporting(E_ALL);
if ($routes->Get(1) == 'admin') {
	//administrator API
	$routes->Shift(2);
	require("admin/admin.php");
	switch (v($routes->Get(1))) {
		case 'phpinfo':
			phpinfo();
			break;
		case 'plugin':
			$pluginName = str_replace('-', '_', $routes->Get(2));
			if (!empty($pluginName)) {
				if ($plugins->Is_active($pluginName)) {
					$apiPath = $core->Get_path('plugins', 'PC_api_admin.php', $pluginName);
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
		default: echo 'Please select API action that you want to execute:<ul><li><a href="'.htmlspecialchars($cfg['url']['base']).'admin/api/phpinfo/">PHPinfo</a></li></ul>';
	}
}
else {
	//public API
	$routes->Shift(1);
	switch($routes->Get(1)) {
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
					$list[$p['pid']] = $p;
					$list += Get_sub_list($p['pid']);
				}
				return $list;
			}
			if (!$site->Identify(true)) {
				echo 'This site is turned off, so you can`t view its` sitemap.';
				exit;
			}
			header ("Content-Type:text/xml");
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			$page->Load_menu();
			$list = array();
			foreach ($page->menus as $menu) {
				foreach ($menu as $p) {
					$list[$p['pid']] = $p;
					$list += Get_sub_list($p['pid']);
				}
			}
			$ids = array();
			foreach ($list as $p) {
				$ids[] = $p['pid'];
				continue;
				/*echo '<url>'."\r\n";
				echo '<loc>'.$cfg['url']['base'].$site->Get_link($p['route']).'</loc>'."\r\n";
				echo '<changefreq>weekly</changefreq>'."\r\n";
				if ((int)$p['hot'] > 0) echo '<priority>0.8</priority>'."\r\n";
				echo '</url>'."\r\n";*/
			}
			$r = $db->query("SELECT pid,route,ln FROM {$cfg['db']['prefix']}content WHERE pid in(".implode(',', $ids).")");
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