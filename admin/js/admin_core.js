// Path to the blank image must point to a valid location on your server
Ext.BLANK_IMAGE_URL = 'ext/resources/images/default/s.gif';
// Main application entry point

PC_acl_manager = {
	has_access_to_pages: function() {
		return PC.global.permissions.admin || PC.global.permissions.pages;
	}
}


Ext.onReady(function(){
	
	// write your application here
	Ext.QuickTips.init();
	// Copy strings from PC.langs[admin_ln] to PC.i18n
	//PC.utils.localize();
	//if (PC.global.permissions.admin) {
		PC.trash_page = function(n) {
			Ext.MessageBox.show({
				title: PC.i18n.msg.title.confirm,
				msg: String.format(PC.i18n.msg.confirm_delete, '"'+n.text+'"'),
				buttons: Ext.MessageBox.YESNO,
				icon: Ext.MessageBox.WARNING,
				fn: function(rslt) {
					switch (rslt) {
						case 'yes':
							// move to recycle bin
							Ext.Ajax.request({
								url: 'ajax.page.php?action=delete',
								params: {
									site: PC.global.site,
									id: n.id,
									old_idp: n.parentNode.id
								},
								method: 'POST',
								callback: function(opts, success, rspns) {
									if (success && rspns.responseText) {
										try {
											var data = Ext.decode(rspns.responseText);
											if (data.success) {
												var trash = PC.tree.component.getNodeById(-1);
												if (trash) {
													if (!trash.childNodes.length)
														trash.collapse();
													trash.insertBefore(n, trash.firstChild);
													return; // OK
												}
												n.remove();
												if (PC.global.pid == n.id) Load_home_page();
												return; // OK
											}
										} catch(e) {};
									}
									Ext.MessageBox.show({
										title: PC.i18n.error,
										msg: String.format(PC.i18n.msg.error.page.del, ''),
										buttons: Ext.MessageBox.OK,
										icon: Ext.MessageBox.ERROR
									});
								}
							});
							break;
						default: // case 'no':
					}
				}
			});
		}
		
		PC.global.page = {};
		PC.global.site_select = new PC.ux.SiteCombo({
			ref: '../../_site_select',
			listeners: {
				beforeselect: function(cmbbox, rec, ndx) {
					if (PC.global.site == rec.get('id')) return;
					return save_prompt(function() {
						PC.global.site = rec.get('id');
						PC.global.site_select.setValue(PC.global.site);
						PC.global.page = {};
						PC.admin._editor_ln_select.disable();
						PC.global.pid = 0;
						PC.tree.component.setSite(PC.global.site);
						PC.admin.restartTinyMCEs();
						clear_fields(); //load_content(); // unload
						PC.global.ln_select.getStore().loadData(rec.get('langs'));
						if (!PC.global.ln_select.getStore().getById(PC.global.tree_ln)) {
							var lnr = PC.global.ln_select.getStore().getAt(0);
							if (lnr) {
								PC.global.tree_ln = lnr.get('ln_id');
								PC.tree.component.setLn(PC.global.tree_ln);
							}
						}
						if (!PC.global.ln_select.getStore().getById(PC.global.ln)) {
							var lnr = PC.global.ln_select.getStore().getAt(0);
							if (lnr) {
								PC.global.ln = lnr.get('ln_id');
								PC.admin._editor_ln_select.topToolbar.items.items[0].pressed = true;
							}
						}
						PC.global.ln_select.setValue(PC.global.tree_ln);
						PC.tree.menus.current_node = undefined;
					});
				}
			}
		});
		PC.global.ln_select = new PC.ux.LnCombo({
			ref: '../../_ln_select',
			listeners: {
				beforeselect: function(cmbbox, rec, ndx) {
					if (PC.global.tree_ln == rec.get('ln_id')) return;
					PC.global.tree_ln = rec.get('ln_id');
					PC.global.ln_select.setValue(rec.get('ln_id'));
					PC.tree.component.setLn(rec.get('ln_id'));
					Render_redirects_from();
					/*return save_prompt(function() {
							PC.global.tree_ln = rec.get('ln_id');
							PC.global.ln_select.setValue(rec.get('ln_id'));
							PC.tree.component.setLn(rec.get('ln_id'));
							//load_content();
						});*/
				}
			}
		});
		PC.global.language_select = new Ext.Panel({
			bodyBorder: false,
			items: PC.global.ln_select,
			layout: 'form', padding: '5px',
			labelWidth: 150,
			height: 30
		});
		function archive_load() {
			var arch = Ext.getCmp('grid_archive');
			var ver = arch.getSelectionModel().getSelected();
			//alert(ver.get('id'));
			if (!ver) return;
			if (ver.get('id') < 1) { // current version
				//alert('current_version');
				Load_to_editor(PC.global.ln, true);
				return;
				//clear_fields();
				//return load_content();
			}
			Ext.MessageBox.show({
				title: PC.i18n.msg.title.loading,
				msg: PC.i18n.msg.load.prev_ver,
				width: 300,
				wait: true,
				waitConfig: {interval:100}
			});
			Ext.Ajax.request({
				url: 'ajax.pagetree.php',
				params: {
					'archive': ver.get('id')
				},
				method: 'POST',
				callback: function(opts, success, rspns) {
					Ext.MessageBox.hide();
					if (success && rspns.responseText) {
						try {
							var data = Ext.decode(rspns.responseText);
							Ext.each(PC.global.db_flds, function(i) {
								if (data[i] == undefined) return;
								var fld = Ext.getCmp('db_fld_'+i);
								fld.setValue(data[i]);
							});
							arch.getStore().each(function(rec) {
								rec.set('sel', rec.get('id') == opts.params.archive);
							});
							return; // OK
						} catch(e) {};
					}
					Ext.MessageBox.show({
						title: PC.i18n.error,
						msg: PC.i18n.msg.error.prev_ver.load,
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.ERROR
					});
				}
			});
		}
		function archive_del() {
			var arch = Ext.getCmp('grid_archive');
			var todel = [];
			arch.getSelectionModel().each(function(i) {
				var id = i.get('id');
				if (id)
					todel.push(id);
			});
			if (!todel.length) return;
			Ext.MessageBox.show({
				title: PC.i18n.msg.title.confirm,
				msg: String.format(PC.i18n.msg.del_prev_ver, todel.length),
				buttons: Ext.MessageBox.YESNO,
				icon: Ext.MessageBox.WARNING,
				fn: function(rslt) {
					switch (rslt) {
					case 'yes':
						Ext.Ajax.request({
							url: 'ajax.pagetree.php',
							params: {
								archive_delete: Ext.encode(todel),
								id: PC.global.pid,
								ln: PC.global.ln
							},
							method: 'POST',
							callback: function(opts, success, rspns) {
								if (success && rspns.responseText) {
									try {
										var data = Ext.decode(rspns.responseText);
										PC.global.page.content[PC.global.ln].archive = Parse_archive_store(data, true);
										Update_content_archive();
										return; // OK
									} catch(e) {};
								}
								Ext.MessageBox.show({
									title: PC.i18n.error,
									msg: PC.i18n.msg.error.prev_ver.load,
									buttons: Ext.MessageBox.OK,
									icon: Ext.MessageBox.ERROR
								});
							}
						});
						break;
					}
				}
			});
		}
	//}
	// ********** ROOT **********
	PC.editors.Register('page', null, {
		ref: '../../_editor_ln_select',
		xtype: 'tabpanel',
		disabled: true,
		disabledClass: 'x-editor-disabled',
		activeTab: 0,
		deferredRender: false,
		tbar: new PC.ux.LanguageBar(),
		/*new Ext.Toolbar({
			listeners: {
				afterrender: function(tbar) {
					tbar.Reload();
					Reload_content_language_list(tbar);
				}
			}
		}),*/
		items: [
			{	id: 'db_tab_text',
				xtype: 'container',
				title: PC.i18n.tab.text,
				title_value: PC.i18n.tab.text,
				layout: 'fit',
				border: false,
				items: [{
					xtype: 'profis_tinymce',
					ref: '../../../_fld_text',
					id: 'db_fld_text'
				}]
			},
			{	id: 'db_tab_info',
				xtype: 'container',
				title: PC.i18n.tab.info,
				title_value: PC.i18n.tab.info,
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '../../../_fld_info',
					id: 'db_fld_info'
				}]
			},
			{	id: 'db_tab_info2',
				xtype: 'container',
				title: PC.i18n.tab.info2,
				title_value: PC.i18n.tab.info2,
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '../../../_fld_info2',
					id: 'db_fld_info2'
				}]
			},
			{	id: 'db_tab_info3',
				xtype: 'container',
				title: PC.i18n.tab.info3,
				title_value: PC.i18n.tab.info3,
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '../../../_fld_info3',
					id: 'db_fld_info3'
				}]
			},
			{	id: 'db_tab_info_mobile',
				xtype: 'container',
				title: PC.i18n.tab.info_mobile,
				title_value: PC.i18n.tab.info_mobile,
				layout: 'fit',
				items: [{
					xtype: 'profis_tinymce',
					ref: '../../../_fld_info_mobile',
					id: 'db_fld_info_mobile'
				}]
			},
			{	id: 'db_tab_seo',
				title: PC.i18n.tab.seo,
				title_value: PC.i18n.tab.seo,
				layout: 'hbox',
				layoutConfig: {
					align: 'stretch'
				},
				defaults: {
					flex: 1,
					bodyCssClass: 'x-border-layout-ct',
					border: false,
					layout: 'fit'
				},
				items: [
					{	border: false,
						items: [{
							border: false,
							autoScroll: true,
							bodyCssClass: 'x-border-layout-ct',
							items: [{
								style: 'padding:0 6px 6px 6px',
								border: false,
								defaults: {
									border: false
								},
								cls: 'x-panel-mc',
								items: [
									//name
									{	id: 'db_fld_name_title',
										xtype: 'box',
										style: 'padding: 6px',
										html: PC.i18n.name.replace(/\s/, '&nbsp;') + ':'
									},
									{	xtype: 'container',
										id: 'db_fld_name_container',
										layout: 'fit',
										height: 22,
										items: [{
											xtype: 'textfield',
											//xtype: 'profis_multilnfield',
											editorCfg: {
												title: PC.i18n.name
											},
											ref: '../../../../../../../../_fld_name',
											id: 'db_fld_name'
										}]
									},
									{	id: 'db_fld_custom_name_title',
										xtype: 'box',
										style: 'padding: 6px',
										html: PC.i18n.custom_name.replace(/\s/, '&nbsp;') + ':'
									},
									{	xtype: 'container',
										id: 'db_fld_custom_name_container',
										layout: 'fit',
										height: 22,
										items: [{
											xtype: 'textfield',
											//xtype: 'profis_multilnfield',
											editorCfg: {
												title: PC.i18n.name
											},
											ref: '../../../../../../../../_fld_custom_name',
											id: 'db_fld_custom_name'
										}]
									},
									//route
									{	id: 'db_fld_route_title',
										xtype: 'box',
										style: 'padding: 6px',
										html: PC.i18n.seo_link.replace(/\s/, '&nbsp;') + ':'
									},
									{	xtype: 'container',
										id: 'db_fld_route_container',
										layout: 'fit',
										height: 22,
										items: [{
											xtype: 'textfield',
											//xtype: 'profis_multilnfield',
											editorCfg: {
												title: PC.i18n.seo_link
											},
											ref: '../../../../../../../../_fld_i18n',
											id: 'db_fld_route'
										}]
									},
									//route lock
									{	xtype: 'container',
										id: 'db_fld_route_lock_container',
										layout: 'fit',
										style: 'text-align: right',
										items: [{
											xtype: 'checkbox',
											boxLabel: PC.i18n.page.route_lock,
											ref: '../../../../../../../../_fld_route_lock',
											id: 'db_fld_route_lock'
										}]
									},
									//permalink
									{	id: 'db_fld_permalink_title',
										xtype: 'box',
										style: 'padding: 6px',
										html: PC.i18n.seo_permalink.replace(/\s/, '&nbsp;') + ':'
									},
									{	xtype: 'container',
										id: 'db_fld_permalink_container',
										layout: 'fit',
										height: 22,
										items: [{
											xtype: 'textfield',
											//xtype: 'profis_multilnfield',
											editorCfg: {
												title: PC.i18n.seo_permalink
											},
											ref: '../../../../../../../../_fld_i18n',
											id: 'db_fld_permalink'
										}]
									},
									//title
									{	id: 'db_fld_title_title',
										xtype: 'box',
										style: 'padding: 6px',
										html: PC.i18n.title.replace(/\s/, '&nbsp;') + ':'
									},
									{	xtype: 'container',
										id: 'db_fld_title_container',
										layout: 'fit',
										height: 22,
										items: [{
											xtype: 'textfield',
											//xtype: 'profis_multilnfield',
											editorCfg: {
												title: PC.i18n.title
											},
											ref: '../../../../../../../../_fld_title',
											id: 'db_fld_title'
										}]
									},
									//description
									{	id: 'db_fld_description_title',
										xtype: 'box',
										style: 'padding: 6px',
										html: PC.i18n.desc.replace(/\s/, '&nbsp;') + ':'
									},
									{	height: 80,
										xtype: 'container',
										id: 'db_fld_description_container',
										layout: 'fit',
										items: [{
											xtype: 'textarea',
											//xtype: 'profis_multilnfield',
											autoCreate: {
												tag: 'textarea'
											},
											editorCfg: {
												defaultType: 'textarea',
												title: PC.i18n.desc,
												defaults: {
													height: 50
												}
											},
											ref: '../../../../../../../../_fld_description',
											id: 'db_fld_description'
										}]
									},
									//keywords
									{	id: 'db_fld_keywords_title',
										xtype: 'box',
										style: 'padding: 6px',
										html: PC.i18n.keywords.replace(/\s/, '&nbsp;') + ':'
									},
									{	xtype: 'container',
										id: 'db_fld_keywords_container',
										height: 80,
										layout: 'fit',
										items: [{
											xtype: 'textarea',
											//xtype: 'profis_multilnfield',
											autoCreate: {
												tag: 'textarea'
											},
											editorCfg: {
												defaultType: 'textarea',
												title: PC.i18n.keywords,
												defaults: {
													height: 50
												}
											},
											ref: '../../../../../../../../_fld_keywords',
											id: 'db_fld_keywords',
											maxLength: 255, //same as in a database, varchar(255)
											listeners: {
												change: function(field, value, old) {
													field.setValue(value.replace(/\n/g, ', '));
												}
											}
										}]
									},
									{	id: 'db_fld_ln_redirect_title',
										xtype: 'box',
										style: 'padding: 6px',
										html: PC.i18n.menu.shortcut_to.replace(/\s/, '&nbsp;') + ':'
									},
									{	id: 'db_fld_ln_redirect_container',
										xtype: 'container',
										layout: 'fit',
										items: PC.view_factory.get_shortcut_field({
											id: 'db_fld_ln_redirect',
											labelAlign: 'top'
										})
									}
								]
							}],
							tbar: [{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save}]
						}]
					}
				]
			},
			{	id: 'grid_archive',
				title: PC.i18n.prev_ver.s,
				xtype: 'grid',
				store: {
					xtype: 'arraystore',
					fields: ['id', 'time', 'username', 'sel'],
					idIndex: 0,
					data: [],
					sortInfo: {
						field: 'time',
						direction: 'DESC'
					}
				},
				selModel: new Ext.grid.RowSelectionModel({
					listeners: {
						selectionchange: function(sm) {
							var c = sm.getCount();
							sm.grid.load_btn.setDisabled(c != 1);
							var dis = (!c || (c==1 && !sm.getSelected().get('id')));
							sm.grid.del_btn.setDisabled(dis);
						},
						beforerowselect: function(sm, row, keepExisting, rec) {
							if (rec.data.sel) return false;
						}
					}
				}),
				viewConfig: {
					markDirty: false
				},
				listeners: {
					containerclick: function(g, e) {
						if (e.target == g.view.scroller.dom)
							this.getSelectionModel().clearSelections();
					},
					keypress: function(e) {
						if (e.getKey() === e.DELETE) archive_del();
					},
					rowdblclick: archive_load
				},
				enableColumnHide: false,
				columns: [
					{
						dataIndex: 'sel',
						width: 30,
						menuDisabled: true,
						fixed: true,
						hideable: false,
						renderer: function(value, metaData, record, rowIndex, colIndex, store) {
							if (value) return '<img src="images/arrow-right.gif" alt="" style="position:absolute; margin-top:-2px" />';
						}
					},{
						header: PC.i18n.save_time,
						dataIndex: 'time',
						sortable: true,
						width: 120,
						renderer: function(value, metaData, record, rowIndex, colIndex, store) {
							if (record.data.sel) return '<b>'+value+'</b>';
							else return value;
						}
					},{
						header: PC.i18n.user,
						dataIndex: 'username',
						sortable: true,
						width: 120
					}
				],
				tbar: [
					{
						text: PC.i18n.load,
						iconCls: 'icon-load',
						ref: '../load_btn',
						disabled: true,
						handler: archive_load
					},{
						text: PC.i18n.del,
						iconCls: 'icon-delete',
						ref: '../del_btn',
						disabled: true,
						handler: archive_del
					//},{
					//	text: PC.i18n.prev_ver.reset,
					//	iconCls: 'icon-refresh',
					//	handler: load_content
					}
				]
			},
			{	id: 'db_tab_properties',
				title: PC.i18n.tab.properties,
				title_value: PC.i18n.tab.properties,
				layout: 'hbox',
				layoutConfig: {
					align: 'stretch'
				},
				defaults: {
					flex: 1,
					bodyCssClass: 'x-border-layout-ct',
					border: false,
					layout: 'fit'
				},
				items: [
					{	padding: '6px 6px 6px 0',
						items: [
							{	layout: 'fit',
							border: false,
							items: [{
								layout: 'form',
								width: 250,//1000
								padding: 6,
								border: false,
								bodyCssClass: 'x-border-layout-ct',
								labelWidth: 200,
								labelAlign: 'right',
								defaults: {
									anchor: '100%'
								},
								autoScroll: true,
								items: [
									{
										xtype: 'hidden',
										ref: '../../../../../../../_fld_id',
										id: 'db_fld_id'
									},
									{	ref: '../../../../../../../_fld_controller',
										fieldLabel: PC.i18n.page.type,
										xtype: 'combo',
										id: 'db_fld_controller',
										mode: 'local',
										store: {
											xtype: 'arraystore',
											fields: ['id', 'name'],
											idIndex: 0,
											data: get_active_controllers()
										},
										displayField: 'name',
										valueField: 'id',
										value: '',
										editable: false,
										forceSelection: true,
										triggerAction: 'all',
										listeners: {
											change: function(field, value, old) {
												Render_page_actions(value);
											},
											select: function(field, record, index) {
												field.fireEvent('change', field, record.data.id);
											}
										}
									},
									{	fieldLabel: PC.i18n.page.hot,
										xtype: 'checkbox',
										ref: '../../../../../../../_fld_hot',
										id: 'db_fld_hot'
									},
									{	fieldLabel: PC.i18n.page.nomenu,
										xtype: 'checkbox',
										ref: '../../../../../../../_fld_nomenu',
										id: 'db_fld_nomenu'
									},
									{	fieldLabel: PC.i18n.page.published,
										xtype: 'checkbox',
										ref: '../../../../../../../_fld_published',
										id: 'db_fld_published'
									},
									{	fieldLabel: PC.i18n.menu.shortcut_to.replace(/\s/, '&nbsp;'),
										id: 'db_fld_redirect_container',
										xtype: 'container',
										layout: 'fit',
										items: PC.view_factory.get_shortcut_field()
									},
									{	fieldLabel: PC.i18n.page.source_id,
										id: 'db_fld_source_id_container',
										xtype: 'container',
										layout: 'fit',
										items: PC.view_factory.get_shortcut_field({id: 'db_fld_source_id'})
									},
									{	fieldLabel: PC.i18n.page.target,
										xtype: 'checkbox',
										ref: '../../../../../../../_fld_target',
										id: 'db_fld_target'
									},		
									{	ref: '../../../../../../../_fld_reference_id',
										id: 'db_fld_reference_id',
										fieldLabel: PC.i18n.reference_id,
										xtype: 'textfield',
										anchor: '50%'
										//hidden: true
									},{
										id: 'db_fld_redirects_from',
										hidden: true,
										fieldLabel: PC.i18n.page.shortcut_from,
										style: 'line-height:0.5cm;',
										xtype: 'label'
									},
									{	ref: '../../../../../../_fld_date_container',
										id: 'db_fld_date_container',
										xtype: 'fieldset',
										labelWidth: 200,
										style: {
											padding: '10px 0',
											margin: '0 0 5px 0',
											border: '2px solid #D2DCEB'
										},
										defaults: {
											anchor: '50%'
										},
										items: [
											{	fieldLabel: PC.i18n.page.show_period,
												xtype: 'checkbox',
												ref: '../../../../../../../_fld_publishing_date',
												listeners: {
													check: function(self, newval) {
														var dfld;
														if (newval) {
															//date
															dfld = Ext.getCmp('db_fld_date_from');
															dfld.enable();
															if (dfld.getValue() == '') dfld.setValue(new Date());
															dfld = Ext.getCmp('db_fld_date_to');
															dfld.enable();
															var date_to = new Date(2147483640000);
															//date_to.setFullYear(2038,0,1);
															if (dfld.getValue() == '') dfld.setValue(date_to);
															//time
															var time_from = Ext.getCmp('db_fld_time_from');
															time_from.enable();
															if (time_from.getValue() == '') time_from.setValue(new Date().format('H:i'));
															var time_to = Ext.getCmp('db_fld_time_to');
															time_to.enable();
															if (time_to.getValue() == '') time_to.setValue('23:59');
														} else {
															//date
															dfld = Ext.getCmp('db_fld_date_from');
															dfld.disable();
															dfld.setValue('');
															dfld = Ext.getCmp('db_fld_date_to');
															dfld.disable();
															dfld.setValue('');
															//time
															var time_from = Ext.getCmp('db_fld_time_from');
															time_from.disable().setValue('');
															var time_to = Ext.getCmp('db_fld_time_to');
															time_to.disable().setValue('');
														}
													}
												},
												id: 'db_fld_publishing_date'
											},
											//date from
											/*{	fieldLabel: PC.i18n.from,
												xtype: 'compositefield',
												border: false,
												autoHeight: true,
												style: 'padding:0',
												defaults: {
													hideLabel: true,
													flex: 1
												},
												items: []
											},*/
											{	fieldLabel: PC.i18n.from,
												xtype: 'datefield',
												ref: '../../../../../../../_fld_date_from',
												format: 'Y-m-d',
												disabled: true,
												id: 'db_fld_date_from'
											},
											{	xtype: 'timefield',
												format: 'H:i',
												increment: 60,
												ref: '../../../../../../../_fld_time_from',
												disabled: true,
												id: 'db_fld_time_from'
											},
											//date to
											/*{	fieldLabel: PC.i18n.to,
												xtype: 'compositefield',
												border: false,
												autoHeight: true,
												style: 'padding:0',
												defaultType: 'textfield',
												defaults: {
													hideLabel: true,
													flex: 1
												},
												items: []
											}*/
											{	fieldLabel: PC.i18n.to,
												xtype: 'datefield',
												ref: '../../../../../../../_fld_date_to',
												format: 'Y-m-d',
												disabled: true,
												id: 'db_fld_date_to'
											},
											{	xtype: 'timefield',
												format: 'H:i',
												increment: 60,
												ref: '../../../../../../../_fld_time_to',
												disabled: true,
												id: 'db_fld_time_to'
											}
										],
										listeners: {
											show: function(field) {
												Ext.getCmp('db_fld_date_from').setWidth(200);
												Ext.getCmp('db_fld_time_from').setWidth(200);
												Ext.getCmp('db_fld_date_to').setWidth(200);
												Ext.getCmp('db_fld_time_to').setWidth(200);
											}
										}
									},
									{	ref: '../../../../../../_fld_date',
										id: 'db_fld_date',
										fieldLabel: PC.i18n.date,
										xtype: 'datefield',
										anchor: '50%',
										format: 'Y-m-d',
										hidden: true,
										value: new Date().format('Y-m-d')
									},
									{	ref: '../../../../../../../_fld_time',
										id: 'db_fld_time',
										fieldLabel: PC.i18n.time,
										xtype: 'timefield',
										anchor: '50%',
										format: 'H:i',
										//hidden: true,
										//disabled: true,
										increment: 60
									}
								]
							}]
						}]
					}
				],
				tbar: [
					{xtype: 'button', text: PC.i18n.save, icon: 'images/disk.png', handler: PC.editors.Save},
					{xtype:'tbfill'},
					{	xtype: 'tbtext',
						id: 'pc_current_pid',
						text: 'ID: -',
						style: {color: '#aaa'},
						_change: function(id){
							this.setText('ID: '+ id);
						}
					}
				]
			}
		],
		listeners: {
			beforetabchange: function(panel, tab, currentTab) {
				//tab.title = '<img style="vertical-align: -2px" src="images/bullet_green.png" alt=""> '+tab.title_value;
				//console.log(tab);
				if (currentTab == undefined) return;
				var tabId = tab.getId();
				if (tabId == 'db_tab_properties') {
					PC.admin._editor_ln_select.getTopToolbar().hide();
				}
				else {
					PC.admin._editor_ln_select.getTopToolbar().show();
					//focus editor depending of which tab is opened
					switch (tabId) {
						case 'db_tab_info':
							var focus_field = 'db_fld_info';
							break;
						case 'db_tab_info2':
							var focus_field = 'db_fld_info2';
							break;
						case 'db_tab_info3':
							var focus_field = 'db_fld_info3';
							break;
						case 'db_tab_info_mobile':
							var focus_field = 'db_fld_info_mobile';
							break;	
						default:
							var focus_field = 'db_fld_text';
					}
					setTimeout(function(){
						tinyMCE.execCommand('mceFocus', false, focus_field);
					}, 100);
				}
				PC.admin._editor_ln_select.doLayout();
			},
			show: function(panel){
				panel.getTopToolbar().Reload();
			}
		},
		Load: function(editor, data, ln, freshLoad, callback) {
			//console.log('------------------------------------------------');
			//prepare data
			if (data.redirect == null) data.redirect = '';
			if (data.source_id == null) data.source_id = '';
			if (data.source_id == 0 || data.source_id == '0') {
				data.source_id = '';
			}
			/*var freshLoad = (PC.global.page == undefined);
			if (!freshLoad) {
				if (typeof PC.global.page.id != 'object') freshLoad = true;
				else if (PC.global.page.id.originalValue != data.id) freshLoad = true;
			}*/
			/*console.log('fresh? '+ freshLoad);
			console.log(PC.global.page.id);
			console.log('new id = '+ data.id);
			console.log(PC.global.ln);
			console.log(PC.global.page);*/
			if (freshLoad) {
				PC.global.page = {};
				PC.global.page.content = {};
				Ext.iterate(data.content, function(index, content) {
					PC.global.page.content[content.ln] = {};
					var content_store = PC.global.page.content[content.ln];
					Ext.iterate(content, function(field, value) {
						if (field == 'archive') {
							content_store.archive = Parse_archive_store(value);
						} else {
							content_store[field] = {};
							var field_store = content_store[field];
							field_store.value = field_store.originalValue = value;
						}
					});
				});
				Ext.iterate(data, function(field, value) {
					if (field == 'content') return;
					PC.global.page[field] = {};
					var field_store = PC.global.page[field];
					field_store.value = field_store.originalValue = value;
				});
				Ext.getCmp('pc_current_pid')._change(PC.global.pid);
				Load_to_editor(PC.global.ln);
			}
			else {
				Load_to_editor(ln);
			}
			if (typeof callback == 'function') callback();
		},
		Save: function(callback, check_if_dirty) {
			if (PC.global.pid < 1) return;
			if (check_if_dirty) if (!Page_dirty()) return;
			//Ext.get('db_fld_text_save').focus();
			Save_content_to_store(null);
			var request_params = {};
			request_params.id = PC.global.pid;
			if (request_params.id != PC.global.page.id.value) {
				return;
			}
			request_params.content = {};
			Ext.iterate(PC.global.page, function(field, value){
				//loop through pages of all languages
				if (field == 'content') {
					Ext.iterate(value, function(language, store){
						var content_store = request_params.content[language] = {};
						Ext.iterate(store, function(field, value){
							if (value != undefined) {
								if (value.value != value.originalValue) {
									content_store[field] = value.value;
								}
							}
						});
						if (isEmpty(content_store)) delete request_params.content[language];
					});
				} else { //shared page config
					if (/^(date_from|date_to)$/.test(field)) {
						if (Ext.getCmp('db_fld_publishing_date').getValue()) {
							if (value.originalValue == null) {
								request_params[field] = value.value;
							} else {
								if (value.value != '' && value.value != null)
								//if (value.value.format('Y-m-d') != value.originalValue) {
								if (value.value != value.originalValue) {
									request_params[field] = value.value;
								}
							}
						} else {
							if (value.originalValue != null) {
								request_params[field] = value.value;
							}
						}
					} else if (value.value != value.originalValue) {
						request_params[field] = value.value;
					}
				}
			});
			//console.log(request_params); Ext.Msg.hide(); return;
			request_params.gmt_offset = new Date().getTimezoneOffset();
			Ext.Ajax.request({
				url: 'ajax.page.php?action=update',
				params: {
					data: Ext.util.JSON.encode(request_params),
					return_page: true
				},
				method: 'POST',
				callback: function(options, success, r) {
					if (success && r.responseText) {
						var data = Ext.util.JSON.decode(r.responseText);
						if (data.success) {
							this.additionalParams = {};
							//set content originalValues = values (make not dirty)
							Equalize_content_values();
							Flag_editors_as_clean();
							//reload file list in the gallery (update 'files in use' marking)
							if (PC.dialog.gallery.window) {
								PC.dialog.gallery.filesStore.load();
							}
							var node = PC.tree.component.getNodeById(PC.global.pid);
							var parentNode = node.parentNode;
							node.attributes.published = data.published;
							node.attributes.hot = data.hot;
							node.attributes.nomenu = data.nomenu;
							node.attributes._redir = (data.redirect!=null?(data.redirect.length?true:false):false);
							node.attributes.controller = data.controller;
							if (node.attributes._names == undefined) node.attributes._routes = {};
							if (node.attributes._routes == undefined) node.attributes._routes = {};
							//--- Updates _names and _routes
							Ext.iterate(data.content, function(ln, fresh_content){
								node.attributes._names[ln] = fresh_content.name;
								node.attributes._routes[ln] = fresh_content.route;
							});
							//---
							Load_page_data({treeNode: node}, data);
							//init save hook
							PC.hooks.Init('page.save', {
								tree: PC.tree.component,
								params: request_params,
								data: data
							});
							Reload_content_archive();
							if (typeof callback == 'function') callback();
							if (request_params.hasOwnProperty('controller')) {
								node.reload();
							}
							return;
						}
						else if (data.errors.length) {
							alert('Error occurred while saving. Not all data has been saved.');
							alert('Please send this report to ProfIS so we can fix it:'+ var_dump(data));
						}
						return; //update successfull
					}
					Ext.MessageBox.show({
						title: PC.i18n.error,
						msg: PC.i18n.msg.error.data.save,
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.ERROR
					});
				}
			});
			return true;
		}
	});
	
	var AdminArea = [
		{	region: 'west',
			split: true,
			//collapsible: true,
			width: 300,
			layout: 'vbox',
			layoutConfig: {
				align: 'stretch',
				pack: 'start'
			},
			items: [
				{	layout: 'form',
					width: 250,//1000
					baseCls: 'x-toolbar',
					style: {
						padding: '4px 4px 0 0'
					},
					labelWidth: 60,
					labelAlign: 'right',
					items: [
						PC.global.site_select
					]
				},
				PC.global.language_select,
				PC.tree.component
			]
		},
		{	id: 'pc_editor_area',
			region: 'center',
			xtype: 'panel',
			layout: 'card',
			activeItem: 0,
			defaults: {
				border:false
			},
			items: PC.editors.Get()
		}
	];
	PC.admin = new Ext.Viewport({
		layout: 'fit',
		restartTinyMCEs: function() {
			//Ext.each(['_fld_text', '_fld_info', '_fld_info2', '_fld_info3', '_fld_info_mobile], function(i) {
			//	PC.admin[i].restart();
			//});
			Ext.each(PC.global.db_flds, function(i) {
				var fld = Ext.getCmp('db_fld_'+i);
				if (fld) if (fld.xtype == 'profis_tinymce') fld.restart();
			});
		},
		items: [
			{	border: false,
				layout: 'border',
				id: 'pc_admin_area',
				tbar: {
					style: 'border-bottom:0',
					items: [
						{xtype:'tbtext', html: '<img style="vertical-align: -3px" src="images/profis16.png" alt="" /> &nbsp;Profis CMS '+ PC.version},
						'->',
						{text: PC.i18n.clear_cache, icon: 'images/clear_cache.png', handler: Clear_cache},
						{xtype:'tbseparator'},
						{text: PC.i18n.logout, icon: 'images/door_open.png', handler: Logout},
						{xtype:'tbseparator'},
						{	ref: '../../_modz',
							text: PC.i18n.plugins, icon: 'images/Compile.png',
							menu: []
						}
					]
				},
				items: (PC_acl_manager.has_access_to_pages()?AdminArea:[{
					xtype: 'panel',
					region: 'center',
					bodyStyle: 'background:#E5EFFD;',
					html: '&nbsp;'
				}])
			}
		],
		listeners: {
			afterrender: function() {
				PC.global.ln_select.getStore().addListener('datachanged', function(){
					if (PC.admin._editor_ln_select) {
						var tbar = PC.admin._editor_ln_select.getTopToolbar();
						tbar.Reload();
					}
				});
			}
		}
	});
	//add CTRL + S shortcut to save page
	PC.global.keymap = new Ext.KeyMap(Ext.getDoc(), {});
	PC.global.keymap.addBinding({
		key: 's',
		ctrl: true,
		handler: function(k, e) {
			PC.editors.Save();
		},
		stopEvent: true
	});
	//load plugins
	PC.global.plugins_panel = Ext.getCmp('plugins_panel');
	Ext.Ajax.request({
		url: 'ajax.plugins.php',
		success: function(rspns, opts) {
			// code based on Ext.Element.update()
			var hd = document.getElementsByTagName('head')[0];
			var attr_rx = /\s(\w+)=([\'\"])(.*?)\2/ig;
			var m, am;
			
			// EXTRACT & LOAD STYLES
			var style_rx = /<style([^>]*)>([^\0]*?)<\/style>/gim;
			while (m = style_rx.exec(rspns.responseText)) {
				var ch = document.createElement('style');
				while (am = attr_rx.exec(m[1]))
					ch.setAttribute(am[1], am[3]); // need to do htmlspecialchars_decode on am[3]
				if (ch.styleSheet)
					ch.styleSheet.cssText = m[2]; // IE
				else
					ch.appendChild(document.createTextNode(m[2])); // the world
				hd.appendChild(ch);
			}
			rspns.responseText = rspns.responseText.replace(style_rx, '');
			
			// EXTRACT & LOAD SCRIPTS
			var a;
			var script_rx = /<script([^>]*)>([^\0]*?)<\/script>/gim;
			while (m = script_rx.exec(rspns.responseText)) {
				a = {};
				while (am = attr_rx.exec(m[1]))
					a[am[1]] = am[3]; // need to do htmlspecialchars_decode on am[3]
				if (a.src) {
					var ch = document.createElement('script');
					for (am in a)
						ch.setAttribute(am, a[am]);
					hd.appendChild(ch);
				} else {
					try {
						if (window.execScript)
							window.execScript(m[2]); // IE
						else {
                                                    //*
                                                    //Making js code debuggable:
                                                    var e = document.createElement('script');
                                                    e.type = 'text/javascript';
                                                    e.text = m[2];
                                                    document.body.appendChild(e);
                                                   //*/
                                                   //window.eval(m[2]); // the world
                                                }
					} catch(e) {
						debug_alert(e);
					};
				}
			}
			rspns.responseText = rspns.responseText.replace(script_rx, '');
			
			//PC.global.plugins_panel.update(rspns.responseText);
			
			/*
			//Sort PC.plugins by priority
			PC.plugins.sort(function(a, b) {
				if (a.priority == b.priority) return 0;
				return (a.priority < b.priority)?1:-1;
			});*/
			
			if (PC.global.permissions.admin) {
				PC.plugin.plugins = {
					icon: 'images/bricks.png',
					name: PC.i18n.dialog.plugins.title,
					priority: 1,
					onclick: PC.dialog.plugins.show
				};
			}
			
			Ext.iterate(PC.plugin, function(plugin, config, o) {
				if (typeof config.onclick == 'function') {
					PC.admin._modz.menu.add({
						id: 'PluginsMenuItem-'+ plugin,
						text: config.name,
						icon: config.icon,
						//scale: 'large',
						listeners: {
							click: config.onclick
						}
					});
				}
			});
		},
		failure: function(rspns, opts) {
			//PC.global.plugins_panel.update(PC.i18n.msg.error.plugins);
		}
	});
	//other
	if (!PC.global.permissions.admin) {
		var mask = Ext.get('loading-mask');
		if (mask) mask.remove();
	};
	var keep_alive_period = (PC.global['session.gc_maxlifetime'] - 90) * 1000;
	if (keep_alive_period > 0) {
		var delayed_task = new Ext.util.DelayedTask(function(){
			var periodic_task = {
				run: function(){
					Ext.Ajax.request({
						url: PC.global.BASE_URL + PC.global.ADMIN_DIR + '/api/keepalive'
					});
				},
				interval: keep_alive_period
			}
			Ext.TaskMgr.start(periodic_task);
		});
		delayed_task.delay(keep_alive_period);
	}

	PC.global.tinymce_absolut = new Ext.Container({
		autoEl: 'div',
		hidden: true,
		renderTo: Ext.getBody(),
		defaults: {
			xtype: 'container'
		},
	//  The two items below will be Ext.Containers, each encapsulated by a <DIV> element.
		items: [{
			xtype: 'profis_tinymce',
			ref: '_tinymce',
			id: 'absolut_tinymce'
		}]
	});

});

