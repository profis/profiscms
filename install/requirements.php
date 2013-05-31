<?php
/* Make sure we've been called using index.php */
if (!defined('PC_INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

$requirements = array(
	'PHP ' . $installer->min_php_version => $installer->get_validation_result('php_version'),
	$t['req_pdo'] => $installer->get_validation_result('pdo'),
	$t['req_pdo_mysql'] => $installer->get_validation_result('pdo_mysql'),
	$t['req_mod_rewrite'] => $installer->get_validation_result('mod_rewrite'),
	'mbstring' => $installer->get_validation_result('mbstring'),
	'mcrypt' => $installer->get_validation_result('mcrypt'),
	'gd (' . $t['req_version'] . ' ' . $installer->min_gd_version . '+)' => $installer->get_validation_result('gd'),
	'bcmath' => $installer->get_validation_result('bcmath'),
	'iconv' => $installer->get_validation_result('iconv'),
	$t['req_config_file_exists']=> $installer->get_validation_result('config_file_exists'),
	$t['req_config_file_writable'] => $installer->get_validation_result('config_file_writable'),
);

//phpinfo();
//echo '<pre>';
//print_r (mysql_get_client_info());
//echo '</pre>';
//$pdo = new PDO();
//$pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
//echo PDO::getAttribute(PDO::ATTR_SERVER_VERSION);
?>


<p>
	<? echo str_replace('{min_mysql_version}', $installer->min_mysql_version, $t['mysql_requirement']) ?>
</p>

<table class="table">
	<thead>
		<tr>
			<th id="requirement"><? echo $t['requirement'] ?></th>
			<th>&nbsp;</th>
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
<hr />
<p>
	<form style="text-align: left;" action="" method="POST">
	<?php
	if (!$installer->requirements_passed) {
		echo '' . $t['fix_problems_and'] .' <button class="btn btn-success" type="submit">' . $t['button_test_again'] .'</button>';
	} else {
		echo '<button class="btn btn-success" name="install" type="submit" value="1">' . $t['button_continue_to_install'] .'</button>';
	}
	?>
	</form>
</p>