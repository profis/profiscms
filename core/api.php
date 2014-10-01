<?php
/**
 * @var array $cfg
 * @var PC_core $core
 * @var PC_site $site
 * @var PC_page $page
 * @var PC_routes $routes
 * @var PC_plugins $plugins
 * @var PC_cache $cache
 * @var PC_auth $auth
 * @var PC_database $db
 */
use \Profis\CMS\SiteMap;

require_once 'path_constants.php';

require("base.php");

$logger = new PC_debug();
$logger->debug = true;
$logger->set_instant_debug_to_file($cfg['path']['logs'] . 'api.html');

error_reporting(E_ALL);

if (isset($_GET['r1'])) {
	$site->route[1] = $_GET['r1'];
}

if (isset($_GET['r2'])) {
	$site->route[2] = $_GET['r2'];
}

if (isset($_GET['r3'])) {
	$site->route[3] = $_GET['r3'];
}

if (isset($_GET['r4'])) {
	$site->route[4] = $_GET['r4'];
}

if (isset($_GET['r5'])) {
	$site->route[5] = $_GET['r5'];
}

if (isset($_GET['r6'])) {
	$site->route[6] = $_GET['r6'];
}

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
	
	$api_name = $routes->Get(1);
	
	if (method_exists($admin_api, $api_name)) {
		$answer = $admin_api->$api_name($routes->Get(2), $routes->Get(3), $routes->Get(4), $routes->Get(5), $routes->Get(6));
		if (is_array($answer)) {
			$answer = json_encode($answer);
		}
		echo $answer;
		exit;
	}
	
	switch ($routes->Get(1)) {
		case 'keepalive':
			break;
		case 'phpinfo':
			phpinfo();
			break;
		case 'server':
			print_pre($_SERVER);
			break;
		case 'tree':
			$tree = $core->Get_object('PC_database_tree');
			switch ($routes->Get(2)) {
				case 'debug':
					$params = array(
						'cols'=> array(
							'parent'=> $routes->Get(4),
							'name'=> $routes->Get(5)
						)
					);
					if ($routes->Get(3) == 'shop_categories') {
						$params['cols']['join_table'] = 'shop_category_contents';
						$params['cols']['join_col'] = 'category_id';
					}
					$tree->Debug_tree($routes->Get(3), $params);
					break;
				case 'recalculate':
					$params = array(
						'cols'=> array(
							'parent'=> $routes->Get(4)
						)
					);
					$tree->Recalculate($routes->Get(3), $params);
					break;
				
				case 'bustghosts':
					$params = array(
						'cols'=> array(
							'parent'=> $routes->Get(4)
						)
					);
					$tree->Delete_ghosts($routes->Get(3), $params);
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
						
						$class_name = $pluginName_up . '_' . $routes->Get(1) . '_admin_api';
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
							
							$extend_api_hook = 'plugin/' . $pluginName . '/extend-admin-api/' . $routes->Get(1);
							$logger->debug('extend_api_hook: ' . $extend_api_hook, 4);
							
							$extend_admin_api_class = '';
							$extend_admin_api_path = '';
							$core->Init_hooks($extend_api_hook, array(
								'class'=> &$extend_admin_api_class,
								'path'=> &$extend_admin_api_path
							));
							if (!empty($extend_admin_api_class)) {
								$extend_file_path = $extend_admin_api_path . '/' . $extend_admin_api_class . '.php';
								$logger->debug('extend_file_path: ' . $extend_file_path, 5);
								require_once $extend_admin_api_path . '/' . $extend_admin_api_class . '.php';
								$class_name = $extend_admin_api_class;
							}
							
							
							/* @var $api PC_shop_admin_api */ 
							$plugin_api_log_dir = $cfg['path']['logs'] . 'plugins_api/' . $pluginName . '';
							if (!file_exists($plugin_api_log_dir)) {
								@mkdir($plugin_api_log_dir);
							}
							$log_file = $plugin_api_log_dir . '/admin_'.$routes->Get(1) . '_' . $routes->Get(2) .'_api.html';
							$logger->debug('Log file: ' . $log_file, 3);
							$api = new $class_name($page_manager);
							$api->debug = true;
							$api->set_instant_debug_to_file($log_file);
							$proccessed = $api->process($routes->Get(3), $routes->Get(4), $routes->Get(5));

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
		case 'ip':
			echo $_SERVER["REMOTE_ADDR"];
			break;
		case 'ipmd5':
			echo md5($_SERVER["REMOTE_ADDR"]);
			break;
		case 'cfg':
			print_pre($cfg);
			break;
		case 'links':
			$links = array(
				'<strong>Admin api</strong>' => '',
				'phpinfo' => 'admin/api/phpinfo',
				'server' => 'admin/api/server',
				'cfg' => 'admin/api/cfg',
				'ip' => 'admin/api/ip',
				'ipmd5' => 'admin/api/ipmd5',
				'pass' => 'admin/api/pass',
				'debug' => 'admin/api/dbg',
				'debug-email' => 'admin/api/debug-email',
				'send test email' => 'admin/api/send_test_email',
				'error' => 'admin/api/error',
				
				'<strong>Logai</strong>' => '',
				'send email log' => 'logs/send_mail.html',
				'route' => 'logs/router/route.html',
				'routes' => 'logs/router/routes.html',
				'redirect' => 'logs/router/redirect.html',
			);
			$table = '';
			foreach ($links as $key => $link) {
				$link_show = $link_url = $cfg['url']['base'] . $link;
				if (empty($link)) {
					$link_show = '';
				}
				$table .= "<tr><td>$key</td><td><a target='_blank' href='$link_url'>" . $link_show .  '</a></td></tr>';
			}
			$table = '<table>' . $table . '</table>';
			echo $table;
			break;
		case 'debug':
		case 'dbg':
			$debug_logger = new PC_debug();
			$debug_logger->debug = true;
			//$debug_logger->debug_forced = true;
			$debug_logger->debug('Test');
			echo '$cfg[debug_output] = ' . v($cfg['debug_output']) . "\n<br />";
			echo '$cfg[debug_ip] = ' . v($cfg['debug_ip']) . "\n<br />";
			echo 'get_debug_string(): ' . $debug_logger->get_debug_string() . "\n<br />";
			break;
		case 'email':
		case 'debug-email':
			$email = 'email@profis.lt';
			$message = 'message';
			PC_utils::debugEmail($email, $message);
			print_pre($cfg['debug_email']);
			echo '<hr />';
			echo $email;
			echo '<hr />';
			echo $message;
			break;
		case 'send_test_email':
		case 'send-test-email':
			$message = 'Test message';
			$email_to = v($_GET['email'], 'test@profis.lt');
			echo '<hr />';
			echo 'sending to ' . $email_to;
			echo '<hr />';
			$send_result = PC_utils::sendEmail($email_to, $message);
			print_pre(PC_utils::$last_send_email_error);
			echo '<hr />';
			echo $send_result;
			echo '<hr />';
			echo $message;
			break;
		case 'error':
			echo 'display_errors = ' . ini_get('display_errors') . "\n<br />";
			echo 'register_globals = ' . ini_get('register_globals') . "\n<br />";
			echo 'max_execution_time = ' . ini_get('max_execution_time') . "\n<br />";
			echo 'post_max_size = ' . ini_get('post_max_size') . "\n<br />";
			echo 'post_max_size+1 = ' . ((int)ini_get('post_max_size')+1) . "\n<br />";
			//echo 'post_max_size in bytes = ' . return_bytes(ini_get('post_max_size')) . '<br />';
			echo '<hr />';
			echo 'log_errors = ' . ini_get('log_errors') . "\n<br />";
			echo 'error_log = ' . ini_get('error_log') . "\n<br />";
			
			break;
		
		case 'pass':
			$salt = $cfg['salt'];
			if (isset($_GET['salt']) and !empty($_GET['salt'])) {
				$salt = $_GET['salt'];
			}
			$pass = v($_GET['password'], 'admin');
			echo '<form action="" method="get">
				Salt: <input name="salt" value="'.pc_e(v($_GET['salt'])).'">
				<br />
				Pass: <input name="password" value="'.pc_e($pass).'">
				<input type=submit>
				</form>';
			echo '<hr />';
			echo $auth->users->auth_users_base->Encode_password($pass, $salt);
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
			$ln = $routes->Get(2);
			if (!empty($ln)) {
				$site->Set_language($ln);
			}
			$list = $page->Get_page(PC_page::ALL_PAGES);
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
			<div style="font-size:14pt;text-align:center;">
				<?php
				$a=0;
				foreach ($site->Get_languages() as $code=>$ln) {
					$a++;
					if ($a>1) echo ' | ';
					echo '<a href="api/texts/'.$code.'/">'.$ln.'</a>';
				}
				?>
			</div>
			<?php
			if (count($list)) foreach ($list as $p) {
				//print_pre($p);
				echo '<div style=""><h2 style="margin: 8px 0;">'.$p['name']."</h2>\n"
				,"<span style=\"font-size:10pt;color:#888;\"><a href=\"".$site->Get_link($p['route'])."\">žiūrėti svetainėje</a> | puslapio id: ".$p['pid']."</span>\n"
				,"<div style=\"margin: 10px 0;background:#fff;border:1px solid #ddd;padding:10px;\">".$p['text']."</div></div>\n\n";
			}
			
			if (v($cfg['api_texts_variables'])) {
				$all_variables = $core->Get_variables();
				foreach ($all_variables as $key => $value) {
					echo '<div style=""><div style=\"margin: 10px 0;background:#fff;border:1px solid #ddd;padding:10px;\">'.$value."</div></div>";
				}
			}
			
			?>
			</body></html>
			<?php
			break;
		case 'plugin':
			$pluginName = str_replace('-', '_', $routes->Get(2));
			if (!empty($pluginName)) {
				if ($plugins->Is_active($pluginName)) {
					if (strpos($pluginName, 'pc_') === 0) {
						$pluginName_up = strtoupper(substr($pluginName, 0, 2)) . substr($pluginName, 2);
					}
					else {
						$pluginName_up = strtoupper(substr($pluginName, 0, 1)) . substr($pluginName, 1);
					}
					if (!$site->Is_loaded()) {
						$site->Identify();
					};
					$routes->Shift(1);
					if (isset($_POST['ln'])) {
						$site->Set_language($_POST['ln']);
					}
					
					$more_shift = 1;
					
					$plugin_api_path = $core->Get_path('plugins', '', $pluginName) . 'api/';

					
					$common_class_name = $pluginName_up . '_api';
					$common_file_name = $plugin_api_path . "$common_class_name.php";
					
					$class_name = $pluginName_up . '_' . $routes->Get(2) . '_api';
					$file_name = $plugin_api_path . "$class_name.php";
					
					
					$proccessed = false;
					if (@file_exists($common_file_name)) {
						require_once($common_file_name);
						//echo ' ' . $common_class_name;
						$api = new $common_class_name();
						$proccessed = $api->process($routes->Get(3), $routes->Get(4), $routes->Get(5), $routes->Get(6));
						
					}
					if (!$proccessed and @file_exists($file_name)) {
						$routes->Shift(1);
						$more_shift--;
						require_once($file_name);
						//echo ' ' . $class_name;
						$api = new $class_name();
						$proccessed = $api->process($routes->Get(3), $routes->Get(4), $routes->Get(5), $routes->Get(6));
					}
					
					if ($proccessed) {
						$output = $api->get_output();
						if (is_array($output)) {
							$content_type = 'application/json';
							header('Content-Type: ' . $content_type);
							header('Cache-Control: no-cache');
							echo json_encode($output);
						}
						else {
							header('Cache-Control: no-cache');
							echo $output;
						}
						exit;
					}
					if ($more_shift > 0) {
						$routes->Shift($more_shift);
					}
					
					$apiPath = $core->Get_path('plugins', 'PC_api.php', $pluginName);
					if (is_file($apiPath)) {
						try {
							include($apiPath);
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
			function Map_sub_list($pid, &$site_languages, SiteMap $map) {
				global $page, $core;
				$list = array();
				foreach ($page->Get_submenu($pid, array(), false, false, true) as $p) {
					if (v($p['pid'])) {
						if( !$p['nositemap'] )
							$map->addPage($p['pid'], $site_languages, 'weekly', ($p['hot'] > 0) ? 0.8 : 0.5);
						if( $p['controller'] )
							$core->Init_hooks('sitemap.generate.' . $p['controller'], array(
								'pageId' => $p['pid'],
								'languages' => &$site_languages,
								'map' => $map
							));
						Map_sub_list($p['pid'], $site_languages, $map);
					}
				}
				return $list;
			}
			if (!$site->Identify(true)) {
				echo 'This site is turned off, so you can`t view its` sitemap.';
				exit;
			}

			$ln_model = new PC_language_model();
			$site_languages = $ln_model->get_all(array(
				'where' => array(
					'disabled' => 0,
					'site' => $site->get_id()
				),
				'value' => 'ln',
			));

			header ("Content-Type:text/xml");

			$map = new SiteMap();
			$map->open();

			$page->Load_menu(true);
			$list = array();

			foreach ($page->menus as $menu) {
				foreach ($menu as $p) {
					if( !$p['nositemap'] )
						$map->addPage($p['pid'], $site_languages, 'weekly', ($p['hot'] > 0) ? 0.8 : 0.5);
					if( $p['controller'] )
						$core->Init_hooks('sitemap.generate.' . $p['controller'], array(
							'pageId' => $p['pid'],
							'languages' => &$site_languages,
							'map' => $map
						));
					Map_sub_list($p['pid'], $site_languages, $map);
				}
			}

			$map->close();

			break;

		//case 'get-online-users':
		//case 'page':
		/*
		case 'new_password':
			$new_pass = $routes->Get(2);
			$new_pass = trim($new_pass);
			if (empty($new_pass)) {
				$new_pass = 'admin';
			}
			$db->query("UPDATE pc_auth_users SET pass = '".$auth->users->Encode_password($new_pass)."' WHERE username = 'admin'");
			break;
		*/
		default: echo 'Please select API action that you want to execute:<ul><li><a href="'.htmlspecialchars($cfg['url']['base']).'api/sitemap/">Sitemap</a></li><li><a href="'.htmlspecialchars($cfg['url']['base']).'api/texts/">List of texts</a></li></ul>';
	}
}