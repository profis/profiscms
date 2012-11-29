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
error_reporting(0);
$cfg['core']['no_login_form'] = true;
require_once '../../admin/admin.php';

if (isset($_POST['ajax'])) {
	header('Content-Type: application/json');
	header('Cache-Control: no-cache');
	if (isset($_POST['deleted'])) {
		$j = json_decode($_POST['deleted'], true);
		if (is_array($j)) {
			foreach ($j as $k) {
				$r = $db->prepare("DELETE FROM {$cfg['db']['prefix']}forms WHERE id=?");
				$r->execute(array($k));
			}
		}
		echo '[]';
		return;
	}
	
	$out = array();
	$r = $db->query("SELECT * FROM {$cfg['db']['prefix']}forms");
	while ($f = $r->fetch()) {
		$f['ip'] = long2ip($f['ip']);
		foreach(json_decode($f['data']) as $key => $value) {
			$f['data_'.$key] = $value;
		}
		$out[] = $f;
	}
	echo json_encode($out);
	return;
}

$mod['name'] = 'Saved forms';
$mod['onclick'] = 'mod_forms_click()';
$mod['priority'] = 40;

?>
<style type="text/css">
/*
.x-grid3-row .x-grid3-cell-inner {
    white-space: normal;
}
.x-grid3-row-selected .x-grid3-cell-inner {
    white-space: pre;
}
*/
</style>
<script type="text/javascript">
Ext.namespace('PC.plugins');

