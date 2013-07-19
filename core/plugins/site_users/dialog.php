<?php
/** ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
$cfg['core']['no_login_form'] = true;
require_once '../../../admin/admin.php';



$plugin_name = basename(dirname(__FILE__));
$plugin_url = $cfg['url']['base'].$cfg['directories']['core_plugins_www'].'/'.$plugin_name.'/';
$plugin_file = $plugin_url . basename(__FILE__);

$plugin_path = $cfg['url']['base'].$cfg['directories']['core_plugins_www'].'/'.$plugin_name;

if (!isset($logger)) {
	$logger = new PC_debug();
	$logger->debug = true;
	$logger->set_instant_debug_to_file($cfg['path']['logs'] . 'plugins/site_users.html', false, 5);
}
$logger->debug('Starting plugin dialog', 3);

if (!function_exists('huge_rand')) {
	function huge_rand($limit) {
		$n = strlen($limit) - 2;
		$o = rand(1, 9);
		for ($i=0; $i<$n; $i++)
			$o .= rand(0, 9);
		return $o;
	}
}

$mod['name'] = 'Site users';
$mod['onclick'] = 'mod_site_users_click()';
$mod['priority'] = 10;

?>
<script type="text/javascript" src="js/BigInt.js"></script>
<script type="text/javascript" src="js/jsaes.js"></script>



<script type="text/javascript">

<?php
$js_files = array(
		'dialog.ln.js'
	);
	foreach ($js_files as $js_file) {
		if (@file_exists($js_file)) {
			include $js_file;
			echo "
";
		}
	}
?>
Ext.namespace('PC.plugins');

function mod_site_users_click() {
	var plugin_path = '<?php echo $plugin_path; ?>';
	
	//var dialog = PC.plugins.site_users;
	PC.plugin.site_users.dialog = {};
	var dialog = PC.plugin.site_users.dialog;
	dialog.plugin_file = '<?php echo $plugin_file; ?>';
	dialog.ln = PC.i18n.mod.site_users;
	var ln = dialog.ln;
	
	var crud_config = {
		ln: ln
	};
	var crud =false;
	
	var hook_params = {
		crud_config: crud_config
	};
	PC.hooks.Init('plugin/site_users/crud_object', hook_params);
	if (hook_params.crud_object) {
		crud = hook_params.crud_object;
	}
	
	if (!crud) {
		crud = new Plugin_site_users_crud(crud_config);
	}
	
	dialog.w = new PC.ux.Window({
		//modal: true,
		title: PC.i18n.mod.site_users.selfname,
		width: 810,
		height: 400,
		layout: 'fit',
		layoutConfig: {
			align: 'stretch'
		},
		items: //[
			crud,
		//],
		buttons: [
			{	text: PC.i18n.close,
				handler: function() {
					dialog.w.close();
				}
			}
		]
	});
	dialog.w.show();
}

//ProfisCMS.plugins.site_users = {
PC.plugin.site_users = {
	name: PC.i18n.mod.site_users.selfname,
	onclick: mod_site_users_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};

</script>