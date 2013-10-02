<?php 
$path = $site->Get_page_path();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $site->Get_title(); ?></title>
	<base href="<?php echo htmlspecialchars($cfg['url']['base']); ?>" />
	<?php
	$site->Add_stylesheet($core->Get_theme_path() . 'css/bootstrap.css');
	$site->Add_stylesheet($core->Get_theme_path() . 'css/style.css');
	
	$site->Add_script($core->Get_theme_path() . 'js/bootstrap.min.js');
	$site->Add_script($core->Get_theme_path() . 'js/main.js');
	echo $site->Get_head();
	?>
	
	<!--[if lt IE 9]>
	  <script src="../../assets/js/html5shiv.js"></script>
	  <script src="../../assets/js/respond.min.js"></script>
	<![endif]-->
	
</head>

<body>
	<div id="wrap">

		<!-- FIXED NAVBAR -->
		<div class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#">Project name</a>
				</div>
				<div class="collapse navbar-collapse">
					<?php 
						echo $site->Get_widget_text('PC_hmenu_widget', array(
							'menu' => 0,
							'max_levels' => 2,
							'wrap' => '<ul class="nav navbar-nav">|</ul>',
							'li_class_with_submenu' => 'dropdown',
							'a_tag_params_with_submenu' => 'class="dropdown-toggle" data-toggle="dropdown"',
							'inner_wrap_with_submenu' => '| <b class="caret"></b>',
							'no_href_with_submenu' => true,
							'submenu_for_all' => true,
							'level_config' => array(
								'2' => array(
									'wrap' => '<ul class="dropdown-menu">|</ul>',
								)
							)
						)); 
					
						$page->Get_html_menu(0, array(
						'ul_class' => 'nav navbar-nav', 
						'level' => 1, 
						'li_class_with_submenu' => 'dropdown',
						'inner_wrap_with_submenu' => '| <b class="caret"></b>',
						'include_submenu' => false,
						'ul_2_class' => 'dropdown-menu'
					)); ?>
				</div>
			</div>
		</div>
		<!-- / FIXED NAVBAR -->
		
		<!-- CONTAINER -->
		<div class="container">

			<div class="row">
				<div class="col-md-3 col-sm-3">
				
					<?php
					echo $site->Get_widget_text('PC_plugin_pc_shop_currency_selector_widget', array(
					
					));
					?>
					
					<!-- INFORMATION MENU -->
					<?php 
						echo $site->Get_widget_text('PC_vmenu_widget', array(
							'root' => $path[0]['pid'],
							'wrap' => '<ul id="menu" class="nav nav-pills nav-stacked side_block">|</ul>',
							'level_config' => array(
								'2' => array('wrap' => '<ul>|</ul>'),
								'3' => array('wrap' => '<ul>|</ul>'),
								'4' => array('wrap' => '<ul>|</ul>'),
								'5' => array('wrap' => '<ul>|</ul>')
							)
						)); 
					?>
					<!-- / INFORMATION MENU -->
					
					
					
					<?php
					echo $site->Get_widget_text('PC_plugin_pc_shop_mini_basket_widget', array(
						'cart_page_ref' => ''
					));
					?>
				</div>
				
				<div class="col-md-9 col-sm-9 pc_content">
				
					<?php
					$breadcrumbs = $site->Get_page_path();
					$breadcrumb_count = count($breadcrumbs);
					if ($breadcrumb_count >= 1 and !$breadcrumbs[0]['front']) {
						array_unshift($breadcrumbs, array(
							'name' => 'Home',
							'route' => '',
						));
						$breadcrumb_count++;
						?>
						<!-- NAVIGATION -->
						<ol class="breadcrumb">
							<?php
							$breadcrumb_html = '';
							$i = 0;
							foreach ($breadcrumbs as $p) {
								$i++;
								if (empty($p['name'])) {
									continue;
								}
								$li_class = '';
								$item_html = v($p['name']);
								if ($breadcrumb_count != $i) {
									if (!empty($p['link'])) {
										$link = $p['link'];
									}
									elseif (!empty($p['route'])) {
										$link = $site->Get_link($p['route']);
									}
									else {
										$link = $site->Get_home_link();
									}
									$item_html = '<a href="'.$link.'">'.$item_html.'</a>';
								}
								else {
									$li_class = ' class="active"';
								}
								$breadcrumb_html .= '<li'.$li_class.'>'.$item_html.'</li>';
							}
							echo $breadcrumb_html;
							?>
						</ol>
						<!-- / NAVIGATION -->
						<?php
					}
					?>
				
					<?php 
					if($site->Is_front_page() and file_exists($core->Get_theme_path().'template_frontpage.php')){
						include $core->Get_theme_path().'template_frontpage.php';
					}else{
						echo $site->Get_text();
					}
					
					?>
					
				</div>
			</div>
		</div>
		<!-- / CONTAINER -->
	</div>
	<!-- FOOTER -->
	<div id="footer">
		<div class="container">
			<p id="copyright" class="text-muted">© 2013 UAB <a href="http://www.profis.eu" title="Profis">„Profis“</a>.</p>
		</div>
	</div>
	<!-- / FOOTER -->
	
</body>
</html>