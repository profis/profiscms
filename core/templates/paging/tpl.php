<?php
/**
 * @var array $items
 * @var int $max_page
 */

use \Profis\Utils\HTML;

if( v($max_page) <= 1 )
	return;
?>
<div class="clearfix"></div>
<div class="pagination-wrap clearfix">
	<ul class="pagination"><?php
		foreach( $items as $key => $item ) {
			$li_classes = array();
			if( v($item['disabled']) )
				$li_classes[] = 'disabled';
			if( v($item['active']) )
				$li_classes[] = 'active';
			if( isset($item['class']) )
				$li_classes[] = $item['class'];
			$li_class = implode(' ', $li_classes);
			echo HTML::tag('li', array('class' => $li_class), HTML::link($item['label'], v($item['disabled']) ? '' : v($item['link']), array('title' => '')));
		}
	?></ul>
</div>
