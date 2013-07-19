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

//config
$plugin_name = basename(dirname(__FILE__));
$plugin_url = $cfg['url']['base'].$cfg['directories']['core_plugins_www'].'/'.$plugin_name.'/';
$plugin_file = $plugin_url.basename(__FILE__);

//api
if (isset($_POST['api']) || isset($_GET['api'])) {
	require("PC_api.php");
	exit;
}

$mod['name'] = 'Users & permissions';
$mod['onclick'] = 'mod_auth_click()';
$mod['priority'] = 10;
?>
<script type="text/javascript">
PC.utils.localize('mod.auth', {
	en: {
		title: 'Users & permissions',
		add: 'Add',
		delete_selected: 'Delete',
		form: {
			name: 'Name',
			password: 'Password',
			re_password: 'Confirm password',
			language: 'Language'
		},
		settings: 'Settings',
		permissions: 'Permissions',
		confirm: {
			delete_additional: 'Are you sure?'
		},
		positions: {
			title: 'Positions',
			position: 'Position',
			description: 'Description'
		},
		add_group: 'Add group',
		add_user: 'Add user',
		basic_information: 'Basic information',
		additional_fields: 'Additional fields',
		field: 'Field',
		value: 'Value',
		save: 'Save',
		cancel: 'Cancel',
		confirm_delete_msg: 'Are you sure you want to delete this item?',
		invalid_fields_found: 'Changes hasn\'t been saved because some fields were marked as invalid.<br />Please correct them and try again.',
		invalid: {
			url: 'Example: http://www.PC.com',
			mail: 'Example: info@PC.com',
			jabber: 'Example: user@PC.com/office'
		},
		chars_required: 'characters required'
	},
	
	ru: {
        title: 'Пользователи и разрешения',
        add: 'Добавить',
        delete_selected: 'Удалить',
        form: {
            name: 'Логин',
            password: 'Пароль',
            re_password: 'Подтверждение пароля',
            language: 'Язык'
        },
        settings: 'Установки',
        permissions: 'Права доступа',
        confirm: {
            delete_additional: 'Вы уверены?'
        },
        positions: {
            title: 'Позиции',
            position: 'Позиция',
            description: 'Описание'
        },
        add_group: 'Добавить группу',
        add_user: 'Добавить пользователя',
        basic_information: 'Основная информация',
        additional_fields: 'Дополнительная информация',
        field: 'Поле',
        value: 'Значение',
        save: 'Сохранить',
        cancel: 'Отмена',
        confirm_delete_msg: 'Вы уверены, что хотите удалить этот элемент?',
        invalid_fields_found: 'Изменения не сохранены т.к. некоторые поля были помечены как недопустимые.<br />Исправьте их и попробуйте заново.',
        invalid: {
            url: 'Пример: http://www.PC.com',
            mail: 'Пример: info@PC.com',
            jabber: 'Пример: user@PC.com/office'
        },
        chars_required: 'необходимо символов'
    },

	
	lt: {
		title: 'Vartotojai ir teisės',
		add: 'Add',
		delete_selected: 'Delete',
		form: {
			name: 'Name',
			password: 'Password',
			re_password: 'Confirm password',
			language: 'Language'
		},
		settings: 'Settings',
		permissions: 'Permissions',
		confirm: {
			delete_additional: 'Are you sure?'
		},
		positions: {
			title: 'Positions',
			position: 'Position',
			description: 'Description'
		},
		add_group: 'Add group',
		add_user: 'Add user',
		basic_information: 'Basic information',
		additional_fields: 'Additional fields',
		field: 'Field',
		value: 'Value',
		save: 'Save',
		cancel: 'Cancel',
		confirm_delete_msg: 'Are you sure you want to delete this item?',
		invalid_fields_found: 'Changes hasn\'t been saved because some fields were marked as invalid.<br />Please correct them and try again.',
		invalid: {
			url: 'Example: http://www.PC.com',
			mail: 'Example: info@PC.com',
			jabber: 'Example: user@PC.com/office'
		},
		chars_required: 'characters required'
	}
});

Ext.ns('PC.plugins');

