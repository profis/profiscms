<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $site->Get_title(); ?></title>
	<base href="<?php echo htmlspecialchars($cfg['url']['base']); ?>" />
	<?php
	$site->Add_stylesheet($core->Get_theme_path() . 'css/bootstrap.css');
	$site->Add_stylesheet($core->Get_theme_path() . 'css/style.css');
	echo $site->Get_head();
	?>
	<script type="text/javascript">
	</script>
	<style type="text/css">
		a {color:#493BBF;}
		a:hover {color:#001E6F}
		li.hot a {font-weight: bold;color: #CF0C0C;}
		#location {background:#222;padding:3px 5px;color:#666}
		#location a {color: #aaa;}
		img.pc_icon{vertical-align:-3px;margin-right:2px;}
		
		
		body {
        padding-top: 20px;
        padding-bottom: 60px;
      }

      /* Custom container */
      .container {
        margin: 0 auto;
        max-width: 1000px;
      }
      .container > hr {
        margin: 60px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 80px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 100px;
        line-height: 1;
      }
      .jumbotron .lead {
        font-size: 24px;
        line-height: 1.25;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }


      /* Customize the navbar links to be fill the entire space of the .navbar */
      .navbar .navbar-inner {
        padding: 0;
      }
      .navbar .nav {
        margin: 0;
        display: table;
        width: 100%;
      }
      .navbar .nav li {
        display: table-cell;
        width: 1%;
        float: none;
      }
      .navbar .nav li a {
        font-weight: bold;
        text-align: center;
        border-left: 1px solid rgba(255,255,255,.75);
        border-right: 1px solid rgba(0,0,0,.1);
      }
      .navbar .nav li:first-child a {
        border-left: 0;
        border-radius: 3px 0 0 3px;
      }
      .navbar .nav li:last-child a {
        border-right: 0;
        border-radius: 0 3px 3px 0;
      }
	</style>
	
</head>
<body style="">
	<div class="container">
		<div class="masthead">
			<?php echo $site->Get_html_languages(array('ul_class' => 'nav nav-pills pull-right')); ?>
			<h3 class="muted"><a href="<?php echo $site->Get_home_link(); ?>">Home</a></h3>
		</div>
		<div class="navbar">
		  <div class="navbar-inner">
			<div class="container">
				<?php echo $page->Get_html_menu(0, array('ul_class' => 'nav', 'level' => 1)); ?>
			</div>
		  </div>
		</div><!-- /.navbar -->


		<?php
		$breadcrumbs = $site->Get_page_path();
		if (count($breadcrumbs) >= 1 and !$breadcrumbs[0]['front']) {
			?>
			<ul class="breadcrumb">
				<li>You are here: </li>
				<?php
				$breadcrumb_html = '';
				foreach ($breadcrumbs as $p) {
					if (empty($p['name'])) {
						continue;
					}
					if (!empty($breadcrumb_html)) {
						$breadcrumb_html .= ' &gt; ';
					}
					$breadcrumb_html .= '<li><a href="'.$site->Get_link($p['route']).'">'.v($p['name']).'</a></li>';
				}
				echo $breadcrumb_html;
				?>
			</ul>
			<?php
		}
		?>
		
		<div style="padding:10px;" class="pc_content">
			<?php 
			if($site->Is_front_page() and file_exists($core->Get_theme_path().'template_frontpage.php')){
				include $core->Get_theme_path().'template_frontpage.php';
			}else{
				echo $site->Get_text();
			}
			
			?>
			<div style="clear:both"></div>
		</div>

		<!-- <div style="color: #aaa; background: #eee;border:1px solid #aaa;padding:3px 5px;">
			<?php /*echo $page->Get_info_block(1); */?>
		</div> -->
		<div class="footer">
			<p>&copy; 2013</p>
		</div>
	</div>
</body>
</html>