function Reload_page_controller_list(n) {
	if (n == undefined) n = PC.tree.component.getSelectionModel().selNode;
	var list = [['','-']];
	if (n.getDepth() == 1) list.push(['menu','Menu']);
	list.push(['core/inactive', PC.i18n.inactive_site_page]);
	list = list.concat(get_active_controllers());
	Ext.getCmp('db_fld_controller').getStore().loadData(list);
	/*
	
		Ext.getCmp('db_fld_controller').getStore().loadData(
			
		);
	} else {
		Ext.getCmp('db_fld_controller').getStore().loadData(
			[['','-'],,].concat(get_active_controllers())
		);
	}
	*/
}
function get_active_controllers() {
	var ctrs = [];
	Ext.iterate(PC.global.controllers, function(ctr){
		if (ctr[0] == 'custom') {
			if (ctr[3] == 1) {
			ctrs.push([ctr[1], ctr[2]]);
			}
		};
	});
	return ctrs;
}
function getLanguageStore(include_blank) {
	var langs = [];
	if (include_blank) langs.push(['', PC.i18n.default_language]);
	Ext.iterate(PC.global.admin_languages, function(k, v) {
		langs.push([k, v]);
	});
	return langs;
};
function Get_site(site_id) {
	if (!site_id) site_id = PC.global.site;
	var site = undefined;
	Ext.iterate(PC.global.SITES, function(s) {
		if (s[0] == site_id) {
			site = s;
			return false;
		}
	});
	return site;
}
function Get_default_site_language() {
	return Get_site()[3][0][0];
}
//is site preview function enabled?
function Check_preview_action_availability() {
	var site = Get_site();
	if (site[7]) {
		var pattern = new RegExp('^https?:\/\/'+ site[7].replace('%', '.+?') +'$');
		if (pattern.test(PC.global.BASE_URL)) {
			PC.tree.actions.Preview.show();
			return;
		}
	}
	PC.tree.actions.Preview.show();
	//PC.tree.actions.Preview.hide();
}
function Load_home_page_on_tree_load() {
	var tree = PC.tree.component;
	if (tree.getRootNode().childNodes.length) Load_home_page();
	else setTimeout(Load_home_page, 500);
	//nepasiteisino, nes reikia paskui kazkaip eventa trinti: tree.addListener('load', function(){ setTimeout(Load_home_page, 500); });
}
function Load_home_page() {
	var node = PC.tree.component.getRootNode().childNodes[0];
	if (node == undefined) return;
	if (node.attributes._front < 1) return;
	node.fireEvent('click', node);
}
function Preview_page(n) {
	if (n == undefined) return;
	window.open(PC.global.BASE_URL + PC.global.ADMIN_DIR +'/preview.php?id='+ n.id +'&ln='+ PC.global.tree_ln);
	//permalink: window.open(PC.global.BASE_URL + PC.global.tree_ln + '/page/' + n.id);
}
function Get_active_editor() {
	return tinymce.activeEditor;
}
function Get_active_page_id() {
	return PC.tree.component.selModel.getSelectedNode().id;
}
function is_function(func) {
	return (typeof func == 'function');
}

