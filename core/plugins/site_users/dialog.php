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
$plugin_url = $cfg['url']['base'].$cfg['directories']['core_plugins'].'/'.$plugin_name.'/';
$plugin_file = $plugin_url . basename(__FILE__);

$plugin_path = $cfg['url']['base'].$cfg['directories']['core_plugins'].'/'.$plugin_name;

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

if (isset($_POST['ajax'])) {
	header('Content-Type: application/json');
	header('Cache-Control: no-cache');
	
	$out = array();
	
	if (isset($_POST['email'])) {
		$logger->debug('Email is set');
		$errors = false;
		$sets = array();
		$values = array();
		$params = array();
		$fields = array(
			'email'=> array(
				'validate'=> function($value){
					global $db, $cfg;
					if (!Validate('email', $value)) return false;
					$r = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}site_users WHERE email=? LIMIT 1");
					$s = $r->execute(array($value));
					if (!$s) return false;
					if ($r->rowCount() == 1) return false;
					return true;
				}
			),
			'password'=> array(
				'validate'=> function($value){
					return Validate('password', $value);
				},
				'sanitize'=> function($value){
					return md5(sha1($value));
				}
			),
			'name'=> array(
				'validate'=> function($value){
					return Validate('name', $value);
				}
			),
			'login'=> array(
				'validate'=> function($value){
					global $db, $cfg;
					if (!Validate('name', $value, true)) return false;
					$r = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}site_users WHERE login=? LIMIT 1");
					$s = $r->execute(array($value));
					if (!$s) return false;
					if ($r->rowCount() == 1) return false;
					return true;
				}
			),
			'banned'=> array(
				'sanitize'=> function($value){
					return Sanitize('boolean', $value, true);
				},
			)
		);
		foreach ($fields as $f=>$data) {
			if (isset($_POST['new_'.$f])) {
				if (isset($data['validate'])) {
					$logger->debug('Validating ' . $f, 1);
					if (!is_callable($data['validate'])) {
						$logger->debug(':( no method validate', 3);
						$errors = true;
					}
					elseif (!$data['validate']($_POST['new_'.$f])) {
						$logger->debug(":( value: {$_POST['new_'.$f]} was wrong for some reason", 3);
						$errors = true;
					}
				}
				if (!$errors) {
					$sets[] = $f.'=?';
					$new_value = (is_callable(v($data['sanitize']))?$data['sanitize']($_POST['new_'.$f]):$_POST['new_'.$f]);
					$values[$f] = '?';
					$params[] = $new_value;
				}
			}
		}
		if ($_POST['id'] != 0) {
			$logger->debug('Id is not zero: updating', 1);
			if (!$errors && count($sets)) {
				$r = $db->prepare("UPDATE {$cfg['db']['prefix']}site_users SET ".implode(',', $sets)." WHERE email=?");
				$params[] = $_POST['email'];
				$s = $r->execute($params);
				if (!$s) $errors = true;
				else {
					if (!$r->rowCount()) $errors = true;
				}
			}
		}
		else {
			$logger->debug('Id is zero: creating', 1);
			if (!$errors && count($sets)) {
				$now = time();
				
				//$values['email'] = '?';
				//$params[] = $_POST['email'];
				
				$values['date_registered'] = '?';
				$params[] = $now;
				
				$values['last_seen'] = '?';
				$params[] = 0;
				
				$values['confirmation'] = '?';
				$params[] = '';
				
				$values['flags'] = '?';
				$params[] = PC_user::PC_UF_DEFAULT;
				$insert_query = "INSERT INTO {$cfg['db']['prefix']}site_users (".implode(',', array_keys($values)).") VALUES (".implode(',', $values).")";
				$r = $db->prepare($insert_query);
				$logger->debug_query($insert_query, $params, 3);
				$logger->debug($values, 4);
				$logger->debug($params, 4);
				$s = $r->execute($params);
				if (!$s) $errors = true;
				else {
					if (!$r->rowCount()) $errors = true;
				}
			}
		}
		
		if ($errors) {
			$logger->debug(':( Errors', 1);
			echo 'errors';
			return;
		}
	}
	if (isset($_POST['delete'])) {
		$errors = false;
		if (empty($_POST['delete'])) $errors = true;
		else {
			$r = $db->prepare("DELETE FROM {$cfg['db']['prefix']}site_users WHERE email=?");
			$s = $r->execute(array($_POST['delete']));
			if (!$s) $errors = true;
		}
		if ($errors) {
			echo 'errors';
			return;
		}
	}
	
	$r = $db->query("SELECT * FROM {$cfg['db']['prefix']}site_users");
	if ($r) {
		while ($d = $r->fetch()) {
			$out[] = array(
				'id'=>$d['id'],
				'email'=>$d['email'],
				'login'=>$d['login'],
				'name'=>$d['name'],
				'date_registered'=>$d['date_registered'],
				'last_seen'=>$d['last_seen'],
				'confirmation'=>$d['confirmation'],
				'banned'=>$d['banned'],
				'flags'=>$d['flags']
			);
		}
	}
	echo json_encode($out);
	return;
}

