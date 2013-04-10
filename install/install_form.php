<?php

if (!defined('PC_INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

$drivers = PDO::getAvailableDrivers();

?>

<p>
	
</p>

<form action="" method="post">
	<input type="hidden" name="install" value="1"/>
	<table class="table-condensed" cellpadding="0" cellspacing="2" border="0">
		<tr>
			<td colspan="3"><h3><?php echo $t['db_info']; ?></h3></td>
		</tr>
		<tr>
			<td class="label_"><label for="config_db_driver"><?php echo $t['db_driver']; ?></label></td>
			<td class="field">
				<select id="config_db_driver" name="config[db_driver]">
					<?php
					if (in_array('mysql', $drivers)) {
						echo '<option value="mysql">MySQL</option>';
					}
					if (in_array('pgsql', $drivers)) {
						//echo '<option value="pgsql">PostgreSQL</option>';
					}
					?>
				</select>
			</td>
			<td class="help"><?php echo $t['required']; ?>.</td>
		</tr>
		<tr id="row-db-host">
			<td class="label_"><label for="config_db_host"><?php echo $t['db_host']; ?></label></td>
			<td class="field"><input class="textbox" id="config_db_host" maxlength="100" name="config[db_host]" size="50" type="text" value="localhost" /></td>
			<td class="help"><?php echo $t['required']; ?>.</td>
		</tr>
		<tr id="row-db-port">
			<td class="label_"><label class="muted" for="config_db_port"><?php echo $t['db_port']; ?></label></td>
			<td class="field"><input class="textbox" id="config_db_port" maxlength="10" name="config[db_port]" size="50" type="text" value="" /></td>
			<td class="help"><?php echo $t['optional']; ?>. <?php echo $t['db_port_expl']; ?></td>
		</tr>
		<tr id="row-db-user">
			<td class="label_"><label for="config_db_user"><?php echo $t['db_user']; ?></label></td>
			<td class="field"><input class="textbox" id="config_db_user" maxlength="255" name="config[db_user]" size="50" type="text" value="root" /></td>
			<td class="help"><?php echo $t['required']; ?>.</td>
		</tr>
		<tr id="row-db-pass">
			<td class="label_"><label class="muted" for="config_db_pass"><?php echo $t['db_password']; ?></label></td>
			<td class="field"><input class="textbox" id="config_db_pass" maxlength="40" name="config[db_pass]" size="50" type="password" value="" /></td>
			<td class="help"><?php echo $t['optional']; ?>. <?php echo $t['db_password_expl']; ?></td>
		</tr>
		<tr id="row-db-name">
			<td class="label_"><label for="config_db_name"><?php echo $t['db_name']; ?></label></td>
			<td class="field"><input class="textbox" id="config_db_name" maxlength="120" name="config[db_name]" size="50" type="text" value="profiscms" /></td>
			<td class="help" id="help-db-name"><?php echo $t['required']; ?>. <?php echo $t['db_name_expl']; ?></td>
		</tr>
		<tr id="row-table-prefix">
			<td class="label_"><label class="muted" for="config_table_prefix"><?php echo $t['db_prefix']; ?></label></td>
			<td class="field"><input class="textbox" id="config_table_prefix" maxlength="40" name="config[table_prefix]" size="50" type="text" value="pc_" /></td>
			<td class="help" id="help-db-prefix"><?php echo $t['optional']; ?>. <?php echo $t['db_prefix_expl']; ?></td>
		</tr>
		<tr>
			<td colspan="3"><h3><?php echo $t['other_info']; ?></h3></td>
		</tr>
		<tr>
			<td class="label_"><label for="config_admin_username"><?php echo $t['admin_name']; ?></label></td>
			<td class="field"><input class="textbox" id="config_admin_username" maxlength="40" name="config[admin_username]" size="50" type="text" value="<?php echo PC_DEFAULT_ADMIN_USER; ?>" /></td>
			<td class="help"><?php echo $t['required']; ?>. <?php echo $t['admin_name_expl']; ?></td>
		</tr>
		<tr>
			<td class="label_"><label for="config_admin_password"><?php echo $t['admin_pass']; ?></label></td>
			<td class="field"><input class="textbox" id="config_admin_password" maxlength="40" name="config[admin_password]" size="50" type="password" value="<?php echo PC_DEFAULT_ADMIN_PASSWORD; ?>" /></td>
			<td class="help"><?php echo $t['required']; ?>. <?php echo $t['admin_pass_expl']; ?></td>
		</tr>
	</table>
	<p class="buttons">
		<button class="btn btn-success" name="commit" type="submit"><?php echo $t['button_install']; ?></button>
	</p>
</form>