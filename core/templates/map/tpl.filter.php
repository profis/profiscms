<div  id="<?php echo $filter_el_id ?>">
<?php
foreach ($categories as $category) {
	?>
	<label for = "<?php echo $category['el_id'] ?>"><?php echo $category['data']->name ?>:</label> <span><input type="checkbox" id = "<?php echo $category['el_id'] ?>" rel = "<?php echo $category['id'] ?>" />&nbsp;</span>
	<?php
}
?>
</div>
