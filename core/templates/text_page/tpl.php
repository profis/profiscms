<?php
/**
 * @var PC_page $this
 * @var string $tpl_group
 * @var string $text
 * @var int $total_pages
 */
echo $text;
?>
<br />
<?php
if (!empty($this->route[1])) {
	$paging_route = $this->route[1];
}
else {
	$paging_route = $this->site->Get_home_link();
}
echo $this->site->Get_widget_text('PC_paging_widget', array(
	'base_url' => $this->site->Get_link($paging_route),
	'per_page' => 1,
	'total_items' => $total_pages
));



