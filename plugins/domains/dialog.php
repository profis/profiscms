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
require_once '../../admin/admin.php';

if (isset($_POST['ajax'])) {
	header('Content-Type: application/json');
	header('Cache-Control: no-cache');
	if (isset($_POST['domains'])) {
		if (!is_array($_POST['domains']))
			$_POST['domains'] = array();
		foreach ($_POST['domains'] as $v) {
			// abort on any mismatch
			if (!(isset($v['mask']) && isset($v['site']) && isset($v['ln']))) return;
			if ($v['mask'] == '') return;
			if (!($v['site']=='' || is_numeric($v['site']))) return;
			if (!($v['ln']=='' || preg_match('/^\w{2}$/', $v['ln']))) return;
		}
		
		if (!$db->query("TRUNCATE TABLE {$cfg['db']['prefix']}domains"))
			$db->query("CREATE TABLE {$cfg['db']['prefix']}domains (
				`mask` tinytext NOT NULL,
				`site` int default NULL,
				`ln` varchar(2) default NULL,
				`nr` int NOT NULL default '0')");
		$i = 0;
		$r = $db->prepare("INSERT INTO {$cfg['db']['prefix']}domains (mask,site,ln,nr) values(?,?,?,?)");
		foreach ($_POST['domains'] as $v)
			$r->execute(array(
				substr(mask2sqlmask($v['mask']), 0, 255),
				($v['site']==''?null:$v['site']),
				($v['ln']==''?null:$v['ln']),
				$i++
			));
		echo '[]';
		return;
	}
	$out = array();
	$r = $db->query("SELECT mask,site,ln FROM {$cfg['db']['prefix']}domains ORDER BY nr");
	while ($f = $r->fetch(PDO::FETCH_NUM)) {
		$f[0] = sqlmask2mask($f[0]); // mask
		$f[1] = $f[1] ? $f[1] : ''; // site
		$f[2] = $f[2] ? $f[2] : ''; // ln
		$out[] = $f;
	}
	echo json_encode($out);
	return;
}

$mod['name'] = 'Domains';
$mod['onclick'] = 'mod_domains_click()';
$mod['priority'] = 30;