function on_content_load() {
	//change date validity field state depending on content config (toggle checkbox)
	Ext.getCmp('db_fld_publishing_date').setValue((Ext.getCmp('db_fld_date_from').getRawValue()!='') || (Ext.getCmp('db_fld_date_to').getRawValue()!=''));
	//load archive / data: [id, time, user, sel]
	Update_content_archive();
	//Ext.getCmp('db_fld_last_update').setValue(data.last_update);
}

// input: PC.global.pid, PC.global.ln
function clear_fields() {
	Ext.each(PC.global.db_flds, function(i) {
		var fld = Ext.getCmp('db_fld_'+i);
		fld.setValue('');
		fld.originalValue = fld.getValue();
	});
	Ext.getCmp('db_fld_time').setValue('');
	Ext.getCmp('db_fld_publishing_date').setValue(false);
	Ext.getCmp('grid_archive').getStore().loadData([]);
	//Ext.getCmp('db_fld_last_update').setValue('');
}

function Get_all_site_languages() {
	var all_langs = {};
	Ext.iterate(PC.global.site_select.getStore().getRange(), function(langs){
		Ext.iterate(langs.data.langs, function(lang){
			if (all_langs[lang[0]] == undefined) all_langs[lang[0]] = lang;
		});
	});
	var all_languages = [];
	Ext.iterate(all_langs, function(lang, data){
		all_languages.push(data);
	});
	all_langs = undefined;
	return all_languages;
}
function Load_page_data(info, data, callback) {
	if (data == undefined) return;
	//choose and load suitable editor
	return PC.editors.Load(info, data, true, callback);
}
function Load_page(data) {
	if (data == undefined && PC.global.pid == 0) return;
	Ext.MessageBox.show({
		title: PC.i18n.msg.title.loading,
		msg: PC.i18n.msg.loading,
		width: 300,
		wait: true,
		waitConfig: {interval:100}
	});
	clear_fields();
	if (data != undefined) return Load_page_data({}, data, Ext.Msg.hide);
	Ext.Ajax.request({
		url: 'ajax.page.php',
		params: {'action':'get','id':PC.global.pid},
		method: 'POST',
		callback: function(opts, success, rspns) {
			if (success && rspns.responseText) {
				try {
					var data = Ext.decode(rspns.responseText);
					if (data.ignore_gmt_offset && data.date) {
						//data.date = parseInt(data.date) + (new Date().getTimezoneOffset()*60);
					}
					//delete data.ignore_gmt_offset;
					Load_page_data({treeNode: PC.tree.component.getNodeById(PC.global.pid)}, data, Ext.Msg.hide);
				} catch(e) { 
					Ext.Msg.hide(); 
					//debugger;
				};
			}
		}
	});
	return true;
}
function Parse_archive_store(archive, numeric) {
	if (numeric == undefined) numeric = false;
	var records = [];
	Ext.each(archive, function(obj){
		if (numeric) var item = [obj[0], obj[1], obj[2], false];
		else var item = [obj.id, obj.time, obj.username, false];
		records.push(item);
	});
	return records;
}
function Create_content_store(ln) {
	PC.global.page.content[ln] = {};
	var content_store = PC.global.page.content[ln];
	Ext.each(PC.global.db_flds, function(i) {
		content_store[i] = {};
		var store = content_store[i];
		store.value = store.originalValue = '';
	});
	return content_store;
}
function Load_to_editor(ln, original) {
	var editor = PC.editors.Get();
	if (typeof editor.Load == 'function') {
		
	}	
	if (PC.global.page.content == undefined) return;
	if (ln == undefined) ln = PC.global.ln;
	if (PC.global.page.content[ln] != undefined) {
		var content_store = PC.global.page.content[ln];
	}
	if (content_store == undefined) {
		content_store = Create_content_store(ln);
	}
	Save_content_to_store(PC.global.ln);
	PC.global.page.loaded_language = ln;
	Render_page_actions();
	//fill page fields
	Ext.each(PC.global.db_flds, function(i) {
		var field = Ext.getCmp('db_fld_'+i);
		if (/^(name|custom_name|info|info2|info3|info_mobile|title|keywords|description|route|permalink|text|ln_redirect)$/.test(i)) {
			var source = content_store[i];
			if (original) field.setValue(source.originalValue);
			else field.setValue(source.value);
			//flag editors as not dirty
			if (/^(info|info2|info3_|info_mobile|text)$/.test(i)) {
				//clear undo history
				tinymce.editors['db_fld_'+i].undoManager.clear();
				tinymce.editors['db_fld_'+i].isNotDirty = 1;
			}
		} else if (/^(date_from|date_to)$/.test(i)) {
			var source = PC.global.page[i];
			//console.log(source);
			if (original) var value = source.originalValue;
			else var value = source.value;
			var time_value = null;
			if (value != null) {
				var date = new Date(value*1000);
				value = date.format('Y-m-d');
				time_value = date.format('H:i');
			}
			field.setValue(value);
			Ext.getCmp('db_fld_time'+i.substr(4)).setValue(time_value);
		} else if (i == 'date') {
			var source = PC.global.page[i];
			
			if (original) var value = source.originalValue;
			else var value = source.value;
			value = Page_datetime_convert(value, null);
			field.setValue(value.date);
			Ext.getCmp('db_fld_time').setValue(value.time);
		} else if (/^(id|controller|published|route_lock|hot|nomenu|redirect|source_id|target|reference_id)$/.test(i)) {
			var source = PC.global.page[i];
			if (original) field.setValue(source.originalValue);
			else field.setValue(source.value);
		}
		field.originalValue = field.getValue();
	});
	
	Render_redirects_from();
	//finished loading
	//content_store.selected_archive = 
	
	//focus editor depending of which tab is currently opened
	var tab_id = PC.admin._editor_ln_select.getActiveTab().getId();
	switch (tab_id) {
		case 'db_tab_info':
			var focus_field = 'db_fld_info';
			break;
		case 'db_tab_info2':
			var focus_field = 'db_fld_info2';
			break;
		case 'db_tab_info3':
			var focus_field = 'db_fld_info3';
			break;
		case 'db_tab_info_mobile':
			var focus_field = 'db_fld_info_mobile';
			break;	
		default:
			var focus_field = 'db_fld_text';
	}
	tinyMCE.execCommand('mceFocus', false, focus_field);
	
	on_content_load(); //load content archive and other...
	return true;
}
function Get_list_of_redirects_from() {
	var page = PC.global.page;
	if (page == undefined) return;
	var from = page.redirects_from;
	if (from == undefined) return;
	from = from.value;
	var html = '';
	Ext.iterate(from, function(id, names) {
		if (names[PC.global.tree_ln] == undefined) {
			Ext.iterate(names, function(ln, name) {
				if (name != undefined && name != '') {
					return false;
				}
			});
		} else name = names[PC.global.tree_ln];
		if (name == '' || name == undefined) name = 'id: '+id;
		if (html.length) html += ', ';
		html += name;
	});
	return html;
}
function Get_page_path(pid, callback) {
	//ajax uzklausa kad gauti path, ji atidaryti ir is naujo gauti node
	Ext.Ajax.request({
		url: 'ajax.page.php?action=get_path',
		params: {id: pid},
		method: 'POST',
		success: function(result){
			var json_result = Ext.util.JSON.decode(result.responseText);
			if (json_result.success) {
				callback(json_result.path);
			}
			else {} //do nothing
		},
		failure: function(){
			//do nothing
		}
	});
}
function Render_redirects_from() {
	var from = PC.global.page.redirects_from;
	var field = Ext.getCmp('db_fld_redirects_from');
	if (from != undefined) {
		from = from.value;
		var html = '';
		var name = '';
		Ext.iterate(from, function(id, names) {
			//is front page?
			var node = PC.tree.component.getNodeById(id);
			if (node && node.attributes._front) {
				name = PC.i18n.home;
			}
			else if (names[PC.global.tree_ln] == undefined) {
				Ext.iterate(names, function(ln, name) {
					if (name != undefined && name != '') {
						return false;
					}
				});
			} else name = names[PC.global.tree_ln];
			if (name == '' || name == undefined) name = 'id: '+id;
			if (html.length) html += ', ';
			html += '<a id="cms_redirect_from_'+id+'" href="#">'+name+'</a>';
		});
		field.setText(html, false);
		Ext.select('[id^=cms_redirect_from_]').on('click', function(e){
			e.preventDefault();
			var pid = this.id.substring(18);
			var tree = PC.tree.component;
			var node = tree.getNodeById(pid);
			if (node != undefined) {
				PC.tree.component.fireEvent('click', node);
				return;
			}
			Get_page_path(pid, function(path){
				PC.tree.component.expandPath(path, undefined, function(){
					node = tree.getNodeById(pid);
					PC.tree.component.fireEvent('click', node);
				});
			});
		});
		field.show();
		return;
	}
	field.hide();
	field.setText('-');
}
function Page_datetime_convert(datetime, default_return_value) {
	if (default_return_value === undefined) default_return_value = '';
	if (typeof datetime == 'object' && datetime != null) {
		if (!/^[0-9]{2}:[0-9]{2}$/.test(datetime.time)) datetime.time = '00:00';
		var date = new Date(datetime.date);
		if (isNaN(date.getTime())) return default_return_value;
		var time = datetime.time.split(':');
		date.setHours(time[0]);
		date.setMinutes(time[1]);
		var timestamp = Math.round(date.getTime()/1000);
		if (PC.global.ignore_time_zone) {
			timestamp = parseInt(timestamp) - date.getTimezoneOffset() * 60;
		}
		return timestamp;
	}
	else {
		var date_value = null;
		var time_value = null;
		if (datetime>0) {
			var date = new Date(datetime*1000);
			if (PC.global.ignore_time_zone) {
				datetime = parseInt(datetime) + date.getTimezoneOffset()*60;
				date = new Date(datetime*1000);
			}
			var date_value = date.format('Y-m-d');
			var time_value = date.format('H:i');
		}
		return {date: date_value, time: time_value};
	}
}
function Page_dirty() {
	Save_content_to_store();
	var dirty = false;
	var date_valid = {};
	Ext.iterate(PC.global.page, function(field, value){
		if (field == 'loaded_language') return;
		else if (field == 'content') {
			Ext.iterate(value, function(language, store){
				if (store.isDirty) {
					dirty = true;
					return false;
				}
			});
		} else {
			if (/^(date_from|date_to)$/.test(field)) {
				if (Ext.getCmp('db_fld_publishing_date').getValue()) {
					if (value.originalValue == null) {
						//alert('Dirty at '+ field);
						dirty = true; return false;
					}
					else {
						if (value.value != '' && value.value != null)
						if (value.value != value.originalValue) {
							//alert('Dirty at '+ field);
							//alert(value.value);
							//alert(value.originalValue);
							dirty = true; return false;
						}
					}
				}
				else {
					if (value.originalValue != null) {
						//alert('Dirty at '+ field);
						dirty = true; return false;
					}
				}
			} else if (value.value != value.originalValue) { /*alert('Dirty at '+ field + ' buvo - '+value.originalValue+' o dabar yra - '+value.value);*/ dirty = true; return false; }
		}
	});
	return dirty;
}
function Content_dirty() {
	if (PC.global.page.id == undefined) return;
	ln = PC.global.ln;
	var content_store = PC.global.page.content[ln];
	//console.log(content_store);
	//console.log('Is content dirty..............?');
	var dirty = false;
	Ext.each(PC.global.db_flds, function(i) {
		//console.log('Checking: '+ i);
		if (/^(info|info2|info3|info_mobile|text)$/.test(i)) {
			//if (tinymce.editors[].isDirty()) {
			if (Ext.getCmp('db_fld_'+i).isDirty()) {
				dirty = true;
				return false;
			}
			else return;
		}
		var field = Ext.getCmp('db_fld_'+i);
		if (/^(name|custom_name|title|keywords|description|route|permalink|ln_redirect)$/.test(i)) {
			var store = content_store[i];
			
		} else if (/^(controller|published|route_lock|hot|nomenu|date_from|date_to|redirect|source_id|target|date|reference_id)$/.test(i)) {
			var store = PC.global.page[i];
		}
		if (/^(date_from|date_to)$/.test(i)) {
			if (field.getValue() == '') var fieldValue = null;
			else {
				var time = document.getElementById('db_fld_time'+i.substr(4)).value;
				//var time = Ext.getCmp('db_fld_time'+i.substr(4)).getValue();
				if (!time.length) time = '00:00';
				else if (time.length < 5) time = '0'+ time;
				var date = new Date(field.getValue().format('Y/m/d') +' '+ time);
				var fieldValue = Math.round(date.getTime()/1000);
			}
		}
		/*else if (/^(time_from|time_to)$/.test(i)) {
			if (field.getValue() == '') var fieldValue = null;
			else var fieldValue = field.getValue().format('H:i');
		}*/
		else if (i == 'date') {
			var date_value = field.getValue();
			var time_value = Ext.getCmp('db_fld_time').getValue();
			var fieldValue = Page_datetime_convert({
				date: date_value,
				time: time_value
			}, null);
		}
		else if(/^(route_lock|published|hot|nomenu)$/.test(i)) {
			var fieldValue = (field.getValue()?'1':'0');
		}
		else {
			var fieldValue = field.getValue();
		}
		//console.log('fieldValue: '+ fieldValue);
		//console.log('get field value: '+ field.getValue());
		//console.log('store.value: '+ store.value);
		if (!store) {
			//debugger;
		}
		if (fieldValue != store.value) {
			dirty = true;
			return false;
		}
	});
	//console.log('So whats the answer? '+ dirty);
	return dirty;
}
function Save_content_to_store(ln_change_to) {
	if (PC.global.page.loaded_language == undefined) return;
	if (!Content_dirty()) return;
	//if (ln_change_to == PC.global.page.loaded_language) return;
	ln = PC.global.page.loaded_language;
	var content_store = PC.global.page.content[ln];
	content_store.isDirty = true;
	Ext.each(PC.global.db_flds, function(i) {
		if (i == 'publishing_date') return;
		var field = Ext.getCmp('db_fld_'+i);
		if (/^(name|custom_name|text|info|info2|info3|info_mobile|title|keywords|description|route|permalink|ln_redirect)$/.test(i)) {
			var store = content_store[i];
			
		} else if (/^(controller|published|route_lock|hot|nomenu|date_from|date_to|redirect|source_id|target|date|reference_id)$/.test(i)) {
			var store = PC.global.page[i];
		}
		//use solid date format
		if (/^(date_from|date_to)$/.test(i)) {
			if (Ext.getCmp('db_fld_publishing_date').getValue()) {
				if (field.getValue() != '' && field.getValue() != null) {
					var time = document.getElementById('db_fld_time'+i.substr(4)).value;
					//var time = Ext.getCmp('db_fld_time'+i.substr(4)).getValue();
					if (!time.length) {
						time = '00:00';
					}
					else if (time.length < 5) time = '0'+ time;
					var date = new Date(field.getValue().format('Y/m/d') +' '+ time);
					store.value = Math.round(date.getTime()/1000);
				}
				else store.value = null;
			} else store.value = null;
		//use solid boolean values so content is not recognized as dirty
		} else if (i == 'date') {
			var date_value = field.getValue();
			var time_value = Ext.getCmp('db_fld_time').getValue();
			var value = Page_datetime_convert({
				date: date_value,
				time: time_value
			}, null);
			store.value = value;
		} else if (/^(route_lock|published|hot|nomenu)$/.test(i)) {
			store.value = (field.getValue()?'1':'0');
		//get raw editor values
		} else if (/^(text|info|info2|info3|info_mobile)$/.test(i)) {
			store.value = field.getRawValue();
		//just get the value
		} else store.value = field.getValue();
	});
	return true;
}
function Equalize_content_values() {
	//force tree to react to the changes
	var tree = PC.tree.component;
	var tree_node = tree.getNodeById(PC.global.pid);
	if (tree_node != undefined) {
		var red = PC.global.page.redirect; 
		//is this node redirected to other node?
		tree_node.attributes._redir = (red.value==''?false:true);
		//new redirect added
		if (red.originalValue == null) red.originalValue = '';
		if (!red.originalValue.length && red.originalValue != 0) {
			if (red.value.length) {
				var red_node = tree.getNodeById(red.value);
				if (red_node != undefined) {
					red_node.attributes.redirects_from++;
				}
			}
		}
		//redirect deleted
		else {
			if (!red.value.length) {
				var red_node = tree.getNodeById(red.originalValue);
				if (red_node != undefined) {
					red_node.attributes.redirects_from--;;
				}
			}
		}
	}
	//equalize all originalValues with the values
	Ext.iterate(PC.global.page, function(field, value){
		//update redirect data in admin
		if (field == 'redirect') {
			if (value.value != value.originalValue) {
				if (value.originalValue > 0) {
					//find this page and tell him that one page doesnt redirect to him anymore
					var id = value.originalValue;
					var node = PC.tree.component.getNodeById(id);
					if (node != undefined) {
						node.attributes.redirects_from--;
					}
				}
				if (value.value > 0) {
					//find this page and tell him that one page is willing to be a shortcut to him
					var id = value.value;
					var node = PC.tree.component.getNodeById(id);
					if (node != undefined) {
						node.attributes.redirects_from++;
					}
				}
			}
		}
		//loop through pages of all languages
		if (field == 'content') {
			Ext.iterate(value, function(language, store){
				Ext.iterate(store, function(field, value){
					if (field == 'isDirty') {
						store[field] = undefined;
						return;
					}
					else if (field == 'last_update') {
						var last_update = new Date();
						value.value = last_update.format('Y-m-d H:i:s').toString();
					}
					else if (field == 'update_by') {
						value.value == PC.global.user;
					}
					value.originalValue = value.value;
				});
			});
		} else value.originalValue = value.value;
	});
	tree.localizeAllNodes();
	//update fields in editor
	Ext.each(PC.global.db_flds, function(i) {
		var field = Ext.getCmp('db_fld_'+i);
		field.originalValue = field.getValue();
	});
	return true;
}
/*function Get_tree_node(id, callback) {
	var node = PC.tree.component.getNodeById(id);
	if (node != undefined) {
		return node;
	}
	Ext.Ajax.request({
		url: 'ajax.page.php?action=get_path',
		params: {id: id},
		method: 'POST',
		success: function(result){
			var json_result = Ext.util.JSON.decode(result.responseText);
			if (json_result.success) callback(json_result);
			else callback(false);
		},
		failure: function(){
			callback(false);
		}
	});
}*/
function Flag_editors_as_clean() {
	Ext.each(PC.global.db_flds, function(i) {
		if (/^(info|info2|info3|info_mobile|text)$/.test(i)) {
			tinymce.editors['db_fld_'+i].isNotDirty = 1;
		}
	});
}

