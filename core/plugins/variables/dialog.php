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
			$r = $db->prepare("DELETE FROM {$cfg['db']['prefix']}variables WHERE vkey=? and controller=? and site=?");
			foreach ($j as $k) {
				if (empty($k['site'])) $k['site'] = 0;
				$r->execute(array($k['key'], $k['controller'], $k['site']));
			}
		}
	}
	if (isset($_POST['variables'])) {
		$j = json_decode($_POST['variables'], true);
		if (is_array($j)) {
			/*if (!$db->query("TRUNCATE TABLE {$cfg['db']['prefix']}variables")) {
				$db->query("CREATE TABLE {$cfg['db']['prefix']}variables (
					  `vkey` varchar(64) NOT NULL DEFAULT '',
					  `controller` varchar(50) NOT NULL,
					  `site` int(11) NOT NULL DEFAULT '0',
					  `ln` varchar(2) NOT NULL DEFAULT '',
					  `value` text NOT NULL,
					  PRIMARY KEY (`vkey`,`controller`,`site`,`ln`),
					  KEY `site` (`site`,`ln`)
					) ENGINE=MyISAM;");
				
			}*/
			foreach ($j as $k) {
				foreach ($k[2] as $v) {
					$r = $db->prepare("DELETE FROM {$cfg['db']['prefix']}variables WHERE vkey=? and controller=? and site=? and ln=?");
					$params = array(
						substr($k[0], 0, 64),
						$k[1],
						(is_null($v[0])?0:$v[0]),
						(is_null($v[1])?null:substr($v[1], 0, 2))
					);
					$r->execute($params);
					$r = $db->prepare("INSERT INTO {$cfg['db']['prefix']}variables (vkey, controller, site, ln, value) VALUES(?,?,?,?,?)");
					$params[] = $v[2];
					$r->execute($params);
				}
			}
			echo '[]';
			return;
		}
	}
	
	$sites = $site->Get_all();
	
	$out = array();
	$r = $db->query("SELECT * FROM {$cfg['db']['prefix']}variables");
	while ($f = $r->fetch()) {
		if (!empty($f['controller']) && !$plugins->Is_active($f['controller'])) continue;
		$clean_k = & $f['vkey'];
		$k = $f['vkey'] . $f['controller'];
		if (!isset($out[$k])) {
			$out[$k][0] = $clean_k;
			$out[$k][1] = $f['controller'];
			$out[$k][2] = array();
		}
		if ($f['site'] == 0) $f['site'] = null;
		if (($f['site'] === null) || isset($sites[$f['site']]))
			$out[$k][2][] = array($f['site'], $f['ln'], $f['value']);
	}
	echo json_encode(array_values($out));
	return;
}

$mod['name'] = 'Variables';
$mod['onclick'] = 'mod_variables_click()';
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

