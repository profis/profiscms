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

if (isset($_POST['query'])) {
	$out = array();
	foreach (get_themes() as $t)
		$out[] = array($t['theme'], $t['name']);
	echo json_encode($out);
	return;
}

if (isset($_POST['ajax'])) {
	header('Content-Type: application/json');
	header('Cache-Control: no-cache');
	
	// add
	if (isset($_POST['add']) && is_array($_POST['add'])) {
		$r_site = $db->prepare("INSERT INTO {$cfg['db']['prefix']}sites (name,theme,editor_width,editor_background) VALUES(?,?,?,?)");
		$r_front_page = $db->prepare("INSERT INTO {$cfg['db']['prefix']}pages (site,front,published,controller) VALUES(?,1,1,'')");
		$r_langs = $db->prepare("INSERT INTO {$cfg['db']['prefix']}languages (site,ln,name,nr,disabled) VALUES (?,?,?,?,?)");
		foreach ($_POST['add'] as $v)
			if (isset($v['n']) && isset($v['d']) && isset($v['l']) && isset($v['width']) && isset($v['background']) && is_array($v['l'])) {
				$r_site->execute(array(
					substr($v['n'], 0, 255),
					$v['d'],
					$v['width'],
					$v['background']
				));
				$k = $db->lastInsertId($sql_parser->Get_sequence('sites'));
				$i = 0;
				$r_front_page->execute(array($k));
				foreach ($v['l'] as $ln_id=>$ln_data) {
					if (preg_match('/^[a-z]{2}$/', $ln_id)) {
						$r_langs->execute(array($k, $ln_id, substr($ln_data['name'], 0, 255), $i++, $ln_data['disabled']));
					}
				}
			}
	}
	// update
	if (isset($_POST['s']) && is_array($_POST['s'])) {
		$r_sites = $db->prepare("UPDATE {$cfg['db']['prefix']}sites SET name=?, theme=?, editor_width=?, editor_background=?, active=? WHERE id=?");
		$r_del_langs = $db->prepare("DELETE FROM {$cfg['db']['prefix']}languages WHERE site=?");
		$r_langs = $db->prepare("INSERT INTO {$cfg['db']['prefix']}languages (site,ln,name,nr,disabled) VALUES(?,?,?,?,?)");
		foreach ($_POST['s'] as $k=>$v) {
			if (isset($v['n']) && isset($v['d']) && isset($v['l']) && isset($v['width']) && isset($v['background']) && is_array($v['l'])) {
				$sites = $site->Get_all();
				if (isset($sites[$k])) {
					if ($v['n'] != $sites[$k]) {
						$r_sites->execute(array(
							substr($v['n'], 0, 255),
							$v['d'],
							$v['width'],
							$v['background'],
							$v['active'],
							$k
						));
					}
					$r_del_langs->execute(array($k));
					$i = 0;
					foreach ($v['l'] as $ln_id=>$ln_data) {
						if (preg_match('/^[a-z]{2}$/', $ln_id)) {
							$r_langs->execute(array($k, $ln_id, substr($ln_data['name'], 0, 255), $i++, $ln_data['disabled']));
						}
					}
				}
			}
		}
	}
	// delete
	if (isset($_POST['del']) && is_array($_POST['del'])) {
		$r_pages = $db->prepare("SELECT id FROM {$cfg['db']['prefix']}pages WHERE site=?");
		$r_del_pages = $db->prepare("DELETE FROM {$cfg['db']['prefix']}pages WHERE site=?");
		$r_del_site = $db->prepare("DELETE FROM {$cfg['db']['prefix']}sites WHERE id=?");
		$r_del_langs = $db->prepare("DELETE FROM {$cfg['db']['prefix']}languages WHERE site=?");
		foreach ($_POST['del'] as $v) {
                    $sites = $site->Get_all();
			if (isset($sites[$v]) && !isset($_POST['s'][$v])) {
				$r_pages->execute(array($v));
				$ids = array();
				while ($f = $r_pages->fetch(PDO::FETCH_NUM))
					$ids[] = $f[0];
				if (count($ids)) {
					$db->query("DELETE FROM {$cfg['db']['prefix']}content WHERE pid IN (".implode(',', $ids).")");
					$r_del_pages->execute(array($v));
				}
				$r_del_site->execute(array($v));
				$r_del_langs->execute(array($v));
			}
                }
	}
        
	// return
	// copy-paste from "inc.PC.php"
	//$r = $db->query("SELECT * FROM {$cfg['db']['prefix']}sites ORDER BY id");
	$sites = $site->Get_all(false);
	/*foreach ($r->fetchAll() as $d) {
		$sites[$d['id']] = $d;
	}*/
	/*if (count($sites)) {
		$r = $db->query("SELECT site,ln,name FROM {$cfg['db']['prefix']}languages WHERE site IN (".implode(',', array_keys($sites)).") ORDER BY nr");
		if ($r) {
			while ($f = $r->fetch()) {
				$sites[$f['site']]['langs'][$f['ln']] = $f['name'];
			}
		}
	}*/
	
	// copy-paste from "admin/index.php"
	$out = array();
	foreach ($sites as $k=>$v) {
		$tmp = array();
		if (isset($v['langs']))
			foreach ($v['langs'] as $k1=>$v1) {
				array_unshift($v1, $k1);
				$tmp[] = $v1;
				//$tmp[] = array($k1, $v1);
			}
				
		//$out[] = array($k, $v['name'], $v['theme'], $tmp);
		$out[] = array($k, $v['name'], $v['theme'], $tmp, null, $v['editor_width'], $v['editor_background'], $v['mask'], $v['active']);
	}
	echo json_encode($out);
	return;
}