$mod['name'] = 'Site users';
$mod['onclick'] = 'mod_site_users_click()';
$mod['priority'] = 10;

?>
<script type="text/javascript" src="js/BigInt.js"></script>
<script type="text/javascript" src="js/jsaes.js"></script>
<script type="text/javascript">
Ext.namespace('PC.plugins');

PC.utils.localize('mod.site_users', {
	en: {
		selfname: 'Site users',
		email: 'Email',
		login: 'Nickname',
		name: 'Name',
		banned: 'Ban user',
		pass_new: 'New password',
		pass_repeat: 'Repeat password',
		update: {
			success: 'User has been saved successfully.',
			error: 'There was an error while trying to update user.'
			
		},
		passwords_doesnt_match: 'Passwords doesn`t match',
		_delete: {
			confirmation: 'Confirmation',
			confirm_message: 'Are you sure you want to delete this user?',
			error: 'There was an error while trying to delete user.'
		},
		date_registered: 'Date registered',
		last_login: 'Last login'
	},
	lt: {
		selfname: 'Svetainės vartotojai',
		email: 'El. paštas',
		login: 'Slapyvardis',
		name: 'Vardas Pavardė',
		banned: 'Banas',
		pass_new: 'Naujas slaptažodis',
		pass_repeat: 'Pakartokite slaptažodį',
		update: {
			success: 'Vartotojo duomenys sėkmingai atnaujinti',
			error: 'Vartotojo duomenys nebuvo išsaugoti'
			
		},
		passwords_doesnt_match: 'Slaptažodžiai turi sutapti',
		_delete: {
			confirmation: 'Patvirtinimas',
			confirm_message: 'Ar jūs tikrai norite panaikinti šį vartotoją?',
			error: 'Vartotojo panaikinti nepavyko'
		},
		date_registered: 'Registracijos data',
		last_login: 'Pask. prisijungimas'
	},
	 ru: {
        selfname: 'Пользователи сайта',
        email: 'Эл. почта',
		login: 'Псевдоним',
		name: 'Имя Фамилия',
		banned: 'Бан',
        pass_new: 'Новый пароль',
        pass_repeat: 'Повторите пароль',
        update: {
            success: 'Учетная запись пользователя успешно сохранена.',
            error: 'При попытке обновить данные учетной записи пользователя произошла ошибка.'
           
        },
        passwords_doesnt_match: 'Введённые пароли не совпадают',
        _delete: {
            confirmation: 'Подтверждение',
            confirm_message: 'Вы действительно хотите удалить учетную запись пользователя?',
            error: 'При попытке удвлить учетную запись пользователя произошла ошибка.'
        },
		date_registered: 'Дата регистрации',
        last_login: 'Последнее подсоединение'
    }
});

