<?php
$this_wrap_begin = $wrap_begin;
$this_wrap_end = $wrap_end;
if (count($menu)) {
	echo $this_wrap_begin;
	foreach ($menu as $menu_item) {
		?>
		<li <?php if (v($menu_item['_active'])) {?> class="active" <?php }?>><a href="<?php echo v($menu_item['link'], '')?>"><?php echo $menu_item['name']?></a></li>
		<?php
		$menu = false;
		if (v($menu_item['_submenu'])) {
			$menu = $menu_item['_submenu'];
		}
		elseif (v($menu_item['children'])) {
			$menu = $menu_item['children'];
		}
		if ($menu and !empty($menu)) {
			$level++;
			list($wrap_begin, $wrap_end) = explode('|', v($this->_config['wrap_' . $level], $this->_config['wrap']));
			include $this->core->Get_tpl_path($tpl_group, 'tpl.submenu');	
		}
	}
	echo $this_wrap_end;
}



