<?php
/**
 * @var PC_page $this
 * @var string $tpl_group
 * @var array[] $categories
 * @var string $id
 * @var string $filter_el_id
 * @var string $width
 * @var string $height
 * @var string $style
 * @var string $class
 * @var bool $filter
 * @var string $js
 */

include $this->core->Get_tpl_path('map', 'tpl.map');

if ($filter) {
	include $this->core->Get_tpl_path('map', 'tpl.filter');
}



