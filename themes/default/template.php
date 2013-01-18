<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $site->Get_title(); ?></title>
	<base href="<?php echo htmlspecialchars($cfg['url']['base']); ?>" />
	<?php
	echo $site->Get_seo_html();
	echo $site->Get_stylesheets_html();
	echo $site->Get_scripts_html();
	echo $site->Get_favicon();
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
	</style>
	
	<?php echo PC_controller_rss::Get_header_addon_html(); ?>
	
</head>
<body style="background:#eee">
	<div style="width:800px;margin:0 auto;background:#fff">
		<?php
		//print_pre($core->Get_variables());
		//$gallery = new PC_gallery;
		//print_pre($gallery->Get_category_id_by_path('automobiliai'));
		?>
		<div style="background:#222;padding:3px 5px;">
			<a style="color:#fff;font-weight:bold" href="<?php echo $site->Get_link(0); ?>">Home</a>
		</div>
		<div style="background:#ccc;padding:3px 5px;">
			<?php
			/*$gallery = new PC_gallery;
			print_pre($gallery->Get_files($category_id));*/
			//print_pre($core->Search('la'));
			echo $site->Get_html_languages();
			?>
		</div>
		<div style="background:#aaa;padding:3px 5px;">
			<?php echo $page->Get_html_menu(); ?>
		</div>
		<div id="location">
			You are here: <?php
			foreach ($site->Get_page_path() as $p) {
				echo ' &gt; <a href="',$site->Get_link($p['route']),'">',v($p['name']),'</a>';
			}
			?>
		</div>
		<div style="padding:10px;" class="pc_content">
			<?php 
			//echo $site->Get_text(); 
			
			if($site->Is_front_page() and file_exists($core->Get_theme_path().'template_frontpage.php')){
				include $core->Get_theme_path().'template_frontpage.php';
			}else{
				echo $site->Get_text();
			}
			
			?>
			<div style="clear:both"></div>
		</div>
		<div style="background:#aaa;padding:3px 5px;">
			<?php echo $page->Get_html_menu(); ?>
		</div>
		<div style="color: #aaa; background: #eee;border:1px solid #aaa;padding:3px 5px;">
			<?php echo $page->Get_info_block(1); ?>
		</div>
	</div>
</body>
</html>