$mod['name'] = 'Sites &amp; Languages';
$mod['onclick'] = 'mod_sites_langs_click()';
$mod['priority'] = 20;
?>
<script type="text/javascript">
Ext.namespace('PC.plugins');

function mod_sites_langs_click() {
	
	var add_s_fn = function() {
		// find all languages into d
		var d = [];
		var dk = {};
		site_store.each(function(item) {
			item.get('lang_store').each(function(item1, ndx1, all1) {
				if (!dk[item1.get('ln_id')]) {
					dk[item1.get('ln_id')] = true;
					d.push([item1.get('ln_id'), item1.get('ln_name'), item1.get('ln_disabled')]);
				}
			});
		});
		var t = site_store.getAt(0);
		t = t ? t.get('site_dir') : '';
		var rec = new site_store.recordType({
			site_id: 0,
			site_name: '',
			site_dir: t,
			site_langs: d,
			lang_store: new Ext.data.ArrayStore({
				fields: ['ln_id', 'ln_name', 'ln_disabled'],
				idIndex: 0,
				data: d
			}),
			editor_width: 600,
			editor_background: 'white'
		});
		site_store.add(rec);
		var idx = site_store.indexOf(rec);
		site_grid.getSelectionModel().selectRow(idx);
		site_grid.startEditing(idx, 0);
	};
	var edit_s_fn = function() {
		var rec = site_grid.getSelectionModel().getSelected();
		if (rec) {
			site_grid.startEditing(site_store.indexOf(rec), 0);
		}
	};
	var del_s_fn = function() {
		if (site_grid.getStore().getCount() < 2) return;
		var rec = site_grid.getSelectionModel().getSelected();
		if (!rec) return;
		var idx = site_store.indexOf(rec);
		if (idx > 0) idx--;
		/* no confirmation required for the new sites that is not saved yet
		if (rec.get('site_id') == 0) {
			site_store.remove(rec);
			site_grid.getSelectionModel().selectRow(idx);
		} else*/
			Ext.MessageBox.show({
				buttons: Ext.MessageBox.YESNO,
				title: PC.i18n.mod.sites_langs.msg.site_delete_title,
				msg: String.format(PC.i18n.mod.sites_langs.msg.site_delete, '"'+rec.get('site_name')+'"'),
				icon: Ext.MessageBox.QUESTION,
				maxWidth: 320,
				fn: function(btn_id) {
					if (btn_id == 'yes') {
						site_store.remove(rec);
						site_grid.getSelectionModel().selectRow(idx);
					}
				}
			});
	};
	var save_s_fn = function() {
		if (re.editing) return;
		var rqparams = {
			ajax: ''
		};
		var del_list = PC.global.SITES.slice(0);
		var brk = false;
		var newi = 0;
		site_store.each(function(rec) {
			Ext.each(del_list, function(item, ndx, all) {
				if (item[0] == rec.get('site_id')) {
					del_list.splice(ndx, 1);
					return false;
				}
			});
			if (!rec.data.lang_store.getCount()) {
				site_grid.getSelectionModel().selectRecords([rec]);
				Ext.MessageBox.alert(PC.i18n.error, PC.i18n.mod.sites_langs.error.lang_none);
				brk = true;
				return false;
			}
			if (rec.get('site_id') == 0) { // new site
				rqparams['add['+newi+'][n]'] = rec.get('site_name');
				rqparams['add['+newi+'][d]'] = rec.get('site_dir');
				rqparams['add['+newi+'][width]'] = rec.get('editor_width');
				rqparams['add['+newi+'][background]'] = rec.get('editor_background');
				rqparams['add['+newi+'][active]'] = rec.get('active');
				rec.data.lang_store.each(function(item) {
					rqparams['add['+newi+'][l]['+item.get('ln_id')+'][name]'] = item.get('ln_name');
					rqparams['add['+newi+'][l]['+item.get('ln_id')+'][disabled]'] = item.get('ln_disabled');
				});
				newi++;
			} else { // old site
				rqparams['s['+rec.get('site_id')+'][n]'] = rec.get('site_name');
				rqparams['s['+rec.get('site_id')+'][d]'] = rec.get('site_dir');
				rqparams['s['+rec.get('site_id')+'][width]'] = rec.get('editor_width');
				rqparams['s['+rec.get('site_id')+'][background]'] = rec.get('editor_background');
				rqparams['s['+rec.get('site_id')+'][active]'] = rec.get('active');
				rec.data.lang_store.each(function(item) {
					rqparams['s['+rec.get('site_id')+'][l]['+item.get('ln_id')+'][name]'] = item.get('ln_name');
					rqparams['s['+rec.get('site_id')+'][l]['+item.get('ln_id')+'][disabled]'] = item.get('ln_disabled');
				});
			}
		});
		if (brk) return;
		var do_rq = function() {
			Ext.Ajax.request({
				url: '<?php echo $cfg['url']['base'].$cfg['directories']['core_plugins_www']; ?>/sites/<?php echo basename(__FILE__) ?>',
				params: rqparams,
				method: 'POST',
				callback: function(opts, success, rspns) {
					if (success && rspns.responseText) {
						try {
							var data = Ext.decode(rspns.responseText);
							PC.global.SITES = data.slice(0);
							PC.admin.restartTinyMCEs();
							PC.global.site_select.getStore().loadData(PC.global.SITES);
							var rec = PC.global.site_select.getStore().getById(PC.global.site);
							if (!rec) {
								rec = PC.global.site_select.getStore().getAt(0);
								if (rec) {
									PC.global.site = rec.get('id');
									PC.global.pid = 0;
									PC.global.tree_pages.setSite(PC.global.site);
									load_content(); //fix?
								}
							}
							PC.global.site_select.setValue(PC.global.site);
							
							PC.global.ln_select.getStore().loadData(rec.get('langs'));
							var rec = PC.global.ln_select.getStore().getById(PC.global.ln);
							if (!rec) {
								rec = PC.global.ln_select.getStore().getAt(0);
								if (rec) {
									PC.global.ln = rec.get('ln_id');
									PC.global.tree_pages.setLn(PC.global.ln);
								}
							}
							PC.global.ln_select.setValue(PC.global.ln);
							
							w.close();
							return; // OK
						} catch(e) {console.log(e);};
					}
					Ext.MessageBox.show({
						title: PC.i18n.error,
						msg: PC.i18n.msg.error.data.save,
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.ERROR
					});
				}
			});
		};
		if (del_list.length == 0) {
			do_rq();
		} else {
			rqparams['del[]'] = [];
			var lst = [];
			Ext.each(del_list, function(item, ndx, all) {
				rqparams['del[]'].push(item[0]);
				lst.push(item[1]);
			});
			Ext.MessageBox.show({
				buttons: Ext.MessageBox.OKCANCEL,
				title: PC.i18n.mod.sites_langs.msg.r_u_sure,
				msg: PC.i18n.mod.sites_langs.msg.del_all_pages+
					'<ul style="list-style-type:disc; margin-left:30px; padding:10px 0 0;"><li>'+lst.join('</li><li>')+'</li></ul>',
				icon: Ext.MessageBox.WARNING,
				maxWidth: 320,
				fn: function(btn_id) {
					if (btn_id == 'ok')
						do_rq();
				}
			});
		}
	}
	
	var site_store = new Ext.data.ArrayStore({
		fields: ['site_id', 'site_name', 'site_dir', 'site_langs', 'lang_store','editor_width','editor_background', 'mask', 'active'],
		idIndex: 0,
		data: PC.global.SITES.slice(0)
	});
	var lang_store = null;
	site_store.each(function(rec) {
		rec.data.lang_store = new Ext.data.ArrayStore({
			fields: ['ln_id', 'ln_name', 'ln_disabled'],
			idIndex: 0,
			data: rec.data.site_langs
		});
		if (!lang_store) lang_store = rec.data.lang_store;
	});
	var site_grid = new Ext.grid.EditorGridPanel({
		id: 'site_grid',
		width: 560,
		store: site_store,
		columns: [
			{	xtype: 'gridcolumn',
				id: 'PC_sites_dialog_autoexpand_column',
				header: PC.i18n.site,
				dataIndex: 'site_name',
				sortable: false,
				menuDisabled: true,
				editor: {
					xtype: 'textfield',
					validator: function(val) {
						if (val == '') return PC.i18n.mod.sites_langs.error.site_empty;
						return true;
					},
					listeners: {
						afterrender: function(ed) {
							ed.gridEditor.on('canceledit', function(ed, val, origval) {
								if (origval == '') {
									//var idx = site_store.indexOf(ed.record);
									//if (idx > 0) idx--;
									site_store.remove(ed.record);
									//site_grid.getSelectionModel().selectRow(idx);
									site_grid.getSelectionModel().selectRow(0);
								}
							});
							//ed.gridEditor.on('startedit', function(be, val) {
							//	this.field.selectText();
							//});
						}
					}
				},
				width: 100
			},
			{	xtype: 'gridcolumn',
				header: PC.i18n.mod.sites_langs.theme,
				dataIndex: 'site_dir',
				sortable: false,
				menuDisabled: true,
				editor: {
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['theme', 'name'],
						url: '<?php echo $cfg['url']['base'].$cfg['directories']['core_plugins_www']; ?>/sites/<?php echo basename(__FILE__) ?>',
						data: <?php
							$out = array();
							foreach (get_themes() as $t)
								$out[] = array($t['theme'], $t['name']);
							echo json_encode($out);
							?>
					},
					displayField: 'name',
					valueField: 'theme',
					value: '',
					forceSelection: true,
					triggerAction: 'all',
					editable: false
				},
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					var rec = PC.global.themes[value];
					if (rec) return rec.name;
					if (value) return '<span style="text-decoration:line-through;">'+value+'</span>';
					return '—'; // &mdash;
				},
				width: 110
			},
			{	xtype: 'gridcolumn',
				header: 'Editor width',
				dataIndex: 'editor_width',
				sortable: false,
				menuDisabled: true,
				editor: {
					xtype: 'numberfield',
					allowDecimals: false,
					editable: true
				},
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					return value+'px';
				},
				width: 80
			},
			{	xtype: 'gridcolumn',
				header: 'Background',
				dataIndex: 'editor_background',
				sortable: false,
				menuDisabled: true,
				editor: {
					xtype: 'textfield',
					editable: true
				},
				width: 80
			},
			{	xtype: 'gridcolumn',
				header: 'Status',
				dataIndex: 'active',
				sortable: false,
				menuDisabled: true,
				width: 120,
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					return (value==='1'?'Active':'Under construction');
				},
				editor: {
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['value', 'name'],
						idIndex: 0,
						data: [
							['0', 'Under construction'],
							['1', 'Active']
						]
					},
					valueField: 'value',
					displayField: 'name',
					triggerAction: 'all',
					forceSelection: true,
					editable: false
				}
			}
			/*{	xtype: 'gridcolumn',
				header: 'Background',
				dataIndex: 'editor_background',
				sortable: false,
				menuDisabled: true,
				editor: {
					xtype: 'colorfield',
					regex: /.* /,
					allowBlank: true,
					listeners: {
						select: function(cf, val) {
							//console.log(site_grid.getSelectionModel().selections.map[1].data);
							site_grid.getSelectionModel().selections.map[1].data.color = val;
							//Ext.getCmp('site_grid').getStore().reload();
						},
						change: function(cf, val, oldval) {
							//site_grid.getSelectionModel().selections.map[1].data.color = val;
							//Ext.getCmp('site_grid').getStore().reload();
						}
					},
					detectFontColor: function() {
						if (this.menu && this.menu.picker.rawValue) {
							var val = this.menu.picker.rawValue;
						} else {
							var pcol = PC.utils.color2Hex(this.value);
							//if (console && console.log) console.log(pcol);
							if (pcol) {
								var val = [
									parseInt(pcol.slice(0, 2), 16),
									parseInt(pcol.slice(2, 4), 16),
									parseInt(pcol.slice(4, 6), 16)
								];
							} else {
								this.el.setStyle('color', 'black');
								this.el.setStyle('background', 'white');
								return;
							}
						}
						var avg = val[0]*0.299 + val[1]*0.587 + val[2]*0.114;
						this.el.setStyle('color', (avg >= 128) ? 'black' : 'white');
					}
				},
				width: 80
			}
			*/
		],
		autoExpandColumn: 'PC_sites_dialog_autoexpand_column',
		selModel: new Ext.grid.RowSelectionModel({
			moveEditorOnEnter: false,
			singleSelect: true,
			listeners: {
				selectionchange: function(sm) {
					if (sm.getCount()) {
						var rec = sm.getSelected();
						lang_store = rec.get('lang_store');
						lang_grid.reconfigure(lang_store, lang_grid.getColumnModel());
					}
					w.del_s_btn.setDisabled(site_store.getCount() < 2);
				}
			}
		}),
		listeners: {
			keypress: function(e) {
				if (e.getKey() === e.INSERT) add_s_fn();
				if (e.getKey() === e.F2) edit_s_fn();
				if (e.getKey() === e.DELETE) del_s_fn();
			},
			containerdblclick: function(g, e) {
				if (e.target == g.view.scroller.dom)
					add_s_fn();
			}
		},
		enableColumnMove: false,
		flex: 1,
		cls: 'only-right-border' // dirty hack
	});
	// DIRTY HACK: select first row as soon as it's rendered
	(function(){
		if (site_grid.getView().getRow(0)) // if ready
			site_grid.getSelectionModel().selectFirstRow();
		else
			if (site_grid.getStore().getCount())
				arguments.callee.defer(1);
	}).defer(1);
	
	var re = new Ext.ux.grid.RowEditor({
		saveText: Ext.Msg.buttonText.ok,
		clicksToEdit: 2,
		errorSummary: false,
		listeners: {
			beforeedit: function(ed, idx) {
				if (ed.editing)
					if (ed.record.get('ln_id') == '')
						return false;
			},
			canceledit: function(ed, canceled) {
				var rec = ed.record;
				if (rec.get('ln_id') == '')
					lang_store.remove(rec);
			}
		}
	});
	var add_fn = function() {
		if (re.editing) return;
		var sel = lang_grid.getSelectionModel().getSelected();
		var rec = new lang_store.recordType({
			ln_id: '',
			ln_name: '',
			ln_disabled: ''
		});
		var idx;
		if (sel) {
			idx = lang_store.indexOf(sel);
			lang_store.insert(idx, rec);
		} else {
			lang_store.add(rec);
			idx = lang_store.indexOf(rec);
		}
		lang_grid.getSelectionModel().selectRow(idx);
		re.startEditing(idx, true);
	};
	var edit_fn = function() {
		var sel = lang_grid.getSelectionModel().getSelected();
		if (sel) {
			var idx = lang_store.indexOf(sel);
			lang_grid.getSelectionModel().selectRow(idx);
			re.startEditing(idx, true);
		}
	};
	var del_fn = function() {
		if (re.editing) return;
		Ext.MessageBox.show({
			title: PC.i18n.msg.title.confirm,
			msg: PC.i18n.msg.confirm_delete,
			buttons: Ext.MessageBox.YESNO,
			icon: Ext.MessageBox.WARNING,
			fn: function(clicked) {
				if (clicked == 'yes') {
					lang_grid.getSelectionModel().each(function(rec) {
						lang_store.remove(rec);
					});
				}
			}
		});
	};
	var lang_grid = new Ext.grid.GridPanel({
		flex: 1,
		border: false,
		store: lang_store,
		plugins: [
			new Ext.ux.dd.GridDragDropRowOrder({
				copy: false,
				scrollable: true,
				targetCfg: {}
			}),
			re
		],
		enableColumnMove: false,
		columns: [
			{
				xtype: 'gridcolumn',
				header: PC.i18n.mod.sites_langs.id,
				dataIndex: 'ln_id',
				sortable: false,
				menuDisabled: true,
				editor: {
					xtype: 'textfield',
					minLength: 2,
					maxLength: 2,
					validator: function(val) {
						if (!val.match(/^[a-z]{2}$/)) return PC.i18n.mod.sites_langs.error.id_bad;
						var id_idx = lang_store.findExact('ln_id', val);
						if (id_idx != -1)
							if (lang_store.getAt(id_idx) != re.record)
								return PC.i18n.mod.sites_langs.error.id_exists;
						return true;
					}
				},
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					return '<div class="flag" style="float:left;margin:1px 6px 0 0;background-position:'+PC.utils.getFlagOffsets(value)+'"></div>'+value;
				},
				fixed: true,
				width: 60
			},{
				xtype: 'gridcolumn',
				header: PC.i18n.mod.sites_langs.name,
				dataIndex: 'ln_name',
				sortable: false,
				menuDisabled: true,
				editor: {
					xtype: 'textfield',
					maxLength: 255,
					validator: function(val) {
						if (val == '') return PC.i18n.mod.sites_langs.error.lang_empty;
						return true;
					}
				},
				width: 150
			},
			{	xtype: 'gridcolumn',
				header: PC.i18n.mod.sites_langs.activated,
				dataIndex: 'ln_disabled',
				sortable: false,
				menuDisabled: true,
				width: 100,
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					//Values are flipped: 1 means no; 0 means yes.
					//This is because in db there is field 'disabled' and in frontend there is column 'activated'
					return (value=='1'?PC.i18n.no:PC.i18n.yes);
				},
				editor: {
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['value', 'name'],
						idIndex: 0,
						data: [
							['0', PC.i18n.yes],
							['1', PC.i18n.no]
						]
					},
					validator: function(val) {
						return true;
					},
					valueField: 'value',
					displayField: 'name',
					forceSelection: true,
					triggerAction: 'all'
				}
			}
		],
		listeners: {
			containerdblclick: add_fn,
			containerclick: function(e) {
				if (!re.editing)
					this.getSelectionModel().clearSelections();
			},
			keypress: function(e) {
				if (e.getKey() === e.INSERT) add_fn();
				if (e.getKey() === e.F2) edit_fn();
				if (e.getKey() === e.DELETE) del_fn();
			}
		},
		tbar: [
			{
				iconCls: 'icon-add',
				text: PC.i18n.add,
				handler: add_fn
			},{
				iconCls: 'icon-edit',
				text: PC.i18n.edit,
				handler: edit_fn,
				ref: '../edit_btn',
				disabled: true
			},{
				iconCls: 'icon-delete',
				text: PC.i18n.del,
				handler: del_fn,
				ref: '../del_btn',
				disabled: true
			}
		]
	});
	lang_grid.getSelectionModel().on('selectionchange', function(sm) {
		var dis = (sm.getCount() == 0);
		lang_grid.edit_btn.setDisabled(dis);
		var only_1 = (sm.getCount()==1 && sm.getSelected().get('site_id')==1);
		lang_grid.del_btn.setDisabled(dis || only_1);
	});
	var w = new PC.ux.Window({
		modal: true,
		title: PC.i18n.mod.sites_langs.selfname,
		width: 900,
		height: 400,
		layout: 'hbox',
		layoutConfig: {
			align: 'stretch'
		},
		tbar: [
			{	iconCls: 'icon-add',
				text: PC.i18n.add,
				handler: add_s_fn
			},
			{	iconCls: 'icon-save',
				text: PC.i18n.save,
				handler: save_s_fn
			},
			{	iconCls: 'icon-edit',
				text: PC.i18n.edit,
				handler: edit_s_fn
			},
			{	iconCls: 'icon-delete',
				text: PC.i18n.del,
				ref: '../del_s_btn',
				handler: del_s_fn
			}
		],
		items: [
			site_grid,
			lang_grid
		],
		buttons: [
			{
				text: PC.i18n.close,
				handler: function() {
					w.close();
				}
			}
		]
	});
	w.show();
}

PC.plugin.sites_langs = {
	name: PC.i18n.mod.sites_langs.selfname,
	onclick: mod_sites_langs_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};

</script>