function Clear_cache() {
	Ext.Ajax.request({
		url: PC.global.BASE_URL + 'admin/api/clear_cache'
	});
}

function Logout() {
	Ext.MessageBox.show({
		title: PC.i18n.msg.title.confirm,
		msg: PC.i18n.msg.logout,
		buttons: Ext.MessageBox.YESNO,
		icon: Ext.MessageBox.WARNING,
		fn: function(clicked) {
			if (clicked == 'yes') {
				document.write('<form style="display:none" method="post" id="logout"><input type="hidden" name="logout" value="1" /><input type="submit" name="logout" /></form>');
				document.getElementById('logout').submit();
			}
		}
	});
}
function Show_redirect_page_window(return_callback, page_selector_params) {
	var get_route = false;
	var select_node_path = false;
	var init_value = false;
	var enable_ok_button = false;
	var additionalBaseParams = false;
	var callback_ok = false;
	var disable_ln_combo = false;
	var site = false;
	var ln = false;
	var fields = false;
	if (page_selector_params) {
		get_route = page_selector_params.get_route;
		select_node_path = page_selector_params.select_node_path;
		init_value = page_selector_params.init_value;
		enable_ok_button = page_selector_params.enable_ok_button;
		callback_ok = page_selector_params.callback_ok;
		disable_ln_combo = page_selector_params.disable_ln_combo;
		site = page_selector_params.site;
		ln = page_selector_params.ln;
		fields = page_selector_params.fields;
		if (page_selector_params.tree_params) {
			additionalBaseParams = page_selector_params.tree_params.additionalBaseParams;
		}
	}
	
	var ok_disabled = true;
	if (enable_ok_button) {
			var ok_disabled = false;
	}
	
	if (!callback_ok) {
		callback_ok = function(w) {
			var n = w._tree.getSelectionModel().getSelectedNode();
			if (n) {
				/*if (n.id == PC.global.page.id.originalValue && w._ln_sel.getValue() == PC.global.ln) {
					alert('You cannot redirect this page to itself');
				}
				else {*/
					var load_anchors = true;


					var lang = w._ln_sel.getValue();

					var set_url_callback = function(url, node_id) {
						if (typeof return_callback == 'function') {
							return_callback(url, lang, node_id);
						}
						w.close();
					}

					var url = false;
					
					
					if (page_selector_params && page_selector_params.return_type == 'name_path') {
						url = n.attributes._names[lang];
						if (url == '') {
							Ext.iterate(n.attributes._names, function(ln_key, ln_name) {
								if (ln_name != '') {
									url = ln_name;
									return false;
								}
							})
						}
						var n_parent = n.parentNode;
						var ln_name = '';
						while(n_parent && n_parent.attributes && n_parent.attributes._names) {
							ln_name = n_parent.attributes._names[lang];
							if (ln_name != '') {
								url = ln_name + ' / ' + url;
							}
							n_parent = n_parent.parentNode;
						}	
					}
					
					if (get_route) {
						
						if (url === false) {
							url = 'pc_page:' + n.id;
							if (lang != '') {
								url += ':' + lang;
							}
							var pseudo_url = '';
							if (n.attributes._routes) {
								if (n.attributes._routes[lang]) {
									pseudo_url = n.attributes._routes[lang] + '/';
								}
							}
							if (pseudo_url == '') {
								if (n.attributes._names) {
									if (n.attributes._names[lang]) {
										pseudo_url = n.attributes._names[lang] + '/';
										pseudo_url = pseudo_url.replace(/\]/g, "");
										pseudo_url = pseudo_url.replace(/\[/g, "");
									}
								}
							}
							if (pseudo_url != '') {
								if (lang != '') {
									pseudo_url = lang + '/' + pseudo_url;
								}
								pseudo_url = '[' + pseudo_url + '] ';
								url = pseudo_url + url;
							}
						}
						
						/*

						if (n.attributes._routes) {
							if (n.attributes._routes[lang]) {
								url = 'pc_page:' + n.id;
								//, lang+'/'+n.attributes._routes[lang]+'/'
								if (lang != '') {
									url += ':' + lang
								}
							}
						}
						var n_id_parts = n.id.split('/');
						var plugin_name = n_id_parts[0];
						var hook_name = 'core/page/generate_url/' + plugin_name;

						if (url === false && PC.hooks.Count(hook_name) > 0) {
							var params = {};
							params.callback = set_url_callback;
							params.full_id = n.id;
							params.id = n_id_parts[n_id_parts.length - 1];
							params.ln = lang;
							PC.hooks.Init(hook_name, params);
							return;
						}
						*/

					}

					if (url === false) {
						url = n.id;
					}

					set_url_callback(url, n.id);

					/*
					if (typeof return_callback == 'function') {
						return_callback((get_route?w._ln_sel.getValue()+'/'+n.attributes._routes[w._ln_sel.getValue()]+'/':n.id), w._ln_sel.getValue());
					}
					w.close();
					*/
				//}
				//self.setValue('?' + w._ln_sel.getValue() + '=' + n.id);
			}
		}
	}
	
	var tree_config = {	xtype: 'profis_pagetree',
		additionalBaseParams: additionalBaseParams,
		enableDD: false,
		ref: '_tree',
		selModel: new Ext.tree.DefaultSelectionModel({
			listeners: {
				selectionchange: function(sm, n) {
					w.ok_btn.enable();
				}
			}
		}),
		listeners: {
			load: function(node){
				if (node.id != '0') return;
				if (select_node_path == undefined) return;
				if (!select_node_path.length) {
					node.ownerTree.selectPath(PC.tree.component.getNodeById(PC.global.page.id.originalValue).getPath());
					return;
				}
				if (select_node_path.substring(0, 1) == '/') {
					node.ownerTree.selectPath(select_node_path);
				}
				else {
					var path_node = PC.tree.component.getNodeById(select_node_path);
					if (path_node != undefined) {
						var path = path_node.getPath();
						node.ownerTree.selectPath(path);
					}
					else {
						Get_page_path(select_node_path, function(path){
							node.ownerTree.selectPath(path);
						});
					}
				}
			},
			beforeappend: function(tr, prnt, n) {
				if (n.id < 0 || n.id == 'create' || n.attributes.controller == 'search' || n.attributes._front > 0) {
					//function_params.allow_front
					if (n.attributes._front > 0) {

					}
					else {
						return false;
					}
				}
			},
			beforeclick: function(n, e) {
				if (n.attributes.controller == 'menu') return false;
			}
		},
		_sid: (init_value!=undefined?init_value:undefined)
	};
	
	if (page_selector_params && page_selector_params.tree_config) {
		Ext.apply(tree_config, page_selector_params.tree_config);
	}
	
	var window_config = {
		items: [
			{
				layout: 'form',
				width: 250,//1000
				baseCls: 'x-toolbar',
				labelWidth: 140,
				labelAlign: 'right',
				style: {
					padding: '4px 4px 4px 0'
				},
				items: [
					/*{
						xtype: 'profis_sitecombo',
						ref: '../_site_sel',
						listeners: {
							beforeselect: function(self, rec, ndx) {
								if (self.getValue() == rec.get('id')) return;
								w._tree.setSite(rec.get('id'));
								w.ok_btn.disable();
								
								var _ln = w._ln_sel.getValue();
								w._ln_sel.getStore().loadData(rec.get('langs'));
								if (!w._ln_sel.getStore().getById(_ln)) {
									_ln = '';
									var r = w._ln_sel.getStore().getAt(0);
									if (r) {
										_ln = r.get('ln_id');
										w._tree.setLn(_ln);
									}
								}
								w._ln_sel.setValue(_ln);
								clear_fields();
							}
						}
					},*/
					{	xtype: 'profis_lncombo',
						ref: '../_ln_sel',
						site: site,
						ln: ln,
						disabled: disable_ln_combo,
						listeners: {
							select: function(self, rec, ndx) {
								//w._tree.setLn(rec.get('ln_id'));
								self.ownerCt.ownerCt._tree.setLn(rec.get('ln_id'));
							}
						}
					}
				]
			},
			tree_config
		],
		width: 300,
		height: 350,
		layout: 'vbox',
		layoutConfig: {
			align: 'stretch',
			pack: 'start'
		}
	};

	if (fields) {
		window_config.items.push({
			xtype: 'form',
			ref: '_form',
			items: fields,
			style: {
				padding: '4px 4px 4px 0'
			}
		});
	}

	if (page_selector_params.return_only_window_config) {
		return window_config
	}
	
	var title = PC.i18n.sel_redir_dst;
	if (page_selector_params.title) {
		title = page_selector_params.title;
	}
	
	Ext.apply(window_config, {
		modal: true,
		title: title,
		buttons: [
			{	ref: '../ok_btn',
				text: Ext.Msg.buttonText.ok,
				disabled: ok_disabled,
				handler: function() {
					callback_ok(w)
				}
			},
			{	text: Ext.Msg.buttonText.cancel,
				handler: function() {
					w.close();
				}
			}
		]
	});
	
	var w = new Ext.Window(window_config);
	if (page_selector_params.window_config) {
		Ext.apply(w, page_selector_params.window_config);
	}
	w.show();
	w._tree.root.expand();
	return w;
}
function Reload_content_archive() {
	if (PC.global.pid < 1) return;
	var pid = PC.global.pid;
	var language = PC.global.ln;
	Ext.Ajax.request({
		url: 'ajax.page.php?action=get_archive',
		params: {
			pid: pid,
			language: language
		},
		method: 'POST',
		success: function(result){
			var json_result = Ext.util.JSON.decode(result.responseText);
			if (json_result.success) {
				var content_store = PC.global.page.content[language];
				content_store.archive = Parse_archive_store(json_result.archive);
				Update_content_archive();
			} else {
				Ext.Msg.alert('Error', 'Reload_content_archive');
			}
		},
		failure: function(){
			Ext.Msg.alert('Error', 'Reload_content_archive');
		}
	});
}
function Update_content_archive() {
	var language = PC.global.ln;
	var content_store = PC.global.page.content[language];
	//current version date
	var last_update = new Date();
	last_update = last_update.format('Y-m-d H:i:s');
	last_update = last_update.toString();
	//var archive = content_store.archive.concat([[0, /*content_store.last_update.value*/last_update, content_store.update_by.value, true]]);
	var archive = content_store.archive;
	if (archive == undefined) archive = [];
	//archive.push(['0', /*content_store.last_update.value*/last_update, PC.global.user, true]);
	if (content_store.last_update != undefined)
		archive.push(['0', content_store.last_update.value, content_store.update_by.value, true]);
	//console.log(archive);
	Ext.getCmp('grid_archive').getStore().loadData(archive);
}
function Render_page_actions(controller) {
	if (controller == undefined) var controller = PC.global.page.controller.value;
	var front = 0;
	if (PC.global.page.front != undefined) front = PC.global.page.front.value;
	var could_be_enabled_or_disabled = 'controller|name_container|custom_name_container|title_container|keywords_container|description_container|route_container|permalink_container|redirect_ln_container|redirect|redirect_container|source_id|source_id_container|target|target_container|published|route_lock_container|hot|nomenu|date_container|name_title|custom_name_title|route_title|permalink_title|redirect_ln_title|title_title|description_title|keywords_title|date|time|reference_id';
	if (front > 0) {
		var disabled = 'route_container|permalink_container|route_title|permalink_title|published|route_lock_container|hot|nomenu|date_container|date|time|reference_id';
	}
	else if (controller == 'menu') {
		//console.log(controller);
		var disabled = 'title_container|title_title|keywords_container|keywords_title|description_container|description_title|route_container|permalink_container|redirect_ln_container|route_title|permalink_title|redirect_ln_title|redirect_container|source_id_container|target_container|published|route_lock_container|hot|nomenu|date_container|date|time';
	}
	else if (controller == 'search') {
		var disabled = 'controller|name_container|custom_name_container|redirect|redirect_container|source_id|source_id_container|target|target_container|route_lock_container|hot|nomenu|date_container|name_title|date|time|reference_id';
	}
	/*else if (Controller_has_fields(controller)) {
		
	}*/
	
	if (disabled != undefined) var pattern = new RegExp('^('+disabled+')$');
	//Ext.each(PC.global.db_flds, function(i) {
	Ext.each(could_be_enabled_or_disabled.split('|'), function(i) {
		var fld = Ext.getCmp('db_fld_'+i);
		if (fld == undefined) return;
		if (disabled != undefined) if (pattern.test(i)) {
			//console.log(disabled);
			//console.log('hide: '+ i);
			fld.hide();
			return;
		}
		if (new RegExp('('+could_be_enabled_or_disabled+')').test(i)) {
			//console.log('show: '+ i);
			fld.show();
		}
	});
}