function mod_forms_click() {
	Ext.ns('PC.dialog.mod_forms');
	var ln = PC.i18n.mod.forms;
	//var baseflds = ['id', {name: 'time', type: 'date', dateFormat: 'Y-m-d H:i:s'}, 'ip'];
	var baseflds = ['id', 'time', 'ip'];
	var basecols = [
		{	header: PC.i18n.date,
			dataIndex: 'time',
			width: 120//,
			//xtype: 'datecolumn',
			//format: 'Y-m-d H:i:s'
		},
		{	header: ln.ip_address,
			dataIndex: 'ip'
		}
	];
	
	var formRenderer = function(value) {
		ret = value;
		if((typeof(value) == 'object') && !(value instanceof Array) && value.name && value.location) {
			// file fields are displayed as links to uploaded files
			ret = '<a href="'+PC.global.BASE_URL+PC.global.directories.uploads+'/'+value.location+'">'+value.name+'</a>';
		}
		return ret;
	};
	
	var applyFilter = function(tab) {
		var filter = tab.search_field.getValue();
		if(filter=='') {
			tab.grid.store.clearFilter();
		} else {
			tab.grid.store.filter([
				{	fn: function(r) {
						var p = new RegExp(filter.replace('\\', '\\\\'), 'i');
						var match = false;
						Ext.iterate(r.data, function(field, value){
							if((typeof(value) == 'object') && !(value instanceof Array) && value.name && value.location) {
								// file fields need this special treatment
								value = value.name;
							}
							if (p.test(value)) {
								match = true;
								return false;
							}
						});
						return match;
					}
				}
			]);
		}
	};
	
	var clearFilter = function(tab) {
		tab.grid.store.clearFilter();
		tab.search_field.setValue('');
	};
	
	var deleteRecords = function(tab) {
		Ext.MessageBox.show({
			title: PC.i18n.msg.title.confirm,
			msg: PC.i18n.msg.confirm_delete,
			buttons: Ext.MessageBox.YESNO,
			icon: Ext.MessageBox.WARNING,
			fn: function(clicked) {
				if (clicked == 'yes') {
					var del_records=[];
					var del_ids=[];
					tab.grid.getSelectionModel().each(function(rec) {
						console.log(rec.data.id);
						if (rec.data.id != '') {
							del_records.push(rec);
							del_ids.push(rec.data.id);
						}
					});
					var rqparams = {
						ajax: '',
						deleted: Ext.encode(del_ids)
					};
					Ext.Ajax.request({
						url: '<?php echo $cfg['url']['base'].$cfg['directories']['plugins']; ?>/forms/<?php echo basename(__FILE__) ?>',
						params: rqparams,
						method: 'POST',
						callback: function(opts, success, rspns) {
							if (success && rspns.responseText) {
								try {
									var data = Ext.decode(rspns.responseText);
									if (rspns.responseText == '[]') {
										tab.grid.store.remove(del_records);
										return; // OK
									}
								} catch(e) {};
							}
							Ext.MessageBox.show({
								title: PC.i18n.error,
								msg: PC.i18n.msg.error.data.save,
								buttons: Ext.MessageBox.OK,
								icon: Ext.MessageBox.ERROR
							});
						}
					});
				}
			}
		});
	};
	
	var tabs = new Ext.TabPanel({
		activeTab: 0,
		flex: 1,
		items: [],
		border: false,
	});

	var w = new Ext.Window({
		modal: false,
		title: ln.selfname,
		width: 800,
		height: 400,
		maximizable: true,
		layout: 'vbox',
		layoutConfig: {
			align: 'stretch'
		},
		items: [
			tabs
		],
		buttonAlign: 'left',
		buttons: [
			{xtype: 'tbfill'},
			{
				text: PC.i18n.close,
				handler: function() {
					w.close();
				}
			}
		]
	});
	w.show();
	
	Ext.Ajax.request({
		url: '<?php echo $cfg['url']['base'].$cfg['directories']['plugins']; ?>/forms/<?php echo basename(__FILE__) ?>',
		params: {
			ajax: ''
		},
		method: 'POST',
		callback: function(opts, success, rspns) {
			if (success && rspns.responseText) {
				try {
					var data = Ext.decode(rspns.responseText);
					
					var tabNames = [];
					var fieldLists = [];
					var gridData = [];
					
					Ext.each(data, function(item, index, all) {
						var tabName = item.form_id;
						if(tabNames.indexOf(tabName) == -1) {
							tabNames.push(tabName);
							fieldLists[tabName] = [];
							gridData[tabName] = [];
						}

						for(var col in item) {
							if((col.substr(0,5) === 'data_') && (col.length > 5)) {
								var colName = col.substr(5);
								if(fieldLists[tabName].indexOf(colName) == -1) {
									fieldLists[tabName].push(colName);
								}
							}
						}
						gridData[tabName].push(item);
					});
					
					Ext.each(tabNames, function(tabName, index, all) {
						var myfields = [];
						var mycols = [];
						Ext.each(fieldLists[tabName], function(fieldName, fieldIndex, allFields) {
							myfields.push('data_'+fieldName);
							mycols.push({
								header: fieldName,
								dataIndex: 'data_'+fieldName,
								renderer: formRenderer
							});
						});
						var mytab = new Ext.Panel({
							title: tabName,
							border: false,
							layout: 'hbox',
							layoutConfig: {
								align: 'stretch'
							},
							items: [
								{
									flex: 3,
									xtype: 'editorgrid',
									ref: 'grid',
									stripeRows: true,
									store: new Ext.data.JsonStore({
										autoDestroy: true,
										fields: baseflds.concat(myfields),
										data: gridData[tabName]
									}),
									colModel: new Ext.grid.ColumnModel({
										defaults: { sortable: true },
										columns: basecols.concat(mycols)
									}),
									selModel: new Ext.grid.RowSelectionModel({
										moveEditorOnEnter: false,
										listeners: {
											selectionchange: function(sm) {
												var dis = (sm.getCount() == 0);
												mytab.del_btn.setDisabled(dis);
											}
										}
									}),
									listeners: {
										cellclick: function(grid, rowIndex, colIndex) {
											var cell = grid.getView().getCell(rowIndex, colIndex);
											mytab.detailPanel.body.update(cell.textContent.replace(/\n/g, '<br />'));
										}
									},
								},
								{
									flex: 1,
									autoScroll: true,
									title: ln.field_contents,
									ref: 'detailPanel',
									padding: 7,
									bodyStyle: "background: #ffffff;"
								}
							],
							tbar: [
								{	ref: '../del_btn',
									disabled: true,
									iconCls: 'icon-delete',
									text: PC.i18n.del,
									handler: function(field, event) {
										deleteRecords(mytab);
									},
								},
								{xtype:'tbfill'},
								{	xtype: 'textfield',
									ref: '../search_field',
									emptyText: PC.i18n.search_empty,
									style: 'font-style: italic',
									enableKeyEvents: true,
									listeners: {
										change: function(field, event) {
											applyFilter(mytab);
										},
										keyup: function(field, event) {
											if(event.getKey() == 13) {
												applyFilter(mytab);
											}
										}
									}
								},
								{	iconCls: 'icon-zoom',
									handler: function(field, event) {
										applyFilter(mytab);
									}
								},
								{	iconCls: 'icon-zoom-out',
									handler: function(field, event) {
										clearFilter(mytab);
									}
								}
							]
						});
						tabs.add(mytab);
					});
					tabs.setActiveTab(0);
					return;
				} catch(e) {};
			}
			Ext.MessageBox.show({
				title: PC.i18n.error,
				msg: PC.i18n.msg.error.data.load,
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
			w.close();
		}
	});
}

PC.plugin.forms = {
	name: PC.i18n.mod.forms.selfname,
	onclick: mod_forms_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};

</script>
