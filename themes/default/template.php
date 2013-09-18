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
					<?php echo $page->Get_html_menu(0, array('ul_class' => 'nav navbar-nav', 'level' => 1)); ?>
				</div>
			</div>
		</div>
		<!-- / FIXED NAVBAR -->
		
		<!-- CONTAINER -->
		<div class="container">

			<div class="row">
				<div class="col-md-3 col-sm-3">
				
					<!-- INFORMATION MENU -->
					<?php 
						echo $site->Get_widget_text('PC_vmenu_widget', array(
							'root' => $path[0]['pid'],
							'wrap' => '<ul id="menu" class="nav nav-pills nav-stacked side_block">|</ul>',
							'wrap_2' => '<ul>|</ul>',
							'wrap_3' => '<ul>|</ul>',
							'wrap_4' => '<ul>|</ul>',
							'wrap_5' => '<ul>|</ul>'
						)); 
					?>
					<!-- / INFORMATION MENU -->
					
					<?php
					echo $site->Get_widget_text('PC_plugin_pc_shop_mini_basket_widget', array(
						'cart_page_ref' => ''
					));
					?>
				</div>
				
				<div class="col-md-9 col-sm-9">
				
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
									if (!empty($p['route'])) {
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