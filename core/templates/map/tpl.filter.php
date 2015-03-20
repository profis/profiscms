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
?>
<div  id="<?php echo $filter_el_id ?>">
<?php
foreach ($categories as $category) {
	?>
	<label for = "<?php echo $category['el_id'] ?>"><?php echo $category['data']->name ?>:</label> <span><input type="checkbox" id = "<?php echo $category['el_id'] ?>" rel = "<?php echo $category['id'] ?>" />&nbsp;</span>
	<?php
}
?>
</div>
