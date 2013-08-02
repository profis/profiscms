<?php
	if (v($max_page) <= 1) {
		return;
	}
?>
<div class="pagination">
  <ul>
	<?php
	foreach ($items as $key => $item) {
		$li_classes = array ();
		if (v($item['disabled'])) {
			$li_classes[] = 'disabled';
		}
		if (v($item['active'])) {
			$li_classes[] = 'active';
		}
		$li_class = implode(' ', $li_classes);
	?>
		<li class="<?php echo $li_class ?>"><a href="<?php echo v($item['link']) ?>"><?php echo $item['label'] ?> </a></li>
	<?php
	}
	?>  
  </ul>
</div>