function mod_auth_click() {
	PC.plugin.auth.dialog = {};
	var dialog = PC.plugin.auth.dialog;
	var ln = PC.i18n.mod.auth;
	dialog.ln = ln;
	
	dialog.plugin_file = '<?php echo $plugin_file; ?>';
	
	dialog.user = {
		Load: function(id, success_callback, failure_callback) {
			if (typeof success_callback != 'function') return false;
			Ext.Ajax.request({
				url: dialog.plugin_file +'?action=get_user',
				params: {api: true, id: id},
				callback: function(options, success, response){
					if (success && response.responseText) {
						try {
							var data = Ext.decode(response.responseText);
							success_callback(data);
							return;
						} catch(e) {console.log(e);};
					}
					if (typeof failure_callback == 'function') failure_callback(response);
				}
			});
		},
		Delete: function(id, config) {
			Ext.Ajax.request({
				url: dialog.plugin_file +'?action=delete_user',
				params: {api: true, id: id},
				callback: function(options, success, response){
					if (success && response.responseText) {
						try {
							var data = Ext.decode(response.responseText);
							if (data.success) {
								if (typeof config.success == 'function') config.success();
							}
							return;
						} catch(e) {console.log(e);};
					}
					if (typeof config.failure == 'function') config.failure();
				}
			});
		}
	};
	dialog.group = {
		Load: function(id, success_callback, failure_callback) {
			if (typeof success_callback != 'function') return false;
			Ext.Ajax.request({
				url: dialog.plugin_file +'?action=get_group',
				params: {api: true, id: id},
				callback: function(options, success, response){
					if (success && response.responseText) {
						try {
							var data = Ext.decode(response.responseText);
							success_callback(data);
							return;
						} catch(e) {console.log(e);};
					}
					if (typeof failure_callback == 'function') failure_callback(response);
				}
			});
		},
		Create: function(config) {
			Ext.Ajax.request({
				url: dialog.plugin_file +'?action=create_group',
				params: {api: true, name: config.name},
				callback: function(options, success, response){
					if (success && response.responseText) {
						try {
							var data = Ext.decode(response.responseText);
							if (data.success) {
								if (typeof config.success == 'function') config.success(data.data, response);
								return;
							}
						} catch(e) {};
					}
					if (typeof config.failure == 'function') config.failure();
				}
			});
		},
		Delete: function(id, config) {
			Ext.Ajax.request({
				url: dialog.plugin_file +'?action=delete_group',
				params: {api: true, id: id},
				callback: function(options, success, response){
					if (success && response.responseText) {
						try {
							var data = Ext.decode(response.responseText);
							if (data.success) {
								if (typeof config.success == 'function') config.success();
							}
							return;
						} catch(e) {console.log(e);};
					}
					if (typeof config.failure == 'function') config.failure();
				}
			});
		}
	};
	
	dialog.treeloader = new Ext.tree.TreeLoader({
		dataUrl: dialog.plugin_file +'?action=get_tree',
		baseParams: {api: true}
	});
	
	dialog.tree = new Ext.tree.TreePanel({
		region: 'west',
		width: 300,
		split: true,
		border: true,
		enableSort: false,
		loader: dialog.treeloader,
		root: {
			nodeType: 'async',
			text: ln.title,
			draggable: false,
			id: '0'
		},
		padding: 3,
		rootVisible: false,
		enableDD: true,
		hlDrop: false,
		ddGroup: 'auth_tree',
		dropConfig: {
			ddGroup: 'auth_tree',
			expandDelay: 1000
		},
		/*
		tbar: [
			{	text: ln.add_group,
				icon: 'images/icons/folder_add.png',
				handler: function(){
					
				}
			},
			{	text: ln.add_user,
				icon: 'images/icons/user_add.png',
				handler: function(){
					var selNode = dialog.tree.selModel.getSelectedNode();
					if (!selNode) return false;
					var type = dialog.tree._getNodeType(selNode.attributes.id);
					if (type == 'user') {
						selNode = selNode.parentNode;
						var type = dialog.tree._getNodeType(selNode.attributes.id);
					}
					if (type != 'group') return false;
					var parent = selNode.attributes.id;
					var appendChild = function(){
						var newNode = selNode.appendChild(new Ext.tree.TreeNode({
							icon: 'images/user.png',
							leaf: true, disabled: true
						}));
						if (!newNode) return;
						//newNode.ui = newNode.getUI();
						dialog.tree._createUser({
							parent: parent,
							success: function(data, response){
								newNode.enable();
								newNode.setId(data.id);
								//newNode.setText(data.text);
								/* TODO: open user management form * /
							},
							failure: function(){
								dialog.treeEditor.editNode.destroy();
								/* TODO: show error * /
							}
						});
					};
					if (!selNode.expanded) selNode.expand(false, true, appendChild);
					else appendChild();
				}
			}
		],
		bbar: [
			{	text: 'Delete',
				icon: 'images/delete.png',
				handler: function(){
					Ext.MessageBox.show({
						title: PC.i18n.msg.title.confirm,
						msg: ln.confirm_delete_msg,
						buttons: Ext.MessageBox.YESNO,
						icon: Ext.MessageBox.QUESTION,
						fn: function(r) {
							if (r == 'yes') {
								Ext.MessageBox.show({
									title: PC.i18n.msg.title.loading,
									msg: PC.i18n.msg.loading,
									width: 300,
									wait: true,
									waitConfig: {interval:100}
								});
								var node = dialog.tree.selModel.getSelectedNode();
								if (!node) {
									Ext.Msg.hide();
									return;
								}
								if (!node.attributes.id) {
									Ext.Msg.hide();
									return;
								}
								Ext.Ajax.request({
									url: dialog.plugin_file +'?action=delete_item',
									method: 'POST',
									params: {
										api: true,
										id: node.attributes.id
									},
									callback: function(opts, success, response) {
										if (success && response.responseText) {
											try {
												var data = Ext.decode(response.responseText);
												if (data.success) {
													node.destroy();
													Ext.Msg.hide();
													return;
												}
												else var error = data.error;
											} catch(e) {
												console.log(e);
												var error = 'Invalid JSON data returned.';
											};
										}
										else {
											var error = 'Connection error.';
										}
										Ext.MessageBox.show({
											title: PC.i18n.error,
											msg: (error?'<b>'+ error +'</b><br />':'') +'Position was not deleted.',
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
		],
		*/
		listeners: {
			containerclick: function(tree, event){
				tree.selModel.clearSelections();
				dialog._switchForm('all', true);
			},
			containercontextmenu: function(tree, event){
				dialog.tree._context._node = null;
				tree._context.container.showAt(event.getXY());
			},
			contextmenu: function(node, event){
				dialog.tree._context._node = node;
				var type = dialog.tree._getNodeType(node.attributes.id);
				if (dialog.tree._context[type] != undefined) {
					if (type == 'user') {
						if (node.attributes.text == PC.global.user) {
							dialog.tree._actions.delete_user.disable();
						}
						else dialog.tree._actions.delete_user.enable();
					}
					else dialog.tree._actions.delete_user.enable();
					
					if (typeof dialog.tree._context[type].showAt == 'function') {
						dialog.tree._context[type].showAt(event.getXY());
					}
				}
			},
			beforeappend: function(tree, parent, node){
				
			},
			click: function(node, event){
				var type = dialog.tree._getNodeType(node.attributes.id);
				dialog._switchForm(type, true);
				dialog.window._form._node = node;
				if (type == 'group') {
					dialog.form._loadGroup(node.attributes.id);
				}
				else dialog.form._loadUser(node.attributes.id);
			},
			nodedragover: function(drop) {
				//cancel drop if trying to drag user node to the root
				var nodeType = drop.dropNode.attributes.type;
				var targetType = drop.target.attributes.type;
				if (nodeType == 'group' || targetType == 'user') return false;
				
				if (drop.target.parentNode.attributes.id == 0 && drop.point != 'append') return false;
				
				if (drop.point == 'above' || drop.point == 'below') return false;
			},
			beforenodedrop: function(drop) {
				drop.dropNode._oldparent = drop.dropNode.parentNode;
				drop.dropNode._oldnsib = drop.dropNode.nextSibling; //old nextSibling
				/*switch (drop.point) {
					case 'append': var anchor = 0; break;
					case 'below': 
						if (drop.target.nextSibling == null) var anchor = 0;
						else var anchor = drop.target.nextSibling.attributes.id;
						break;
					case 'above': var anchor = drop.target.attributes.id; break;
					default: return false;
				}
				drop.dropNode._anchor = anchor;*/
			},
			nodedrop: function(drop) {
				var newParent = drop.target;
				var params = {
					api: true,
					id: drop.dropNode.attributes.id,
					group_id: newParent.attributes.id
				};
				Ext.Ajax.request({
					url: dialog.plugin_file +'?action=change_user_group',
					params: params,
					callback: function(options, success, response){
						if (success && response.responseText) {
							try {
								var data = Ext.decode(response.responseText);
								if (data.success) {
									//SUCCESS
									return;
								}
							} catch(e) {};
						}
						//FAILURE
						drop.dropNode._oldparent.insertBefore(drop.dropNode, drop.dropNode._oldnsib);
					}
				});
			}
		},
		_getNodeType: function(id){
			if (id == undefined) return false;
			return (/^group_[0-9]+$/.test(id)?'group':(/^[0-9]+$/.test(id)?'user':'unknown'));
		},
		_parseNodeType: function(id, if_group, if_user, if_unknown){
			var type = this._getNodeType(id);
			if (!type) return false;
			var callback;
			if (type == 'group') callback = if_group;
			else if (type == 'user') callback = if_user;
			else callback = if_unknown;
			if (typeof callback == 'function') callback();
		},
		_getSelectedNodeID: function(){
			var selNode = dialog.tree.selModel.getSelectedNode();
			if (!selNode) return false;
			if (selNode.attributes.id != undefined) return selNode.attributes.id;
			return false;
		},
		_selectedNodeIsGroup: function(){
			var id = this._getSelectedNodeID();
			if (!id) return false;
			var type = dialog.tree._getNodeType(id);
			if (type == 'group') return true;
			return false;
		},
		_createUser: function(config){
			var type = this._getNodeType(config.parent);
			if (type != 'group') {
				if (typeof config.failure == 'function') config.failure();
				return;
			}
			Ext.Ajax.request({
				url: dialog.plugin_file +'?action=add_user',
				params: {api: true, parent: config.parent, name: 'New user'},
				callback: function(options, success, response){
					if (success && response.responseText) {
						try {
							var data = Ext.decode(response.responseText);
							if (data.success) {
								if (typeof config.success == 'function') config.success(data, response);
								return;
							}
						} catch(e) {};
					}
					if (typeof config.failure == 'function') config.failure();
				}
			});
		}
	});
	
	dialog.tree._actions = {
		create_group: new Ext.Action({
			text: 'Create group',
			icon: 'images/add.png',
			handler: function(){
				var rootNode = dialog.tree.getRootNode();
				var newNode = rootNode.appendChild(new Ext.tree.TreeNode({
					type: 'group',
					draggable: false,
					icon: 'images/group.png',
					expanded: true, disabled: true
				}));
				if (!newNode) return;
				//newNode.ui = newNode.getUI();
				dialog.treeEditor.editNode = newNode;
				dialog.treeEditor._create = function(name){
					dialog.group.Create({
						name: name,
						success: function(data, response) {
							newNode.enable();
							newNode.setId('group_'+ data.id);
							/* TODO: open group management form */
						},
						failure: function() {
							newNode.destroy();
						}
					});
				};
				dialog.treeEditor.startEdit(newNode.ui.textNode);
			}
		}),
		create_user: new Ext.Action({
			text: 'Create user',
			icon: 'images/add.png',
			handler: function(){
				var target = dialog.tree._context._node;
				var type = dialog.tree._getNodeType(target.attributes.id);
				if (type == 'group') {
					var anchor = target;
					//create child
				}
				else if (type == 'user') {
					//create sibling
					var anchor = target.parentNode;
				}
				else return false;
				
				var newNode = anchor.appendChild(new Ext.tree.TreeNode({
					type: 'user',
					icon: 'images/user.png',
					text: '<i>New user</i>',
					leaf: true, disabled: true
				}));
				
				if (!newNode) return;
				
				newNode.select();
				
				dialog._switchForm('user', true, {
					closeCallback: function() {
						newNode.destroy();
					},
					successCallback: function(data) {
						newNode.enable();
					}
				});
				dialog.window._form._node = newNode;
			}
		}),
		delete_user: new Ext.Action({
			text: 'Delete user',
			icon: 'images/delete.png',
			handler: function(){
				Ext.MessageBox.show({
					title: PC.i18n.msg.title.confirm,
					msg: 'Ar tikrai?',
					buttons: Ext.MessageBox.YESNO,
					icon: Ext.MessageBox.QUESTION,
					fn: function(r) {
						if (r == 'yes') {
							var node = dialog.tree._context._node;
							dialog.user.Delete(node.id, {
								success: function() {
									if (dialog.window._form._node) if (dialog.window._form._node.id == node.id) {
										dialog._switchForm('all', true);
									}
									node.destroy();
								},
								failure: function() {
									alert('error');
								}
							});
						}
					}
				});
			}
		}),
		delete_group: new Ext.Action({
			text: 'Delete group',
			icon: 'images/delete.png',
			handler: function(){
				Ext.MessageBox.show({
					title: PC.i18n.msg.title.confirm,
					msg: 'Ar tikrai? Nes visi vidiniai vartotojai taip pat pradings!',
					buttons: Ext.MessageBox.YESNO,
					icon: Ext.MessageBox.QUESTION,
					fn: function(r) {
						if (r == 'yes') {
							var node = dialog.tree._context._node;
							dialog.group.Delete(node.id, {
								success: function() {
									if (dialog.window._form._node) if (dialog.window._form._node.id == node.id || dialog.window._form._node.parentNode.id == node.id) {
										dialog._switchForm('all', true);
									}
									node.destroy();
								},
								failure: function() {
									alert('error');
								}
							});
						}
					}
				});
			}
		})
	};
	
	dialog.tree._context = {
		//contextmenu node
		_node: null,
		//menu objects
		container: new Ext.menu.Menu({
			items: [
				dialog.tree._actions.create_group
			]
		}),
		user: new Ext.menu.Menu({
			items: [
				dialog.tree._actions.create_user,
				dialog.tree._actions.delete_user
			]
		}),
		group: new Ext.menu.Menu({
			items: [
				dialog.tree._actions.create_group,
				dialog.tree._actions.create_user,
				dialog.tree._actions.delete_group
			]
		})
	};
	
	dialog.treeEditor = new Ext.tree.TreeEditor(dialog.tree, {}, {
		editDelay: 350,
		_create: null,
		_edit: null,
		listeners: {
			beforestartedit: function(editor, el, value){
				if (!editor.editNode.disabled) return false;
			},
			canceledit: function(editor, value, start_value){
				if (editor.editNode.disabled && start_value == '') {
					editor.editNode.destroy();
				}
			},
			beforecomplete: function(editor, value, start_value){
				if (editor.editNode.disabled) {
					if (value == '') {
						editor.editNode.destroy();
						return;
					}
					if (typeof editor._create == 'function') editor._create(value);
				}
				//edit existing node
				else {
					if (start_value == value) {
						return;
					}
					if (typeof editor._edit == 'function') editor._edit(value, start_value);
				}
				editor._create = editor._edit = null;
			}
		}
	});
	
	dialog.GetVisibilityField = function(config, allowInheritance){
		if (allowInheritance == undefined) allowInheritance = true;
		var data = [
			['on', '1'],
			['hidden', '1'],
			['off', '1']
		];
		if (allowInheritance) {
			data.unshift(['inherit', '1']);
		}
		var field = {
			ref: '_visibility',
			width: 100,
			xtype: 'combo', mode: 'local',
			store: {
				xtype: 'arraystore',
				fields: ['value', 'display'],
				idIndex: 0,
				data: data
			},
			valueField: 'value',
			displayField: 'display',
			value: 'inherit',
			forceSelection: true,
			triggerAction: 'all',
			editable: false,
			listeners: {
				change: function(visibility_field, value, ovalue) {
					//pakeisti spalva i gray ar panasiai, kad btu lengva atskirti kurie yra public laukeliai
				},
				select: function(cb, rec, idx) {
					cb.fireEvent('change', cb, cb.value, cb.originalValue);
				}
			}
		};
		if (typeof config == 'object' && config != null) {
			Ext.applyIf(config, field);
			return config;
		}
		return field;
	}
	
	dialog.Create_form_field = function(ref, field){
		Ext.applyIf(field, {
			flex: 1,
			hidden: true,
			ref: ref
		});
		return field;
	}
	
	dialog.form = {
		ref: '../_form',
		layout: 'form',
		//region: 'center',
		//padding: 6,
		autoScroll: true,
		flex: 1,
		bodyCssClass: 'x-border-layout-ct',
		border: false,
		labelWidth: 120,
		labelAlign: 'right',
		defaults: {xtype: 'textfield', anchor: '100%'},
		items: [
			//name
			dialog.Create_form_field('_name', {fieldLabel: ln.form.name, validator: function(value){
				if (dialog.tree._selectedNodeIsGroup()) {
					//group validator
				}
				else {
					//user validator
				}
			}}),
			//password
			dialog.Create_form_field('_password', {
				fieldLabel: ln.form.password,
				inputType: 'password'
			}),
			//re-password
			dialog.Create_form_field('_re_password', {
				fieldLabel: ln.form.re_password,
				inputType: 'password'
			}),
			//language
			dialog.Create_form_field('_language', {
				fieldLabel: ln.form.language,
				xtype: 'combo',
				mode: 'local',
				store: {
					xtype: 'arraystore',
					fields: ['value', 'display'],
					idIndex: 0,
					data: getLanguageStore(true)
				},
				displayField: 'display',
				valueField: 'value',
				value: '',
				forceSelection: true,
				triggerAction: 'all',
				editable: false
			})
		],
		_node: null,
		_loadUserData: function(d){
			var form = dialog.window._form;
			form._name.setValue(d.username);
			//form._password.setValue(d.secondname);
			//form._re_password.setValue(d.lastname);
			form._language.setValue(d.language);
			if (typeof d.permissions.length != 'undefined') d.permissions = {};
			dialog.permissions._loaded.data = d.permissions;
			dialog.permissions.view.refresh();
		},
		_loadUser: function(id){
			dialog.user.Load(id, function(d){
				dialog.form._loadUserData(d);
			});
		},
		_loadGroupData: function(d){
			var form = dialog.window._form;
			form._name.setValue(d.groupname);
			/*form._address.setValue(d.address);
			form._url.setValue(d.url);
			form._mail.setValue(d.mail);
			form._status.setValue(d.status);
			dialog.permissions.store.loadData(d.additional);*/
		},
		_loadGroup: function(id){
			dialog.group.Load(id, function(d){
				dialog.form._loadGroupData(d);
			});
		},
		_getFields: function(type, getComponents){
			switch (type){
				case 'group':
					var fields = ['name'];
					break;
				case 'user':
					var fields = ['name','password','re_password','language'];
					break;
				default: var fields = ['name','password','re_password','language'];
			}
			if (!getComponents) return fields;
			var form = dialog.window._form;
			var relations = {
				'name': form._name,
				'password': form._password,
				're_password': form._re_password,
				'language': form._language
			};
			var fieldComponents = {};
			Ext.iterate(fields, function(field){
				fieldComponents[field] = relations[field];
			});
			return fieldComponents;
		},
		_clear: function(keep_selection){
			if (keep_selection == undefined) keep_selection = false;
			var form = dialog.window._form;
			form._name.setValue('');
			form._password.setValue('');
			form._re_password.setValue('');
			form._language.setValue('');
			//dialog.permissions.store.loadData([]);
			if (!keep_selection) form._node = null;
		},
		_formType: 'all'
	};
	
	dialog._switchForm = function(type, clear, create_mode_config){
		var form = dialog.window._form;
		
		if (form._createMode) if (form._createModeConfig) if (typeof form._createModeConfig.closeCallback == 'function') {
			form._createModeConfig.closeCallback();
		}
		
		if (clear != undefined) if (clear === true) form._clear();
		
		var all = form._getFields();
		switch (type){
			case 'group':
				form._formType = 'group';
				var visible = form._getFields('group');
				visible.unshift('');
				dialog.permissions.disable();
				break;
			case 'user':
				form._formType = 'user';
				var visible = form._getFields('user');
				visible.unshift('');
				dialog.permissions.enable();
				//dialog.permissions.view.refresh();
				break;
			default:
				form._formType = 'all';
				var visible = [];//form._getFields();
				visible.unshift('');
				dialog.permissions.disable();
				/* TODO: hide form and show tip message */
		}
		Ext.iterate(all, function(field){
			if (form['_'+field] == undefined) return;
			var fld = form['_'+field];
			if (visible.has(field)) {
				fld.show();
			}
			else fld.hide();
		});
		//create mode
		form._createMode = (create_mode_config?true:false); //force bool type
		if (form._createMode) {
			form._createModeConfig = create_mode_config;
		}
		else form._createModeConfig = null;
	};
	
	/*dialog.permissions_fields_store = new Ext.data.JsonStore({
		autoLoad: true,
		url: dialog.plugin_file +'?action=get_additional_fields',
		baseParams: {api: true},
		fields: ['plugin', 'name', 'data'],
		idProperty: 'id'
	});
	
	dialog.GetAdditionalField = function(config){
		var field = {
			xtype: 'combo', mode: 'local',
			store: dialog.permissions_fields_store,
			valueField: 'plugin',
			displayField: 'name',
			triggerAction: 'all',
			editable: false,
			listeners: {
				change: function(field, value, ovalue) {
					//
				},
				select: function(cb, rec, idx) {
					cb.fireEvent('change', cb, cb.value, cb.originalValue);
				}
			}
		};
		if (typeof config == 'object' && config != null) Ext.applyIf(field, config);
		return field;
	}*/
	
	dialog.permissions = new Ext.grid.EditorGridPanel({
		disabled: true,
		ref: '../_permissions',
		bodyCssClass: 'x-border-layout-ct',
		store: new Ext.data.GroupingStore({
			//url: 'ajax.gallery.php?action=get_thumbnail_types',
			reader: new Ext.data.ArrayReader({
				fields: [
					'plugin',
					'name',
					{	name: 'localized_name',
						mapping: 'name',
						convert: function(value, data){
							var types_ln = PC.i18n.auth.permissions.types;
							if (types_ln[data[0]] == undefined) return data[1];
							if (types_ln[data[0]][data[1]] == undefined) return data[1];
							if (types_ln[data[0]][data[1]].title == undefined) return data[1];
							return types_ln[data[0]][data[1]].title;
						}
					},
					'description',
					{	name: 'localized_description',
						mapping: 'description',
						convert: function(value, data){
							var types_ln = PC.i18n.auth.permissions.types;
							if (types_ln[data[0]] == undefined) return PC.i18n.no_description;
							if (types_ln[data[0]][data[1]] == undefined) return PC.i18n.no_description;
							if (types_ln[data[0]][data[1]].description == undefined) return PC.i18n.no_description;
							return types_ln[data[0]][data[1]].description;
						}
					}
				]
			}),
			groupField: 'plugin',
			sortInfo: {
				field: 'plugin',
				direction: 'ASC'
			},
			listeners: {
				load: function(store, records, options){
					store.commitChanges();
					store._deletedFields = [];
				},
				remove: function(store, record, index){
					if (record.data.id != null) store._deletedFields.push(record);
				}
			},
			_deletedFields: [],
			_getDeletedFields: function(){
				return this._deletedFields;
			}
		}),
		view: new Ext.grid.GroupingView({
			forceFit: true,
			groupTextTpl: '<tpl if="gvalue==\'\'">Core</tpl><tpl if="gvalue!=\'\'">{gvalue} plugin</tpl>'
		}),
		border: false,
		height: 350,
		style: 'border: 2px solid #D2DCEB;',
		_loaded: {
			data: {},
			Set: function(plugin, name, data) {
				if (typeof data == 'object') {
					//decode data
					var data = Ext.encode(data);
					if (!data) return false;
				}
				else if (typeof data != 'string') return false;
				if (this.data[plugin] == undefined) {
					this.data[plugin] = {};
				}
				this.data[plugin][name] = data;
				return true;
			},
			Get: function(plugin, name, is_json) {
				var default_return = (is_json?{}:'');
				if (typeof this.data[plugin] == 'undefined') {
					return default_return;
				}
				else if (typeof this.data[plugin][name] != 'string') {
					return default_return;
				}
				if (!this.data[plugin][name].length) return default_return;
				//normal string (boolean)
				if (!is_json) return this.data[plugin][name];
				//json
				var data = Ext.decode(this.data[plugin][name]);
				if (!data) return {};
				return data;
			}
		},
		_getSelectedRecord: function() {
			var cell = dialog.permissions.selModel.getSelectedCell();
			if (!cell) return false;
			var r = dialog.permissions.store.getAt(cell[0]);
			return r;
		},
		columns: [
			{	dataIndex: 'plugin',
				hidden: true
			},
			{	dataIndex: 'name',
				hidden: true
			},
			{	header: 'Name',
				width: 50,
				dataIndex: 'localized_name'
			},
			{	header: 'Description',
				id: 'pc_auth_description_col',
				dataIndex: 'localized_description'
			},
			{	header: 'Config.',
				width: 20,
				editor: {
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['value', 'display'],
						data: [['0','Off'],['1','On']]
					},
					displayField: 'display',
					valueField: 'value',
					editable: false,
					forceSelection: true,
					value: '',
					triggerAction: 'all',
					listeners: {
						beforeshow: function(cb){
							var r = dialog.permissions._getSelectedRecord();
							if (!r) return false;
							var plugin = r.data.plugin;
							if (PC.auth.perms.editors.Has(plugin, r.data.name)) {
								var cell = dialog.permissions.selModel.getSelectedCell();
								if (cell) dialog.permissions.colModel.getCellEditor(cell[1],cell[0]).cancelEdit();
								return false;
							}
							return true;
						},
						blur: function(field) {
							var r = dialog.permissions._getSelectedRecord();
							if (!r) return false;
							var data = field.getValue()
							dialog.permissions._loaded.Set(r.data.plugin, r.data.name, data);
							dialog.permissions.view.refresh();
						}/*,
						select: function(cb, record, index) {
							var cell = dialog.permissions.selModel.getSelectedCell();
							if (cell) dialog.permissions.colModel.getCellEditor(cell[1],cell[0]).completeEdit();
							dialog.permissions.view.refresh();
						}*/
					}
				},
				renderer: function(value, metaData, r, rowIndex, colIndex, store) {
					var plugin = r.data.plugin;
					if (PC.auth.perms.editors.Has(plugin, r.data.name)) {
						var editor = PC.auth.perms.editors.Get(plugin, r.data.name);
						//edit
						var id = Ext.id();
						
						
						var callback_ok = function(window) {
							//handler
							var perm_data = window.editor.data.Get(window);
							if (typeof perm_data == 'object' && perm_data != null) {
								if (dialog.permissions._loaded.Set(r.data.plugin, r.data.name, perm_data)) {
									window.close();
									return;
								}
							}
							Ext.MessageBox.show({
								title: PC.i18n.error,
								msg: 'Error while validating changes',
								buttons: Ext.MessageBox.OK,
								icon: Ext.MessageBox.ERROR
							});
						}
						
						var button = new Ext.Button({
							text: '<img src="images/pencil.png" alt="" />',
							style: 'margin-top:-3px;margin-left:-3px;margin-bottom:-2px;',
							handler: function(){
								//decode permission data
								var perm_data = dialog.permissions._loaded.Get(r.data.plugin, r.data.name, true);
								//construct window
								var window_config = {
									title: 'Permissions editor',
									width: 500, maxHeight: 600,
									listeners: {
										afterrender: function(window) {
											if (window.getHeight() > window.maxHeight) {
												window.setHeight(window.maxHeight);
											}
											window.center();
										}
									},
									autoScroll: true,
									layout: 'form',
									bodyCssClass: 'x-border-layout-ct',
									padding: 5,
									border: false,
									flex: 1,
									labelWidth: 120,
									labelAlign: 'right',
									//defaults: {xtype: 'textfield', anchor: '100%'},
									layoutConfig: {
										align: 'stretch'
									},
									buttonAlign: 'right',
									buttons: [
										{	text: Ext.Msg.buttonText.ok,
											handler: function() {
												callback_ok(window);
											}
										},
										{	text: Ext.Msg.buttonText.cancel,
											handler: function() {
												window.close();
											}
										}
									],
									editor: editor
								};
								
								var window = false;
								var show_window_after_data_load = false;
								var hook_name = 'core/auth/get_perm_window/' + plugin;
								if (PC.hooks.Count(hook_name) > 0) {
									var params = {};
									PC.hooks.Init(hook_name, params);
									if (params.show_window_after_data_load) {
										show_window_after_data_load = true;
									}
								}
								if (!window) {
									var custom_config = editor.window.Get(perm_data);
									//apply custom config
									Ext.apply(window_config, custom_config);
									//show window and load data
									window = new Ext.Window(window_config);
								}
								
								if (!window.isVisible() && !show_window_after_data_load) {
									window.show();
								}
								editor.data.Load(window, perm_data);
								if (!window.isVisible() && show_window_after_data_load) {
									window.show();
								}
							}
						});
						//render
						setTimeout(function(){
							button.render(Ext.get(id));
						}, 10);
						return '<div id="'+ id +'"></div>';
					}
					else {
						var active = (dialog.permissions._loaded.Get(r.data.plugin, r.data.name, false)==='1');
						var id = Ext.id();
						var button = new Ext.Button({
							text: '<img src="images/'+ (active?'tick':'delete') +'.png" alt="" />',
							style: 'margin-top:-3px;margin-left:-3px;margin-bottom:-2px;',
							//enableToggle: true,
							//pressed: active,
							handler: function(button, event){
								var active = (dialog.permissions._loaded.Get(r.data.plugin, r.data.name, false)==='1');
								dialog.permissions._loaded.Set(r.data.plugin, r.data.name, (!active?'1':'0'));
								button.setText('<img src="images/'+ (!active?'tick':'delete') +'.png" alt="" />');
								//dialog.permissions.view.refresh();
							}
						});
						//render
						setTimeout(function(){
							button.render(Ext.get(id));
						}, 10);
						return '<div id="'+ id +'"></div>';
					}
					/*
					var data = dialog.permissions._loaded.Get(r.data.plugin, r.data.name);
					if (data === '1') {
						return '<span style="color:green">On</span>';
					}
					return '<span style="color:red">Off</span>';
					*/
				}
			}
		],
		autoExpandColumn: 'pc_auth_description_col'
		/*bbar: [
			{	xtype: 'button', icon: 'images/delete.png',
				text: ln.delete_selected,
				handler: function(){
					var cell = dialog.permissions.selModel.getSelectedCell();
					if (cell == null) return;
					Ext.MessageBox.show({
						title: PC.i18n.msg.title.confirm,
						msg: ln.confirm.delete_additional,
						buttons: Ext.MessageBox.YESNO,
						icon: Ext.MessageBox.QUESTION,
						fn: function(r) {
							if (r == 'yes') {
								return dialog.permissions.store.removeAt(cell[0]);
							}
						}
					});
				}
			},
			{xtype:'tbseparator'},
			dialog.GetAdditionalField({ref: '../_field_to_add'}),
			{	xtype: 'button', icon: 'images/add.png',
				text: ln.add,
				handler: function(){
					var id = dialog.window._additional._field_to_add.getValue();
					var record = dialog.window._additional._field_to_add.store.getById(id);
					if (!record) return;
					var record = new dialog.permissions.store.recordType({
						id: null,
						field_id: id,
						value: '',
						status: 'inherit'
					});
					record.markDirty();
					dialog.permissions.store.add(record);
				}
			}
		]*/
		/*
		selModel: new Ext.grid.RowSelectionModel({
			moveEditorOnEnter: false,
			singleSelect: true,
			listeners: {
				selectionchange: function(sm) {
					var count = sm.getCount();
					//enable/disable controls
					if (count) dialog.answers._delete.enable();
					else dialog.answers._delete.disable();
				}
			}
		})*/
	});
	
	dialog.aes = {
		_bits2keylen: {128:16, 192:24, 256:32},
		_hex2nibble: {0:0,1:1,2:2,3:3,4:4,5:5,6:6,7:7,8:8,9:9,'A':10,'B':11,'C':12,'D':13,'E':14,'F':15},
		_nibble2hex: {0:0,1:1,2:2,3:3,4:4,5:5,6:6,7:7,8:8,9:9,10:'a',11:'b',12:'c',13:'d',14:'e',15:'f'},
		encrypt_cbc: function(key_hex, iv_arr, data_str, bits) {
			var keylen = 32;
			if (bits && this._bits2keylen[bits]) keylen = this._bits2keylen[bits];
			var key = this._hex2binArray(key_hex, keylen);
			var iv = iv_arr.slice(0);
			var o = [];
			AES_Init();
			AES_ExpandKey(key);
			for (var i=0; i<data_str.length; i+=16) {
				var block = this._str2binArray(data_str.substr(i, 16), 16);
				for (var j=0; j<16; j++)
					block[j] = block[j] ^ iv[j];
				AES_Encrypt(block, key);
				o = o.concat(block);
				iv = block;
			}
			AES_Done();
			return o;
		},
		encrypt_cbc_hex: function(key_hex, iv_arr, data_str, bits) {
			return this._binArray2hex(this.encrypt_cbc(key_hex, iv_arr, data_str, bits));
		},
		generate_iv: function() {
			var o = [];
			for (var i=0; i<16; i++)
				o.push(Math.floor(Math.random()*256));
			return o;
		},
		hexpad256: function(hex) {
			hex = hex.toLowerCase();
			while (hex.length < 64)
				hex = '0'+hex;
			return hex;
		},
		_hex2binArray: function(hex, len) {
			hex = hex.replace(/[^0-9a-f]/ig, '').toUpperCase();
			if (hex.length % 2) hex='0'+hex;
			if (len && (hex.length > len*2))
				hex = hex.substr(0, len*2);
			var o = [];
			for (var i=0; i<hex.length; i+=2)
				o.push(this._hex2nibble[hex.charAt(i)]*16 + this._hex2nibble[hex.charAt(i+1)]);
			if (len)
				while (o.length < len)
					o.push(0);
			return o;
		},
		_str2binArray: function(str, len) {
			if (len && (str.length > len))
				str = str.substr(0, len);
			var o = [];
			for (var i=0; i<str.length; i++)
				o.push(str.charCodeAt(i));
			if (len)
				while (o.length < len)
					o.push(0);
			return o;
		},
		_binArray2hex: function(bin) {
			var o = '';
			for (var i=0; i<bin.length; i++)
				o = o + this._nibble2hex[bin[i]>>>4] + this._nibble2hex[bin[i]%16];
			return o;
		}
	};
	
	dialog.settings = {
		xtype: 'panel',
		region: 'center',
		layout: {
			type: 'vbox',
			align : 'stretch'
		},
		border: false,
		bodyCssClass: 'x-border-layout-ct',
		padding: 6,
		items: [
			{	html: '<img style="vertical-align:-3px;" src="images/pencil.png" alt="" /> '+ln.settings,
				border: false,
				bodyCssClass: 'x-border-layout-ct',
				style: 'font-size:10pt;',
				height: 30
			},
			dialog.form,
			{	html: '<img style="vertical-align:-3px;" src="images/pencil.png" alt="" /> '+ln.permissions,
				border: false,
				bodyCssClass: 'x-border-layout-ct',
				style: 'font-size:10pt;',
				height: 25
			},
			dialog.permissions
		],
		buttonAlign: 'center',
		buttons: [
			{	text: ln.save,
				icon: 'images/disk.png',
				ref: '_send',
				handler: function(b, e) {
					var form = dialog.window._form;
					
					//define error callback (so we would be able to remove fictive nodes if create action is unsuccessful)
					function _false() {
						if (form._createMode) {
							if (typeof form._createModeConfig.errorCallback == 'function') {
								form._createModeConfig.errorCallback();
								dialog._switchForm('all', true);
							}
						}
						return false;
					}
					function _true(data) {
						if (form._createMode) {
							if (typeof form._createModeConfig.successCallback == 'function') {
								form._createModeConfig.successCallback(data);
								//exit from create-mode
								form._createMode = false;
							}
						}
						return true;
						
					}
					if (form._node == null) return _false();
					//var type = dialog.tree._getNodeType(form._node.attributes.id);
					var type = form._node.attributes.type;
					if (type != 'group' && type != 'user') return _false();
					
					b.disable();
					
					var params = {api: true};
					if (!form._createMode) params.id = form._node.attributes.id;
					var perms = [];
					
					//collect type specific data
					switch (type){
						case 'group':
							var fields = dialog.form._getFields('group', true);
							break;
						case 'user':
							var fields = dialog.form._getFields('user', true);
							break;
					}
					var fieldHasErrors = false;
					Ext.iterate(fields, function(name, field){
						if (!field.isValid()) {
							fieldHasErrors = true;
							return false;
						}
						params[name] = field.getValue();
					});
					//is user password changed?
					if (type == 'user') {
						//password settings
						if (params['password'].length) {
							if (params['password'] != params['re_password']) {
								alert('neatitinka passw');
								fieldHasErrors = true;
							}
							else {
								var dhke = {
									p: str2bigInt(dialog.config.dhke.p, 16),
									g: str2bigInt(dialog.config.dhke.g, 16),
									aa: str2bigInt(dialog.config.dhke.aa, 16),
									b: randBigInt(255),
									iv: dialog.aes.generate_iv()
								};
								dhke.key = dialog.aes.hexpad256(bigInt2str(powMod(dhke.aa, dhke.b, dhke.p), 16));
								params['bb'] = dialog.aes.hexpad256(bigInt2str(powMod(dhke.g, dhke.b, dhke.p), 16));
								params['iv'] = dialog.aes._binArray2hex(dhke.iv);
								params['zkey'] = dhke.key;
								params['pass_aes'] = dialog.aes.encrypt_cbc_hex(dhke.key, dhke.iv, PC.utils.utf8_encode(params['password']));
								delete dhke;
							}
						}
						//permissions
						params['permissions'] = Ext.encode(dialog.permissions._loaded.data);
					}
					
					if (fieldHasErrors) {
						Ext.MessageBox.show({
							title: PC.i18n.error,
							msg: dialog.ln.invalid_fields_found,
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
						b.enable();
						return _false();
					}
					
					delete params.password;
					delete params.re_password;
					
					if (form._createMode) {
						params.group_id = form._node.parentNode.id;
					}
					
					Ext.Ajax.request({
						url: dialog.plugin_file +'?action='+(form._createMode?'create':'edit')+'_'+type,
						params: params,
						callback: function(options, success, response){
							if (success && response.responseText) {
								try {
									var data = Ext.decode(response.responseText);
									if (data.success) {
										if (type == 'group') {
											dialog.form._clear(true);
											dialog.form._loadGroupData(data.data);
											form._node.setText(data.data.groupname);
										}
										else {
											dialog.form._clear(true);
											dialog.form._loadUserData(data.data);
											form._node.setText(data.data.username);
										}
										if (form._createMode) {
											form._node.setId(data.data.id);
										}
										b.enable();
										return _true(data.data);
									}
								} catch(e) {console.log(e);};
							}
							//show error
							_false();
							b.enable();
							alert('Not saved.'+(data!=undefined?(data.error!=undefined?'<br />Error: '+data.error:''):''));
						}
					});
				}
			},
			{	text: PC.i18n.close,
				handler: function() {
					dialog.window.close();
				}
			}
		]
	};
	dialog.window = new Ext.Window({
		modal: true,
		title: ln.title,
		width: 800,
		height: 600,
		layout: 'border',
		border: false,
		layoutConfig: {
			align: 'stretch'
		},
		items: [dialog.tree, dialog.settings]
	});
	dialog.window.show(null, function(){
		Ext.MessageBox.show({
			title: PC.i18n.msg.title.loading,
			msg: PC.i18n.msg.loading,
			width: 300,
			wait: true,
			waitConfig: {interval:100}
		});
		Ext.Ajax.request({
			url: dialog.plugin_file +'?action=get_config',
			params: {api: true},
			callback: function(options, success, response){
				if (success && response.responseText) {
					try {
						//debugger;
						var data = Ext.decode(response.responseText);
						dialog.config = data;
						dialog.permissions.store.loadData(dialog.config.perms);
						Ext.Msg.hide();
						return;
					} catch(e) {console.log(e);};
				}
				alert('dialog error (LOAD_DIALOG_CONFIG)');
				Ext.Msg.hide();
				dialog.window.close();
			}
		});
	});
}

PC.plugin.auth = {
	name: PC.i18n.mod.auth.title,
	onclick: mod_auth_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};
</script>
<?php
unset($plugin_name, $plugin_url, $plugin_file);
?>