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

require_once '../../../admin/admin.php';

if (isset($_POST['ajax'])) {
	header('Content-Type: application/json');
	header('Cache-Control: no-cache');
	if (isset($_POST['deleted'])) {
		$j = json_decode($_POST['deleted'], true);
		if (is_array($j)) {
			foreach ($j as $k) {
				if (empty($k['site'])) $k['site'] = 0;
				$delete_query = "DELETE FROM {$cfg['db']['prefix']}config WHERE ckey=? and plugin=? and site=?";
				$r = $db->prepare($delete_query);
				$params = array($k['key'], $k['plugin'], $k['site']);
				$r->execute($params);
			}
		}
	}
	if (isset($_POST['config'])) {
		$j = json_decode($_POST['config'], true);
		if (is_array($j)) {
			/*if (!$db->query("TRUNCATE TABLE {$cfg['db']['prefix']}config")) {
				$db->query("CREATE TABLE {$cfg['db']['prefix']}config (
					  `ckey` varchar(64) NOT NULL DEFAULT '',
					  `plugin` varchar(50) NOT NULL,
					  `site` int(11) NOT NULL DEFAULT '0',
					  `ln` varchar(2) NOT NULL DEFAULT '',
					  `value` text NOT NULL,
					  PRIMARY KEY (`ckey`,`plugin`,`site`,`ln`),
					  KEY `site` (`site`,`ln`)
					) ENGINE=MyISAM;");
				
			}*/
			foreach ($j as $k) {
				$delete_query = "DELETE FROM {$cfg['db']['prefix']}config WHERE ckey=? and plugin=? and site=?";
				$r = $db->prepare($delete_query);
				$params = array(
					substr($k[0], 0, 64),
					$k[1],
					intval($k[2]),
				);
				$r->execute($params);
				$insert_query = "INSERT INTO {$cfg['db']['prefix']}config (ckey, plugin, site, value) VALUES(?,?,?,?)";
				$r = $db->prepare($insert_query);
				$params[] = $k[3];
				$r->execute($params);
			}
			echo '[]';
			return;
		}
	}
	
	$sites = $site->Get_all();
	
	$out = array();
	$r = $db->query("SELECT * FROM {$cfg['db']['prefix']}config");
	while ($f = $r->fetch()) {
		if (!empty($f['plugin']) && !$plugins->Is_active($f['plugin'])) continue;
		$k =& $f['ckey'];
		if (!isset($out[$k])) {
			$out[$k][0] = $k;
			$out[$k][1] = $f['plugin'];
			$out[$k][2] = array();
		}
		if (($f['site'] == 0) || isset($sites[$f['site']])) {
			//print_pre($f);
			if (is_null($f['site'])) {
				$f['site'] = 0;
			}
			$out[$k][2][] = array($f['site'], $f['value']);
		}
			
	}
	$output = array_values($out);
	echo json_encode($output);
	return;
}

$mod['name'] = 'Settings';
$mod['onclick'] = 'mod_config_click()';
$mod['priority'] = 40;



?>
<style type="text/css">
.icon-explode {
	background-image: url("images/explode.png") !important;
}
.icon-implode {
	background-image: url("images/implode.png") !important;
}
.icon-google {
	background-image: url("http://www.google.com/favicon.ico") !important;
}
</style>
<script type="text/javascript">
Ext.namespace('PC.plugins');