?>
<script type="text/javascript">
Ext.namespace('PC.plugins');
function mod_domains_click() {
	var stor = new Ext.data.ArrayStore({
		fields: ['dmn_mask', 'dmn_site', 'dmn_ln'],
		data: []
	});
	// collect all languages into ln_data array
	var ln_data = [['', '—']];
	var ln_tst = {};
	PC.global.site_select.getStore().each(function(rec) {
		Ext.each(rec.get('langs'), function(item, ndx, all) {
			if (!ln_tst[item[0]]) {
				ln_tst[item[0]] = true;
				ln_data.push(item);
			}
		});
	});
	var re = new Ext.ux.grid.RowEditor({
		saveText: 'OK',
		clicksToEdit: 2,
		listeners: {
			beforeedit: function(ed, idx) {
				if (ed.editing)
					if (ed.record.get('dmn_mask') == '')
						return false;
			},
			canceledit: function(ed, canceled) {
				var rec = ed.record;
				if (rec.get('dmn_mask') == '')
					stor.remove(rec);
			}
		}
	});
	var add_fn = function() {
		if (re.editing) return;
		var sel = grd.getSelectionModel().getSelected();
		var rec = new stor.recordType({
			dmn_mask: '',
			dmn_site: '',
			dmn_ln: ''
		});
		var idx;
		if (sel) {
			idx = stor.indexOf(sel);
			stor.insert(idx, rec);
		} else {
			stor.add(rec);
			idx = stor.indexOf(rec);
		}
		grd.getSelectionModel().selectRow(idx);
		re.startEditing(idx, true);
	};
	var edit_fn = function() {
		var sel = grd.getSelectionModel().getSelected();
		if (sel) {
			var idx = stor.indexOf(sel);
			grd.getSelectionModel().selectRow(idx);
			re.startEditing(idx, true);
		}
	};
	var del_fn = function() {
		if (re.editing) return;
		grd.getSelectionModel().each(function(rec) {
			stor.remove(rec);
		});
	};
	var grd = new Ext.grid.GridPanel({
		border: false,
		store: stor,
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
				header: PC.i18n.mod.domains.mask,
				dataIndex: 'dmn_mask',
				sortable: false,
				editor: {
					xtype: 'textfield',
					validator: function(val) {
						if (!val.length) return PC.i18n.mod.domains.error.mask_empty;
						var id_idx = stor.findExact('dmn_mask', val);
						if (id_idx != -1)
							if (stor.getAt(id_idx) != re.record)
								return PC.i18n.mod.domains.error.mask_exists;
						return true;
					}
				},
				width: 200
			},{
				xtype: 'gridcolumn',
				header: PC.i18n.site,
				dataIndex: 'dmn_site',
				sortable: false,
				editor: {
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['site_id', 'site_name'],
						data: [['', '—']].concat(PC.global.SITES)
					},
					displayField: 'site_name',
					valueField: 'site_id',
					editable: false,
					forceSelection: true,
					triggerAction: 'all'
				},
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					var rec = PC.global.site_select.getStore().getById(value);
					if (rec) return rec.get('name');
					if (value) return '<span style="text-decoration:line-through">'+value+'</span>';
					return '—'; // &mdash;
				},
				width: 150
			},{
				xtype: 'gridcolumn',
				header: PC.i18n.default_language,
				dataIndex: 'dmn_ln',
				sortable: false,
				editor: {
					xtype: 'profis_flagcombo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['ln_id', 'ln_name'],
						data: ln_data
					},
					displayField: 'ln_name',
					valueField: 'ln_id',
					editable: false,
					forceSelection: true,
					triggerAction: 'all'
				},
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					for (var i=0; i<ln_data.length; i++)
						if (ln_data[i][0] == value) {
							return '<div class="flag" style="float:left;margin:1px 4px 0 0;background-position:'+PC.utils.getFlagOffsets(value)+'"></div>'+ln_data[i][1];
						}
					if (value) return '<div class="flag" style="float:left;margin:1px 4px 0 0;background-position:'+PC.utils.getFlagOffsets(value)+'"></div>'+'<span style="text-decoration:line-through">'+value+'</span>';
					return '<div class="flag" style="float:left;margin:1px 4px 0 0"></div>'+'—'; // &mdash;
				},
				width: 140
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
	grd.getSelectionModel().on('selectionchange', function(sm) {
		var dis = (sm.getCount() == 0);
		grd.edit_btn.setDisabled(dis);
		grd.del_btn.setDisabled(dis);
	});
	var w = new PC.ux.Window({
		modal: true,
		title: PC.i18n.mod.domains.selfname,
		width: 530,
		height: 300,
		layout: 'fit',
		layoutConfig: {
			align: 'stretch'
		},
		items: [
			grd
		],
		buttonAlign: 'left',
		buttons: [
			{xtype: 'tbtext', text: PC.i18n.mod.domains.mask_example},
			{xtype: 'tbfill'},
			{
				text: Ext.Msg.buttonText.ok,
				disabled: true,
				ref: '../ok_btn',
				handler: function() {
					if (re.editing) return;
					var rqparams = {
						ajax: ''
					};
					if (stor.getCount()) {
						for (var i=0; i<stor.getCount(); i++) {
							var d = stor.getAt(i).data;
							rqparams['domains['+i+'][mask]'] = d.dmn_mask;
							rqparams['domains['+i+'][site]'] = d.dmn_site;
							rqparams['domains['+i+'][ln]'] = d.dmn_ln;
						}
					} else
						rqparams.domains = '';
					Ext.Ajax.request({
						url: '<?php echo $cfg['url']['base'].$cfg['directories']['plugins']; ?>/domains/<?php echo basename(__FILE__) ?>',
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
		url: '<?php echo $cfg['url']['base'].$cfg['directories']['plugins']; ?>/domains/<?php echo basename(__FILE__) ?>',
		params: {
			ajax: ''
		},
		method: 'POST',
		callback: function(opts, success, rspns) {
			if (success && rspns.responseText) {
				try {
					var data = Ext.decode(rspns.responseText);
					stor.loadData(data);
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

PC.plugin.domains = {
	name: PC.i18n.mod.domains.selfname,
	onclick: mod_domains_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};

</script>