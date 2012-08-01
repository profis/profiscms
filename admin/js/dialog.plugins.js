Ext.ns('PC.dialog');
PC.dialog.plugins = {
	show: function() {
		this.ln = PC.i18n.dialog.plugins;
		var dialog = this;
		if (this.window) {
			this.window.show();
			return;
		}
		dialog.grid = new Ext.grid.EditorGridPanel({
			layout: 'fit',
			height: 300,
			store: new Ext.data.GroupingStore({
				reader: new Ext.data.ArrayReader({}, ['type','id','plugin','activated']),
				groupField: 'type',
				data: PC.global.plugins
			}),
			view: new Ext.grid.GroupingView({
				groupTextTpl: '{group} ({[values.rs.length]} {[values.rs.length > 1 ? "Plugins" : "Plugin"]})'
			}),
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true
				},
				columns: [
					{	dataIndex: 'type',
						hidden: true,
						groupRenderer: function(value) {
							if (value == '') {
								return PC.i18n.custom;
							}
							else if (value == 'core') {
								return PC.i18n.core;
							}
							else {
								//need to search plugin translation
								var group = value.charAt(0).toUpperCase() + value.substr(1);
								return group.replace('_', ' ');
							}
						}
					},
					{	header: 'Plugin',
						id: 'plugin',
						id: 'pc_dialog_plugins_plugincol',
						sortable: true,
						dataIndex: 'plugin'
					},
					{	header: 'Status',
						width: 100,
						renderer: function(value, id, r) {
							var activated = r.data.activated;
							if (activated) {
								return '<span style="color:green">On</span>';
							}
							return '<span style="color:red">Off</span>';
						},
						editor: {
							xtype: 'combo',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								fields: ['value', 'display'],
								data: [[0,'Off'],[1,'On']]
							},
							displayField: 'display',
							valueField: 'value',
							editable: false,
							forceSelection: true,
							value: '',
							triggerAction: 'all'
						},
						dataIndex: 'activated'
					}
				]
			}),
			autoExpandColumn: 'pc_dialog_plugins_plugincol',
			listeners: {
				beforeedit: function(ev) {
					if (ev.record.data.type == 'core') return false;
				}
			},
			sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
			/*tbar: [
				{	ref: '../edit_btn',
					iconCls: 'icon-edit',
					text: PC.i18n.edit,
					disabled: true,
					handler: function() {
						
					}
				}
			],*/
			buttons: [
				{	text: Ext.Msg.buttonText.ok,
					handler: function() {
						var data = [];
						Ext.iterate(dialog.grid.getStore().getModifiedRecords(), function(r){
							data.push([r.data.id,r.data.activated]);
						});
						if (data.length) {
							Ext.Ajax.request({
								url: 'ajax.plugins.php?action=update',
								method: 'POST',
								params: {
									plugins: Ext.util.JSON.encode(data)
								},
								success: function(result){
									var r = Ext.util.JSON.decode(result.responseText);
									if (r.plugins != undefined) {
										PC.global.plugins = r.plugins;
										PC.global.controllers = r.controllers;
										setTimeout(function(){
											Reload_page_controller_list();
										}, 100);
									}
									if (r.success) {
										dialog.grid.getStore().commitChanges();
										dialog.window.hide();
									}
									else if (r.success == '?') {
										PC.dialog.plugins.grid.getStore().reload();
										alert('Not everything was saved.');
									}
								},
								failure: function(){
									alert('Connection error.');
								}
							});
						}
						else dialog.window.hide();
					}
				},
				{	text: Ext.Msg.buttonText.cancel,
					handler: function() {
						dialog.window.hide()
					}
				}
			]
		});
		this.window = new Ext.Window({
			title: this.ln.title,
			/*layoutConfig: {
				align: 'stretch'
			},*/
			//height: 300,
			width: 350,
			resizable: false,
			border: false,
			items: this.grid,
			closeAction: 'hide'
		});
		this.window.show();
	}
};