function save_prompt(callback) {
	if (PC.global.pid > 0) {
		if (Page_dirty()) {
			Ext.MessageBox.show({
				title: PC.i18n.msg.title.save,
				msg: PC.i18n.msg.save,
				buttons: Ext.MessageBox.YESNOCANCEL,
				fn: function(rslt) {
					switch (rslt) {
					case 'yes':
						PC.editors.Save(callback);
						break;
					case 'no':
						if (is_function(callback)) callback();
						break;
					default: // case 'cancel':
					}
				},
				icon: Ext.MessageBox.QUESTION
			});
			return false;
		}
	}
	if (is_function(callback)) callback();
	return true;
}

window.onbeforeunload = function() {
	var do_not_check = PC.global.do_not_check_dirty_content;
	PC.global.do_not_check_dirty_content = false;
	if (PC_acl_manager.has_access_to_pages()) if (!do_not_check && Content_dirty()) {
		return PC.i18n.msg.title.save;
	}
}

function node_rename_menu(node, create_mode) {
	var node_el = Ext.get(node.ui.getEl());
	create_mode = (create_mode?true:false);
	PC.dialog.multilnedit.show({
		pageX: node_el.getX()+200,
		pageY: node_el.getY()+20,
		node: node,
		create_mode: create_mode,
		values: node.attributes._names,
		title: (create_mode?PC.i18n.menu.addNew:PC.i18n.menu.rename),
		Save: function(vals, w, d) {
			if (vals) {
				var data = vals.other;
				data.id = node.id;
				data.content = {};
				Ext.iterate(vals.names, function(ln, name){
					data.content[ln] = {name: name};
				});
				Ext.Ajax.request({
					url: 'ajax.page.php?action=update',
					_node: node,
					_window: w,
					params: {
						data: Ext.encode(data),
						return_page: true,
						rename_only: true
					},
					method: 'POST',
					callback: function(opts, success, rspns) {
						if (success && rspns.responseText) {
							try {
								var data = Ext.decode(rspns.responseText);
								if (data.success) {
									if (typeof data.names == 'object') Ext.iterate(data.names, function(ln, name){
										opts._node.attributes._names[ln] = name;
									});
									PC.tree.component.localizeNode(opts._node);
									var currentPage = (data.id == PC.global.pid);
									if (currentPage) {
										PC.editors.Load({treeNode: opts._node}, data, true);
									}
									PC.hooks.Init('page.save', {
										tree: PC.tree.component,
										params: vals,
										data: data,
										create_mode: create_mode
									});
									return; // OK
								}
							} catch(e) {};
						}
						Ext.MessageBox.show({
							title: PC.i18n.error,
							msg: PC.i18n.msg.error.page.rename,
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				});
				return true;
			}
		}
	});
}


function reload_admin() {
	window.location.href = PC.global.BASE_URL + PC.global.ADMIN_DIR;
}

function debug_alert(o) {
	var a='', s;
	for (var x in o) {
		s = ''.concat(x, ': ', o[x]);
		if (s.length > 64)
			s = s.substr(0, 64)+'...';
		a = a + s + "\n";
	}
	alert(a);
}


var Base64 = {
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
		input = Base64._utf8_encode(input);
		while (i < input.length) {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
		}
		return output;
	},
	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		while (i < input.length) {
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
		}
		output = Base64._utf8_decode(output);
		return output;
	},
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while ( i < utftext.length ) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}