function mod_site_users_click() {
	var plugin_path = '<?php echo $plugin_path; ?>';
	
	//var dialog = PC.plugins.site_users;
	PC.plugin.site_users.dialog = {};
	var dialog = PC.plugin.site_users.dialog;
	dialog.plugin_file = '<?php echo $plugin_file; ?>';
	dialog.ln = PC.i18n.mod.site_users;
	var ln = dialog.ln;
	dialog.Isset = function(confirmation) {
		if (typeof confirmation == 'string') {
			if (confirmation.length) return true;
		}
		return false;
	}
	dialog.Status_icon = function(id, n) {
		if (parseInt(n.banned)) {
			return '<img src="images/delete.png" alt="" />';
		}
		if (!dialog.Isset(n.confirmation)) {
			return '<img src="images/tick.png" alt="" />';
		}
		return '<img src="images/hourglass.png" alt="" />';
	}
	dialog.Time_to_date = function(time){
		return new Date(time*1000).format('Y-m-d H:i');
	}
	dialog.store = new Ext.data.JsonStore({
		url: dialog.plugin_file,
		remoteSort: false,
		fields: [
			'id', 'email', 'login', 'name', 'date_registered', 'last_seen', 'confirmation', 'banned', 'flags',
			{name: 'confirmed', mapping: 'confirmation', convert: dialog.Isset},
			{name: 'status', mapping: 'id', convert: dialog.Status_icon},
			{name: '_date_registered', mapping: 'date_registered', convert: dialog.Time_to_date},
			{name: '_last_seen', mapping: 'last_seen', convert: dialog.Time_to_date}
		],
		baseParams: {ajax: 1},
		//totalProperty: 'total',
		//root: 'comments',
		idProperty: 'id',
		autoLoad: true,
		sortInfo: {
			field: 'date_registered',
			direction: 'DESC'
		}
	});
	dialog.grid = {	ref: '_grid',
		xtype: 'grid',
		width: 510,
		height: 400,
		cls: 'only-right-border',
		store: dialog.store,
		colModel: new Ext.grid.ColumnModel({
			defaults: {
				sortable: true
			},
			columns: [
				{header: '&nbsp;', dataIndex: 'status', width: 30},
				{header: ln.email, dataIndex: 'email', width: 150},
				{header: ln.login, dataIndex: 'login', width: 120},
				{header: ln.name, dataIndex: 'name', width: 120},
				{header: ln.date_registered, dataIndex: '_date_registered'},
				{header: ln.last_login, dataIndex: '_last_seen'}
			]
		}),
		selModel: new Ext.grid.RowSelectionModel({
			singleSelect: true,
			listeners: {
				selectionchange: function(sm){
					if (!sm.getCount()) {
						//disable
						dialog.w._btn_save.disable();
						dialog.w._btn_del.disable();
						//clear values
						dialog.Clear_form();
					}
					else {
						var node = sm.getSelected();
						//enble
						dialog.w._btn_save.enable();
						dialog.w._btn_del.enable();
						//update values
						dialog.w._f._email.setValue(node.data.email);
						dialog.w._f._email.originalValue = node.data.email;
						
						dialog.w._f._login.setValue(node.data.login);
						dialog.w._f._login.originalValue = node.data.login;
						
						dialog.w._f._name.setValue(node.data.name);
						dialog.w._f._name.originalValue = node.data.name;
						
						dialog.w._f._banned.setValue(node.data.banned);
						dialog.w._f._banned.originalValue = node.data.banned;
					}
				}
			}
		})
	};
	dialog.Clear_form = function() {
		dialog.w._f._email.setValue('');
		dialog.w._f._login.setValue('');
		dialog.w._f._name.setValue('');
		dialog.w._f._pass1.setValue('');
		dialog.w._f._pass2.setValue('');
		dialog.w._f._banned.setValue(0);
	}
	dialog.form = {
		ref: '_f',
		flex: 1,
		layout: 'form',
		padding: 6,
		border: false,
		bodyCssClass: 'x-border-layout-ct',
		labelWidth: 100,
		labelAlign: 'right',
		defaults: {xtype: 'textfield', anchor: '100%'},
		items: [
			{	ref: '_email',
				fieldLabel: dialog.ln.email
			},
			{	ref: '_login',
				fieldLabel: dialog.ln.login
			},
			{	ref: '_name',
				fieldLabel: dialog.ln.name
			},
			{	ref: '_pass1',
				fieldLabel: dialog.ln.pass_new,
				inputType: 'password'
			},
			{	ref: '_pass2',
				fieldLabel: dialog.ln.pass_repeat,
				inputType: 'password'
			},
			{	ref: '_banned',
				xtype: 'checkbox',
				fieldLabel: dialog.ln.banned
			}
		],
		buttonAlign: 'center',
		buttons: [
			{	text: PC.i18n.save,
				iconCls: 'icon-save',
				ref: '../../_btn_save',
				disabled: true,
				handler: function(b, e) {
					var node = dialog.grid.selModel.getSelected();
					if (!node) return;
					if (dialog.w._f._email.getValue() == '') {
						dialog.w._f._email.focus();
						return;
					}
					if (dialog.w._f._pass1.getValue() != dialog.w._f._pass2.getValue()) {
						Ext.MessageBox.show({
							buttons: Ext.MessageBox.OK,
							title: PC.i18n.error,
							msg: dialog.ln.passwords_doesnt_match,
							icon: Ext.MessageBox.WARNING,
							fn: function(btn_id) {
								dialog.w._f._pass1.focus();
								dialog.w._f._pass1.selectText();
							}
						});
						return;
					}
					
					var rqparams = {ajax: ''};
					//update
					rqparams.id = node.data.id;
					rqparams.email = node.data.email;
					//change email
					var new_email = dialog.w._f._email.getValue();
					if (new_email != node.data.email) {
						rqparams.new_email = new_email;
					}
					//change password
					var pass = dialog.w._f._pass1.getValue();
					if (pass) {
						rqparams.new_password = pass;
					}
					//change login
					var login = dialog.w._f._login.getValue();
					if (login != node.data.login) {
						rqparams.new_login = login;
					}
					//change name
					var name = dialog.w._f._name.getValue();
					if (name != node.data.name) {
						rqparams.new_name = name;
					}
					//change banned status
					var banned = dialog.w._f._banned.getValue();
					if (banned != node.data.banned) {
						rqparams.new_banned = banned;
					}
					Ext.Ajax.request({
						url: dialog.plugin_file,
						params: rqparams,
						method: 'POST',
						callback: function(opts, success, rspns) {
							if (success && rspns.responseText) {
								try {
									if (rspns.responseText == 'errors') {
										//alert('errors occured');
									}
									else {
										var data = Ext.decode(rspns.responseText);
										dialog.store.loadData(data);
										dialog.grid.selModel.selectRecords([dialog.store.getById(node.data.id)]);
										Ext.MessageBox.show({
											title: new_email,
											msg: dialog.ln.update.success,
											buttons: Ext.MessageBox.OK,
											icon: Ext.MessageBox.INFO
										});
										if (pass) {
											dialog.w._f._pass1.setValue('');
											dialog.w._f._pass2.setValue('');
										}
										return; // OK
									}
								} catch(e) {};
							}
							Ext.MessageBox.show({
								title: PC.i18n.error,
								msg: dialog.ln.update.error,
								buttons: Ext.MessageBox.OK,
								icon: Ext.MessageBox.ERROR
							});
						}
					});
				}
			},
			{	ref: '../../_btn_del',
				text: PC.i18n.del,
				iconCls: 'icon-delete',
				disabled: true,
				handler: function(b, e) {
					Ext.MessageBox.show({
						buttons: Ext.MessageBox.YESNO,
						title: dialog.ln._delete.confirmation,
						msg: dialog.ln._delete.confirm_message,
						icon: Ext.MessageBox.WARNING,
						maxWidth: 320,
						fn: function(btn_id) {
							if (btn_id == 'yes') {
								var node = dialog.grid.selModel.getSelected();
								Ext.Ajax.request({
									url: dialog.plugin_file,
									params: {
										ajax: '',
										'delete': node.data.email
									},
									method: 'POST',
									callback: function(opts, success, rspns) {
										if (success && rspns.responseText) {
											try {
												if (rspns.responseText != 'errors') {
													dialog.Clear_form();
													var data = Ext.decode(rspns.responseText);
													dialog.store.loadData(data);
													return; // OK
												}
											} catch(e) {};
										}
										Ext.MessageBox.show({
											title: PC.i18n.error,
											msg: dialog.ln._delete.error,
											buttons: Ext.MessageBox.OK,
											icon: Ext.MessageBox.ERROR
										});
									}
								});
							}
						}
					});
				}
			}
		]
	};
	var grd = dialog.grid;
	var stor = dialog.store;
	var add_fn = function() {
		//var sel = grd.getSelectionModel().getSelected();
		var sel = grd.selModel.getSelected();
		var rec = new stor.recordType({
			id: 0
		});
		var idx;
		//if (sel) {
		//	idx = stor.indexOf(sel);
		//	stor.insert(idx, rec);
		//} else {
			stor.add(rec);
			idx = stor.indexOf(rec);
		//}
		//grd.getSelectionModel().selectRow(idx);
		grd.selModel.selectRow(idx);
	};
	
	dialog.w = new PC.ux.Window({
		modal: true,
		title: PC.i18n.mod.site_users.selfname,
		width: 810,
		height: 400,
		layout: 'hbox',
		layoutConfig: {
			align: 'stretch'
		},
		items: [
			dialog.grid,
			dialog.form
		],
		buttons: [
			{	text: PC.i18n.close,
				handler: function() {
					dialog.w.close();
				}
			}
		],
		tbar: [
			{
				iconCls: 'icon-add',
				text: PC.i18n.add,
				handler: add_fn
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