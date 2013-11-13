<?php
if (count($menu)) {
	$level_wrap_end_var_name = 'this_wrap_end_' . $level;
	$this_config = $this->_config;
	if (isset($this->_config['level_config']) and isset($this->_config['level_config'][$level])) {
		$this_config = array_merge($this_config, $this->_config['level_config'][$level]);
	}
	list($this_config['wrap_begin'], $this_config['wrap_end']) = explode('|', $this_config['wrap']);
	$this_wrap_begin = $this_config['wrap_begin'];
	$$level_wrap_end_var_name = $this_config['wrap_end'];
	echo $this_wrap_begin;
	//print_pre($menu);
	foreach ($menu as $menu_item) {
		$submenu = false;
		if (v($menu_item['_submenu'])) {
			$submenu = $menu_item['_submenu'];
		}
		elseif (v($menu_item['children'])) {
			$submenu = $menu_item['children'];
		}
			
		$li_classes = array();
		if (v($menu_item['_active'])) {
			$li_classes[] = 'active';
		}
		$a_tag_params = '';
		if (isset($menu_item['link'])) {
			$link = $menu_item['link'];
			if (!empty($this->site->link_prefix) and strpos($link, $this->site->link_prefix) !== 0) {
				$link = $this->site->Get_link($link);
			}
		}
		else {
			$link = $this->site->Get_link($menu_item['route']);
		}
		$full_href = 'href="'. $link . '"';
		
		$inner = $menu_item['name'];
				
		if ($submenu and !empty($submenu) and v($this_config['li_class_with_submenu'])) {
			if (v($this_config['inner_wrap_with_submenu'])) {
				list($iwb, $iwe) = explode('|', $this_config['inner_wrap_with_submenu']);
				$inner = $iwb . $inner . $iwe;
			}
			if (v($this_config['li_class_with_submenu'])) {
				$li_classes[] = $this_config['li_class_with_submenu'];
			}
			if (v($this_config['no_href_with_submenu'])) {
				$full_href = 'href';
			}
			if (v($this_config['a_tag_params_with_submenu'])) {
				$a_tag_params = $this_config['a_tag_params_with_submenu'];
			}
		}
		
		
		?>
		<li class="<?php echo implode(' ', $li_classes)?>">
			<a <?php echo $full_href?> <?php echo $a_tag_params?>><?php echo $inner?></a>
			<?php
			if ($submenu and !empty($submenu)) {
				$level++;
				$menu = $submenu;
				include $this->core->Get_tpl_path($tpl_group, 'tpl.submenu');
				$level--;
			}
			?>
		</li>
		<?php
	}
	echo $$level_wrap_end_var_name;
}



