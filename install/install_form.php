<?php

if (!defined('PC_INSTALL_SEQUENCE')) {
    echo '<p>Illegal call. Terminating.</p>';
    exit();
}

$drivers = PDO::getAvailableDrivers();

?>


<h1>Installation information</h1>
<p>
	When setting up Profis CMS for use with multiple sites, please remember to either choose a site specific
	database name or to use a site specific table prefix.
</p>

<form action="" method="post">
	<input type="hidden" name="install" value="1"/>
	<table class="fieldset" cellpadding="0" cellspacing="2" border="0">
		<tr>
			<td colspan="3"><h3>Database information</h3></td>
		</tr>
		<tr>
			<td class="label_"><label for="config_db_driver">Database driver</label></td>
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
			<td class="help">Required.</td>
		</tr>
		<tr id="row-db-host">
			<td class="label_"><label for="config_db_host">Database server</label></td>
			<td class="field"><input class="textbox" id="config_db_host" maxlength="100" name="config[db_host]" size="50" type="text" value="localhost" /></td>
			<td class="help">Required.</td>
		</tr>
		<tr id="row-db-port">
			<td class="label_"><label class="muted" for="config_db_port">Port</label></td>
			<td class="field"><input class="textbox" id="config_db_port" maxlength="10" name="config[db_port]" size="50" type="text" value="" /></td>
			<td class="help">Optional. Default MySQL: 3306; default PostgreSQL: 5432</td>
		</tr>
		<tr id="row-db-socket">
			<td class="label_"><label class="muted" for="config_db_socket">Database unix socket</label></td>
			<td class="field"><input class="textbox" id="config_db_socket" maxlength="100" name="config[db_socket]" size="50" type="text" value="" /></td>
			<td class="help">Optional. When filled, database servername and port are ignored. (/path/to/socket)</td>
		</tr>
		<tr id="row-db-user">
			<td class="label_"><label for="config_db_user">Database user</label></td>
			<td class="field"><input class="textbox" id="config_db_user" maxlength="255" name="config[db_user]" size="50" type="text" value="root" /></td>
			<td class="help">Required.</td>
		</tr>
		<tr id="row-db-pass">
			<td class="label_"><label class="muted" for="config_db_pass">Database password</label></td>
			<td class="field"><input class="textbox" id="config_db_pass" maxlength="40" name="config[db_pass]" size="50" type="password" value="" /></td>
			<td class="help">Optional. If there is no database password, leave it blank.</td>
		</tr>
		<tr id="row-db-name">
			<td class="label_"><label for="config_db_name">Database name</label></td>
			<td class="field"><input class="textbox" id="config_db_name" maxlength="120" name="config[db_name]" size="50" type="text" value="profiscms" /></td>
			<td class="help" id="help-db-name">Required. You have to create a database manually and enter its name here.</td>
		</tr>
		<tr id="row-table-prefix">
			<td class="label_"><label class="muted" for="config_table_prefix">Table prefix</label></td>
			<td class="field"><input class="textbox" id="config_table_prefix" maxlength="40" name="config[table_prefix]" size="50" type="text" value="pc_" /></td>
			<td class="help" id="help-db-prefix">Optional. Useful to prevent conflicts if you have, or plan to have, multiple Profis cms installations with a single database.</td>
		</tr>
		<tr>
			<td colspan="3"><h3>Other information</h3></td>
		</tr>
		<tr>
			<td class="label_"><label for="config_admin_username">Administrator username</label></td>
			<td class="field"><input class="textbox" id="config_admin_username" maxlength="40" name="config[admin_username]" size="50" type="text" value="<?php echo PC_DEFAULT_ADMIN_USER; ?>" /></td>
			<td class="help">Required. Allows you to specify a custom username for the administrator. Default: admin</td>
		</tr>
		<tr>
			<td class="label_"><label for="config_admin_password">Administrator password</label></td>
			<td class="field"><input class="textbox" id="config_admin_password" maxlength="40" name="config[admin_password]" size="50" type="password" value="<?php echo PC_DEFAULT_ADMIN_PASSWORD; ?>" /></td>
			<td class="help">Required. Allows you to specify a custom password for the administrator. Default: admin</td>
		</tr>
	</table>
	<p class="buttons">
		<button class="btn btn-success" name="commit" type="submit">Install now!</button>
	</p>
</form>