function mod_variables_click() {
	Ext.ns('PC.dialog.mod_variables');
	var ln = PC.i18n.mod.variables;
	var deleted_list = [];
	PC.dialog.mod_variables.deleted = deleted_list;
	
	PC.dialog.mod_variables.custom_edit = {
		_custom: {
			
		}
	};
	
	var hook_params = {};
	PC.hooks.Init('plugin/variables/custom_edit', hook_params);
	if (hook_params) {
		Ext.apply(PC.dialog.mod_variables.custom_edit, hook_params);
	}
	
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
	var baseflds = ['key', 'controller', 'lock'];
	var basecols = [
		{	width: 150,
			_base: true,
			header: PC.i18n.name,
			dataIndex: 'key',
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
		{	_base: true,
			header: ln.category,
			dataIndex: 'controller',
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
		{	_base: true,
			header: '<img src="images/lock.png" alt="" />',
			dataIndex: 'lock',
			//sortable: true,
			menuDisabled: true,
			width: 30,
			fixed: true,
			hideable: false,
			editor: {
				xtype: 'checkbox',
				//completeOnEnter: false,
				//listeners: { specialkey: ed_sk },
				boxLabel: ''
			},
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				if (value) return '<img src="images/lock.png" alt="" style="position:absolute; margin-top:-2px" />';
			}
		}
	];
	
	// ***** CREATE "All sites" TAB *****
	var flds = baseflds.slice(0);
	var cols = basecols.slice(0);
	// collect all languages into ln_data array
	var ln_idxs = {};
	var i = 0;
	PC.global.site_select.getStore().each(function(rec) {
		Ext.each(rec.get('langs'), function(item, ndx, all) {
			if (ln_idxs[item[0]] == undefined) {
				ln_idxs[item[0]] = i++;
				flds.push(item[0]);
				cols.push({
					width: 160,
					header: '<div class="flag" style="background-position:'+PC.utils.getFlagOffsets(item[0])+';margin-right:4px;vertical-align:-1px"></div>'+item[1],
					dataIndex: item[0],
					sortable: true,
					groupable: false,
					renderer: 'htmlEncode',
					editor: {
						xtype: 'textfield',
						//completeOnEnter: false,
						//listeners: { specialkey: ed_sk }
						listeners: {
							afterrender: function(ed) {
								/*ed.gridEditor.on('startedit', function(be, val) {
									this.field.selectText();
								});*/
							}
						}
					}
				});
			}
		});
	});
	var applyFilter = function() {
		atab.str_store.filter([
			{	fn: function(r) {
					var filter = grd.getTopToolbar().findById('mod-variables-search').getValue();
					var p = new RegExp(filter,'i');
					var match = false;
					Ext.iterate(r.data, function(field, value){
						if (!(/^(controller|lock)$/.test(field))) if (p.test(value)) {
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
		grd.getTopToolbar().findById('mod-variables-search').setValue('');
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
				var filter = grd.getTopToolbar().findById('mod-variables-search').getValue();
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
			groupField: 'controller',
			data: []
		}),
		col_mod: new Ext.grid.ColumnModel({columns:cols})
	});
	
	var all_tabs = {
		all: atab
	};
	var tab_recs = {
		all: []
	};
	PC.dialog.mod_variables.all_tabs = all_tabs;
	
	// ***** CREATE INDIVIDUAL SITE TABS *****
	PC.global.site_select.getStore().each(function(rec) {
		flds = baseflds.slice(0);
		cols = basecols.slice(0);
		Ext.each(rec.get('langs'), function(item1, ndx1, all1) {
			flds.push(item1[0]);
			cols.push({
				header: '<div class="flag" style="background-position:'+PC.utils.getFlagOffsets(item1[0])+';margin-right:4px;vertical-align:-1px"></div>'+item1[1],
				dataIndex: item1[0],
				groupable: false,
				editor: {
					xtype: 'textfield',
					listeners: {
						afterrender: function(ed) {
							//ed.gridEditor.on('startedit', function(be, val) {
							//	this.field.selectText();
							//});
						}
					}
				}
			});
		});
		tab_recs[rec.get('id')] = [];
		all_tabs[rec.get('id')] = tabs.add({
			title: rec.get('name'),
			site_id: rec.get('id'),
			str_store: new Ext.data.GroupingStore({
				store_id: rec.get('id'),
				reader: new Ext.data.ArrayReader({}, flds),
				groupField: 'controller',
				data: []
			}),
			col_mod: new Ext.grid.ColumnModel({columns:cols})
		});
	});
	
	var add_fn = function() {
		var rec = new grd.store.recordType({
			key: '',
			controller: '',
			lock: false
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
				if (grd._lastcol!='key' || sel.data.controller=='') {
					col = grd._lastcol;
				}
					
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
						if (rec.data.controller == '') {
							grd.store.remove(rec);
							deleted_list.push({
								site: grd.store.store_id,
								key: rec.data.key,
								controller: rec.data.controller
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
				controller: rec.data.controller
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
								controller: del_rec.data.controller
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
								controller: del_rec.data.controller
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
					controller: del_rec.data.controller
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
		//autoEncode: true,
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
							if (rec.data.controller == '')
								return false;
						});
					grd.del_btn.setDisabled(dis);
				}
			}
		}),
		listeners: {
			beforeedit: function(ee) {
				if (ee.field=='key' && ((!ee.record.modified || !ee.record.modified.hasOwnProperty('key')) && ee.value != '') /*&& ee.record.data.controller!=''*/)
					return false; // do not allow to edit keys (instead of allow editing only custom keys)
				var controller = ee.record.data.controller;
				if (controller == '') {
					controller = '_custom';
				}
				if (PC.dialog.mod_variables.custom_edit[controller] && PC.dialog.mod_variables.custom_edit[controller][ee.record.data.key]) {
					var dialog = PC.dialog.mod_variables;
					var expl = '';
					if (PC.dialog.mod_variables.custom_edit[controller][ee.record.data.key]['expl']) {
						expl = PC.dialog.mod_variables.custom_edit[controller][ee.record.data.key]['expl'];
					}
					if (!dialog.edit_window) {
						
						var form = new Ext.form.FormPanel({
							ref: '_f',
							//width: this.form_width,
							flex: 1,
							layout: 'form',
							padding: 6,
							border: false,
							bodyCssClass: 'x-border-layout-ct',
							labelWidth: 100,
							labelAlign: 'top',
							defaults: {xtype: 'htmleditor', anchor: '100%', enableLists: false, enableFontSize: false},
							items: [
								{	
									ref: '../_field',
									name: 'value',
									mode: 'local',
									editable: false,
									forceSelection: true,
									value: '',
									allowBlank: false
								},
								{
									ref: '../_expl',
									xtype: 'box', 
									autoEl: {cn: expl}
								}
							],
							frame: true,
							buttonAlign: 'center',
							buttons: [
								{	text: PC.i18n.save,
									iconCls: 'icon-save',
									ref: '../../_btn_save',
									handler: function() {
										var w = PC.dialog.mod_variables.edit_window;
										w.record.set(w.field_name, w._field.getValue()); 
										w.hide();
									}
								}
							]

						});
						
						dialog.edit_window = new PC.ux.Window({
							modal: true,
							title: 'Window title',
							closeAction: 'hide',
							width: 600,
							height: 425,
							layout: 'hbox',
							layoutConfig: {
								align: 'stretch'
							},
							items: form
						});
					}
					dialog.edit_window.record = ee.record;
					dialog.edit_window.field_name = ee.field;
					dialog.edit_window.setTitle(ee.record.data.key + ' - ' + ee.field);

					dialog.edit_window._field.setValue(ee.value);
					
					try {
						dialog.edit_window._expl.update(expl);
					}
					catch(e) {
					
					}
					dialog.edit_window.show();
					return false;
				}
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
					if (sel && sel.data.controller=='') {
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
				id: 'mod-variables-search',
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
						var sid = x=='all' ? null : x;
						Ext.each(st.modified, function(rec) {
							if (rec.dirty) {
								var a, i;
								if (o[rec.data.key] === undefined) {
									a = [rec.data.key, rec.data.controller, []];
									strs.push(a);
									o[rec.data.key] = a[2];
								}
								a = o[rec.data.key];
								if (rec.data.lock) {
									var val = '';
									Ext.each(all_tabs[x].col_mod.config, function(c, idx, all) {
										if (!c._base && (rec.data[c.dataIndex] != undefined)) {
											val = rec.data[c.dataIndex];
											return false;
										}
									});
									a.push([
										sid,
										null,
										val
									]);
								} else {
									var def_col = null;
									Ext.each(all_tabs[x].col_mod.config, function(c, idx, all) {
										if (!c._base) {
											if (rec.data[c.dataIndex] != null)
												a.push([
													sid,
													c.dataIndex,
													rec.data[c.dataIndex]
												]);
											if (def_col === null) def_col = c.dataIndex;
										}
									});
									if (!a.length) // stub
										a.push([
											sid,
											def_col,
											''
										]);
								}
							}
						});
					}
					var rqparams = {
						ajax: '',
						variables: Ext.encode(strs),
						deleted: Ext.encode(PC.dialog.mod_variables.deleted)
					};
					Ext.Ajax.request({
						url: '<?php echo $cfg['url']['base'].$cfg['directories']['core_plugins_www']; ?>/variables/<?php echo basename(__FILE__) ?>',
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
		url: '<?php echo $cfg['url']['base'].$cfg['directories']['core_plugins_www']; ?>/variables/<?php echo basename(__FILE__) ?>',
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
							var site_id = (item1[0] === null) ? 'all' : item1[0];
							if (all_tabs[site_id] === undefined) return;
							var cm = all_tabs[site_id].col_mod;
							
							// init record
							if (recs[site_id] === undefined) {
								recs[site_id] = [item[0], item[1], true]; // key, controller, lock
								for (var i=3; i<cm.getColumnCount(); i++)
									recs[site_id].push(null);
							}
							var rec = recs[site_id];
							
							// add data to record
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
						});
						for (var x in recs) {
							tab_recs[x].push(recs[x]);
							//all_tabs[x].str_store.loadData([ recs[x] ], true);
						}
							
					});
					for (var x in all_tabs) {
						all_tabs[x].str_store.loadData(tab_recs[x], true);
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

PC.plugin.variables = {
	name: PC.i18n.mod.variables.selfname,
	onclick: mod_variables_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};

</script>