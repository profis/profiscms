<?php
/* Make sure we've been called using index.php */
if (!defined('PC_INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

$requirements = array(
	'PHP ' . $installer->min_php_version => $installer->get_validation_result('php_version'),
	'PDO supported' => $installer->get_validation_result('pdo'),
	'PDO supports MySQL' => $installer->get_validation_result('pdo_mysql'),
	'Mod rewrite enabled' => $installer->get_validation_result('mod_rewrite'),
	'mbstring' => $installer->get_validation_result('mbstring'),
	'mcrypt' => $installer->get_validation_result('mcrypt'),
	'gd (version ' . $installer->min_gd_version . '+)' => $installer->get_validation_result('gd'),
	'bcmath' => $installer->get_validation_result('bcmath'),
	'iconv' => $installer->get_validation_result('iconv'),
	'Config file exists' => $installer->get_validation_result('config_file_exists'),
	'Config file is writable' => $installer->get_validation_result('config_file_writable'),
);

?>



<h1>Requirements check</h1>
<p>
	All of the items that are checked below, are <strong>required</strong>
	for proper installation and operation of Profis CMS.
</p>
<p>
	Please make sure you either have: MySQL <?php echo $installer->min_mysql_version?> upwards.
</p>
<table class="table">
	<thead>
		<tr>
			<th id="requirement">Requirement</th>
			<th>Available?</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($requirements as $key => $value) {
		?>
		<tr>
			<td><span class="text-info"><strong><?php echo $key; ?></strong></span></td>
			<td class="available strong"><strong><?php echo $value; ?></strong></td>
		</tr>
		<?php
		}
		?>
	</tbody>
</table>

<p>
	<form style="text-align: right;" action="" method="POST">
	<?php
	if (!$installer->requirements_passed) {
		echo 'Please fix these problems and <button class="btn btn-success" type="submit">test again</button>';
	} else {
		echo '<button class="btn btn-success" name="install" type="submit" value="1">Continue to install</button>';
	}
	?>
	</form>
</p>