function mod_config_click() {
	Ext.ns('PC.dialog.mod_config');
	var ln = PC.i18n.mod.variables;
	if (PC.i18n.mod.config) {
		Ext.apply(ln, PC.i18n.mod.config);
	}
	var deleted_list = [];
	PC.dialog.mod_config.deleted = deleted_list;
	/*
	//var ed_sk = function(fld, e) {
	function ed_sk(fld, e) {
		// copied from Editor::onSpecialKey()
		if (e.getKey() == e.ENTER) {
			//e.stopEvent(); // wtf doesn't work, grid still gets it
			fld.gridEditor.completeEdit();
			//if (fld.gridEditor.triggerBlur) fld.gridEditor.triggerBlur();
		}
	};
	*/
	var baseflds = ['title', {name:'key', sortType:Ext.data.SortTypes.asNatural} , 'plugin', 'value'];
	var basecols = [
		{	width: 300,
			_base: true,
			header: PC.i18n.name,
			dataIndex: 'title',
			sortable: true,
			hideable: false,
			editor: {
				xtype: 'textfield',
				maxlength: 64,
				//completeOnEnter: false,
				//listeners: { specialkey: ed_sk },
				listeners: {
					afterrender: function(ed) {
						ed.gridEditor.on('canceledit', function(ed, val, origval) {
							if (origval == '')
								grd.store.remove(ed.record);
						});
						//ed.gridEditor.on('startedit', function(be, val) {
						//	this.field.selectText();
						//});
					}
				},
				validator: function(val) {
					if (val=='') return ln.error.name_empty;
					var id_idx = grd.store.findExact('key', val);
					if (id_idx != -1)
						if (grd.store.getAt(id_idx) != grd.selModel.getSelected())
							return ln.error.name_in_use;
					return true;
				}
			}
		},
		{	
			_base: true,
			dataIndex: 'key',
			sortable: true,
			hideable: false,
			hidden: true
		},
		{	_base: true,
			header: ln.category,
			dataIndex: 'plugin',
			hideable: false,
			hidden: true,
			groupRenderer: function(value, unused, record, rowIndex, colIndex, store) {
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
		{	width: 150,
			_base: true,
			header: 'Reikšmė',
			dataIndex: 'value',
			sortable: true,
			hideable: false,
			editor: {
				xtype: 'textfield'//,
				//maxlength: 64,
				//completeOnEnter: false,
				//listeners: { specialkey: ed_sk },
				/*
				listeners: {
					afterrender: function(ed) {
						ed.gridEditor.on('canceledit', function(ed, val, origval) {
							if (origval == '')
								grd.store.remove(ed.record);
						});
						//ed.gridEditor.on('startedit', function(be, val) {
						//	this.field.selectText();
						//});
					}
				},
				validator: function(val) {
					if (val=='') return ln.error.name_empty;
					var id_idx = grd.store.findExact('key', val);
					if (id_idx != -1)
						if (grd.store.getAt(id_idx) != grd.selModel.getSelected())
							return ln.error.name_in_use;
					return true;
				}
				*/
			}
		}
	];
	
	// ***** CREATE "All sites" TAB *****
	var flds = baseflds.slice(0);
	var cols = basecols.slice(0);

	var applyFilter = function() {
		atab.str_store.filter([
			{	fn: function(r) {
					var filter = grd.getTopToolbar().findById('mod-config-search').getValue();
					var p = new RegExp(filter,'i');
					var match = false;
					Ext.iterate(r.data, function(field, value){
						if (!(/^(plugin)$/.test(field))) if (p.test(value)) {
							match = true;
							return false;
						}
					});
					return match;
				}
			}
		]);
	};
	var clearFilter = function() {
		atab.str_store.clearFilter();
		grd.getTopToolbar().findById('mod-config-search').setValue('');
	};
	var tabs = new Ext.TabPanel({
		activeTab: 0,
		items: [],
		border: false,
		bodyCfg: {
			border: false
		},
		listeners: {
			tabchange: function(tbs, tab) {
				atab = tab;
				var filter = grd.getTopToolbar().findById('mod-config-search').getValue();
				if (filter == '') clearFilter();
				else applyFilter();
				grd.reconfigure(tab.str_store, tab.col_mod);
				grd.exp_btn.setVisible(!tab.site_id);
				grd.imp_btn.setVisible(tab.site_id);
			}
		},
		defaults: {
			style: 'font-size:1px;height:0'
		}
	});
	// active tab
	var atab = tabs.add({
		title: ln.all_sites,
		site_id: null,
		str_store: new Ext.data.GroupingStore({
			reader: new Ext.data.ArrayReader({}, flds),
			groupField: 'plugin',
			data: []
		}),
		col_mod: new Ext.grid.ColumnModel({columns:cols})
	});
	
	var all_tabs = {
		all: atab
	};
	PC.dialog.mod_config.all_tabs = all_tabs;
	
	// ***** CREATE INDIVIDUAL SITE TABS *****
	PC.global.site_select.getStore().each(function(rec) {
		flds = baseflds.slice(0);
		cols = basecols.slice(0);
		all_tabs[rec.get('id')] = tabs.add({
			title: rec.get('name'),
			site_id: rec.get('id'),
			str_store: new Ext.data.GroupingStore({
				store_id: rec.get('id'),
				reader: new Ext.data.ArrayReader({}, flds),
				groupField: 'plugin',
				data: []
			}),
			col_mod: new Ext.grid.ColumnModel({columns:cols})
		});
	});
	
	var add_fn = function() {
		var rec = new grd.store.recordType({
			key: '',
			plugin: ''
		});
		grd.store.add(rec);
		grd.store.sort();
		idx = grd.store.indexOf(rec);
		grd.getSelectionModel().selectRow(idx);
		grd.startEditing(idx, grd.getColumnModel().findColumnIndex('key'));
	};
	var edit_fn = function() {
		var sel = grd.getSelectionModel().getSelected();
		if (sel) {
			var idx = grd.store.indexOf(sel);
			grd.getSelectionModel().selectRow(idx);
			// determine column to edit
			var cm = grd.getColumnModel();
			var col = 'key';
			Ext.each(cm.config, function(c, idx, all) {
				if (!c._base) {
					col = c.dataIndex;
					return false;
				}
			});
			if (grd._lastcol !== undefined)
				if (grd._lastcol!='key' || sel.data.plugin=='')
					col = grd._lastcol;
			grd.startEditing(idx, cm.findColumnIndex(col));
		}
	};
	var del_fn = function() {
		Ext.MessageBox.show({
			title: PC.i18n.msg.title.confirm,
			msg: PC.i18n.msg.confirm_delete,
			buttons: Ext.MessageBox.YESNO,
			icon: Ext.MessageBox.WARNING,
			fn: function(clicked) {
				if (clicked == 'yes') {
					grd.getSelectionModel().each(function(rec) {
						if (rec.data.plugin == '') {
							grd.store.remove(rec);
							deleted_list.push({
								site: grd.store.store_id,
								key: rec.data.key,
								plugin: rec.data.plugin
							});
						}
					});
				}
			}
		});
	};
	var exp_fn = function() {
		if (atab.site_id !== null) { alert('!'); return; } // this should never happen
		grd.getSelectionModel().each(function(rec) {
			for (var x in all_tabs) {
				if (all_tabs[x].site_id !== null) {
					var st = all_tabs[x].str_store;
					if (st.findExact('key', rec.data.key) == -1) {
						var exp_rec = new st.recordType(Ext.apply({}, rec.data));
						exp_rec.markDirty();
						st.addSorted(exp_rec);
					}
				}
			}
			atab.str_store.remove(rec);
			deleted_list.push({
				key: rec.data.key,
				plugin: rec.data.plugin
			});
		});
	};
	var imp_fn = function() {
		if (atab.site_id === null) { alert('!'); return; } // this should never happen
		grd.getSelectionModel().each(function(rec) {
			var d = {};
			Ext.apply(d, rec.data);
			if (d.lock) {
				// get common value
				var val = null;
				Ext.each(atab.col_mod.config, function(c, idx, all) {
					if (!c._base) {
						val = d[c.dataIndex];
						return false;
					}
				});
				// set common value
				Ext.each(all_tabs.all.col_mod.config, function(c, idx, all) {
					if (!c._base)
						d[c.dataIndex] = val;
				});
				// kill other data
				for (var x in all_tabs)
					if (all_tabs[x].site_id !== null) {
						var idx = all_tabs[x].str_store.findExact('key', d.key);
						if (idx != -1) {
							var del_rec = all_tabs[x].str_store.getAt(idx);
							all_tabs[x].str_store.removeAt(idx);
							deleted_list.push({
								site: all_tabs[x].str_store.store_id,
								key: del_rec.data.key,
								plugin: del_rec.data.plugin
							});
						}
					}
			} else {
				// gather data
				for (var x in all_tabs)
					if (all_tabs[x].site_id !== null) {
						var idx = all_tabs[x].str_store.findExact('key', d.key);
						if (idx != -1) {
							var del_rec = all_tabs[x].str_store.getAt(idx);
							if (all_tabs[x] != atab) {
								Ext.applyIf(d, all_tabs[x].str_store.getAt(idx).data);
							}
							all_tabs[x].str_store.removeAt(idx);
							deleted_list.push({
								site: all_tabs[x].str_store.store_id,
								key: del_rec.data.key,
								plugin: del_rec.data.plugin
							});
						}
					}
			}
			var st = all_tabs.all.str_store;
			// kill target (should not exist anyway)
			var idx = st.findExact('key', d.key);
			if (idx != -1) {
				var del_rec = st.getAt(idx);
				st.removeAt(idx);
				deleted_list.push({
					site: st.store_id,
					key: del_rec.data.key,
					plugin: del_rec.data.plugin
				});
			}
			// ADD!
			var imp_rec = new st.recordType(d);
			imp_rec.markDirty();
			st.addSorted(imp_rec);
		});
	};
	var gt_fn = function() {
		grd.getSelectionModel().each(function(rec) {
			if (rec.get('lock')) return;
			var src = null;
			var data;
			Ext.each(atab.col_mod.config, function(c, idx, all) {
				if (!c._base) {
					data = rec.get(c.dataIndex);
					if (data) {
						src = c.dataIndex;
						return false;
					}
				}
			});
			if (src) {
				Ext.each(atab.col_mod.config, function(c, idx, all) {
					if (!c._base) {
						if (!rec.get(c.dataIndex)) {
							// translate [data] from [src] to [c.dataIndex]
							if (rec.get(c.dataIndex) === undefined)
								rec.set(c.dataIndex, ''); // to mark as modified
							google.language.translate(data, src, c.dataIndex, function(result) {
								if (!result.error) {
									rec.set(c.dataIndex, result.translation);
								}
							});
						}
					}
				});
			}
		});
	};
	
	var grd = new Ext.grid.EditorGridPanel({
		store: atab.str_store,
		//layout: 'fit',
		flex: 1,
		border: false,
        view: new Ext.grid.GroupingView({
			enableGroupingMenu: false,
			enableNoGroups: false,
			columnsText: ln.show_langs,
			groupTextTpl: '{values.group}'
		}),
		colModel: atab.col_mod,
		selModel: new Ext.grid.RowSelectionModel({
			moveEditorOnEnter: false,
			listeners: {
				selectionchange: function(sm) {
					var dis = (sm.getCount() == 0);
					grd.edit_btn.setDisabled(dis);
					grd.exp_btn.setDisabled(dis);
					grd.imp_btn.setDisabled(dis);
					//grd.gt_btn.setDisabled(dis);
					if (!dis)
						dis = sm.each(function(rec) {
							if (rec.data.plugin == '')
								return false;
						});
					grd.del_btn.setDisabled(dis);
				}
			}
		}),
		listeners: {
			beforeedit: function(ee) {
				if (ee.field=='key' && ee.record.data.plugin!='')
					return false; // allow editing only custom keys
				grd._lastcol = ee.field;
			},
			containerdblclick: function(g, e) {
				if (e.target == g.view.scroller.dom)
					add_fn();
			},
			containerclick: function(g, e) {
				if (e.target == g.view.scroller.dom)
					this.getSelectionModel().clearSelections();
			},
			keypress: function(e) {
				if (e.getKey() === e.INSERT) add_fn();
				if (e.getKey() === e.ENTER) edit_fn();
				if (e.getKey() === e.F2) {
					var sel = grd.getSelectionModel().getSelected();
					if (sel && sel.data.plugin=='') {
						var idx = grd.store.indexOf(sel);
						grd.getSelectionModel().selectRow(idx);
						grd.startEditing(grd.store.indexOf(sel), grd.getColumnModel().findColumnIndex('key'));
					}
				}
				if (e.getKey() === e.DELETE) del_fn();
			},
			afteredit: function(ee) {
				var cm = ee.grid.colModel;
				var chngs = {};
				chngs[ee.field] = ee.value;
				var rec = ee.record;
				var newdata = Ext.apply({}, chngs, rec.data);
				if (newdata.lock) {
					// ... this code was used with RowEditor (multiple edits at once)
					// Generating old & nu arrays
					var old = []; // unchanged keys
					var nu = [];  // changed keys
					Ext.each(cm.config, function(c, idx, all) {
						if (!c._base) {
							var x = c.dataIndex;
							if ((chngs[x] === undefined) || ((chngs[x]==='') && (rec.data[x]===null)))
								old.push(newdata[x]);
							else
								nu.push(newdata[x]);
						}
					});
					old = Ext.unique(old);
					nu = Ext.unique(nu);
					//alert('--- old['+old.length+'] ---\n* '+old.join("\n* "));
					//alert('--- nu['+nu.length+'] ---\n* '+nu.join("\n* "));
					
					if ((old.length + nu.length) > 1) {
						if ((nu.length == 1) && rec.data.lock) {
							// Clone value
							Ext.each(cm.config, function(c, idx, all) {
								if (!c._base)
									rec.set(c.dataIndex, nu[0]); // set all to single changed value
							});
						} else {
							// Show value dialog
							rec.set('lock', false);
							var val_w_items = [
								{
									border: false,
									padding: 4,
									html: String.format(ln.choose_new_value, '<span style="font-weight:bold">"'+newdata.key+'"</span>')
								}
							];
							Ext.each(nu.concat(old), function(item, idx, all) {
								val_w_items.push({
									xtype: 'radio',
									height: 20,
									name: 'str_new_val',
									value: item,
									boxLabel: (item=='')||(item==null) ? '&nbsp;' : item,
									checked: val_w_items.length==1 ? 1 : 0
								});
							});
							var val_w = new PC.ux.Window({
								modal: true,
								title: ln.choose_new_value_title,
								width: 250,
								autoHeight: true,
								items: [
									{
										border: false,
										baseCls: 'x-panel-mc',
										layout: 'form',
										labelWidth: 20,
										ref: 'radios',
										items: val_w_items
									}
								],
								buttonAlign: 'center',
								buttons: [
									{
										text: Ext.Msg.buttonText.ok,
										handler: function() {
											val_w.radios.items.each(function(item, idx, all) {
												if (item.xtype == 'radio')
													if (item.getValue()) {
														rec.set('lock', true);
														Ext.each(cm.config, function(c, idx, all) {
															if (!c._base)
																rec.set(c.dataIndex, item.value);
														});
														val_w.close();
														return false;
													}
											});
										}
									},{
										text: Ext.Msg.buttonText.cancel,
										handler: function() {
											val_w.close();
										}
									}
								]
							});
							val_w.show();
						}
					}
				}
			}
		},
		tbar: [
			{	iconCls: 'icon-add',
				text: PC.i18n.add,
				handler: add_fn
			},
			{	ref: '../edit_btn',
				iconCls: 'icon-edit',
				text: PC.i18n.edit,
				handler: edit_fn,
				disabled: true
			},
			{	ref: '../del_btn',
				iconCls: 'icon-delete',
				text: PC.i18n.del,
				handler: del_fn,
				disabled: true
			},
			'-',
			{	ref: '../exp_btn',
				iconCls: 'icon-explode',
				text: ln.explode,
				handler: exp_fn,
				disabled: true
			},
			{	ref: '../imp_btn',
				iconCls: 'icon-implode',
				text: ln.implode,
				handler: imp_fn,
				hidden: true,
				disabled: true
			},
			/*{	ref: '../gt_btn',
				iconCls: 'icon-google',
				text: ln.translate,
				handler: function() {
					if (google.language)
						gt_fn();
					else
						google.load('language', '1', {callback: gt_fn});
				},
				disabled: true
			},*/
			{xtype:'tbfill'},
			{	xtype: 'textfield',
				id: 'mod-config-search',
				emptyText: PC.i18n.search_empty,
				style: 'font-style: italic',
				listeners: {
					change: function(field, val, old) {
						if (val == '') {
							atab.str_store.clearFilter();
						}
					}
				}
			},
			{	iconCls: 'icon-zoom',
				handler: applyFilter
			},
			{	iconCls: 'icon-zoom-out',
				handler: clearFilter
			}
		]
	});
	var w = new PC.ux.Window({
		modal: false,
		title: ln.selfname,
		width: 640,
		height: 400,
		maximizable: true,
		layout: 'vbox',
		layoutConfig: {
			align: 'stretch'
		},
		items: [
			tabs,
			grd
		],
		buttonAlign: 'left',
		buttons: [
			{xtype: 'tbtext', text: '<img style="vertical-align: -4px" src="images/lock.png" alt="" /> - '+ln.lock},
			{xtype: 'tbfill'},
			{
				text: Ext.Msg.buttonText.ok,
				disabled: true,
				ref: '../ok_btn',
				handler: function() {
					// *** SAVE DATA ***
					var strs = []; // output JSON array
					var o = {}; // keyed pointers to arrays
					for (var x in all_tabs) {
						var st = all_tabs[x].str_store;
						var sid = x=='all' ? 0 : x;
						Ext.each(st.modified, function(rec) {
							if (rec.dirty) {
								var a, i;
								if (o[rec.data.key] === undefined) {
									var value_key = rec.data.key;
									if (value_key == '') {
										value_key = rec.data.title;
									}
									a = [value_key, rec.data.plugin, sid, rec.data.value];
									strs.push(a);
									o[value_key] = a[2];
								}
								a = o[value_key];
							}
						});
					}
					var rqparams = {
						ajax: '',
						config: Ext.encode(strs),
						deleted: Ext.encode(PC.dialog.mod_config.deleted)
					};
					Ext.Ajax.request({
						url: '<?php echo $cfg['url']['base'].$cfg['directories']['core_plugins_www']; ?>/config/<?php echo basename(__FILE__) ?>',
						params: rqparams,
						method: 'POST',
						callback: function(opts, success, rspns) {
							if (success && rspns.responseText) {
								try {
									var data = Ext.decode(rspns.responseText);
									if (rspns.responseText == '[]') {
										w.close();
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
			},{
				text: Ext.Msg.buttonText.cancel,
				handler: function() {
					w.close();
				}
			}
		]
	});
	w.show();
	Ext.Ajax.request({
		url: '<?php echo $cfg['url']['base'].$cfg['directories']['core_plugins_www']; ?>/config/<?php echo basename(__FILE__) ?>',
		params: {
			ajax: ''
		},
		method: 'POST',
		callback: function(opts, success, rspns) {
			if (success && rspns.responseText) {
				try {
					var data = Ext.decode(rspns.responseText);
					
					// *** LOAD DATA ***
					Ext.each(data, function(item, ndx, all) { // keys
						var recs = {};
						Ext.each(item[2], function(item1, ndx1, all1) { // values
							var site_id = (item1[0] == 0) ? 'all' : item1[0];
							if (all_tabs[site_id] === undefined) return;
							var cm = all_tabs[site_id].col_mod;
							
							// init record
							if (recs[site_id] === undefined) {
								var title = item[0];
								if (item[1] != '' && PC.plugin[item[1]] && PC.plugin[item[1]].ln && PC.plugin[item[1]].ln.config_titles && PC.plugin[item[1]].ln.config_titles[item[0]]) {
									title = PC.plugin[item[1]].ln.config_titles[item[0]];
								}
								else if (true) {
									
								}
								recs[site_id] = [title, item[0], item[1], item[2][0][1]]; // title, key, plugin, value
								//for (var i=3; i<cm.getColumnCount(); i++)
								//	recs[site_id].push(null);
							}
							var rec = recs[site_id];
							
							// add data to record
							/*
							if (item1[1] === null) { // ln
								Ext.each(cm.config, function(c, idx, all) {
									//if (!in_array(c.dataIndex, baseflds))
									if (!c._base)
										if (rec[idx] === null)
											rec[idx] = item1[2]; // value
								});
							} else {
								var idx = cm.findColumnIndex(item1[1]);
								if (idx != -1) {
									rec[2] = false; // common value
									rec[idx] = item1[2]; // value
								}
							}
							*/
						});
						for (var x in recs)
							all_tabs[x].str_store.loadData([ recs[x] ], true);
					});
					for (var x in all_tabs) {
						all_tabs[x].str_store.sort('key');
					}
					w.ok_btn.enable();
					return; // OK
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

PC.plugin.config = {
	name: PC.i18n.mod.config.selfname,
	onclick: mod_config_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};

</script>