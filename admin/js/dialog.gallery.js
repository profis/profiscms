Ext.ns('PC.dialog');
PC.dialog.gallery = {
	// Initial config
	show: function(params) {
		this.ln = PC.i18n.dialog.gallery;
		var dialog = this;
		// if gallery window is already created, just show it and return
		dialog.params = (typeof params == 'object'?params:{});
		if (this.window) {
			if (typeof dialog.params.save_fn == 'function' && !dialog.params.show_insert) {
				PC.ux.gallery.files.actions.Get('Insert', this.files_view).hide();
			}
			else {
				//dialog.show_window(PC.ux.gallery.files.actions.Get('Insert', this.files_view));
				PC.ux.gallery.files.actions.Get('Insert', this.files_view).show();
			}
			PC.dialog.gallery.files_view.refresh();
			this.window.show();
			dialog.Select_initial_file();
			return;
		}
		// parse config found in the cookies
		if (PC.utils.getCookie('admin_files_current_template')) {
			PC.ux.gallery.FilesCurrentTemplate = PC.utils.getCookie('admin_files_current_template');
		}
		if (PC.utils.getCookie('admin_mark_unused_files')) {
			PC.ux.gallery.MarkUnusedFiles = true;
		}
		if (PC.utils.getCookie('admin_close_after_insert')) {
			PC.ux.gallery.CloseAfterInsert = true;
		}
		if (PC.utils.getCookie('admin_close_after_click_outside_gallery')) {
			PC.ux.gallery.CloseAfterClickOutside = true;
		}
		/*if (PC.utils.getCookie('admin_selected_category')) {
			PC.ux.gallery.SelectedCategory = PC.utils.getCookie('admin_selected_category');
		}*/
		// create gallery and all its' components...
		// context menu items for the categories tree
		Ext.ns('PC.dialog.gallery.categories_context_items');
		this.categories_context_items.open = {
			text: PC.i18n.dialog.gallery.action.open, iconCls: 'gallery_open',
			handler: function(){
				PC.dialog.gallery.categories_context.contextNode.select();
				PC.dialog.gallery.categories_context.contextNode.expand();
				PC.dialog.gallery.select_category(PC.dialog.gallery.categories_context.contextNode.attributes.id);
			}
		};
		this.categories_context_items.new_category = {
			text: PC.i18n.dialog.gallery.action.new_category, iconCls: 'gallery_upload',
			handler: function(){
				var New_category_node = PC.dialog.gallery.categories_context.contextNode.parentNode.appendChild(new Ext.tree.TreeNode({
					iconCls: 'gallery_folder', size: '0.0', disabled: true
				}));
				setTimeout(function(){
					PC.dialog.gallery.categories.getSelectionModel().select(New_category_node);
					setTimeout(function(){
						PC.dialog.gallery.categories_editor.editNode = New_category_node;
						PC.dialog.gallery.categories_editor.startEdit(New_category_node.ui.textNode);
					}, 250);
				}, 100);
				/* Prevent from creating siblings for root/bin nodes
				if (!isNaN(parseInt(PC.dialog.gallery.categories_context.contextNode.id))) {}
				else Ext.Msg.alert('Atsiprašome...','Jūs galite kurti katalogus <u>tik galerijos viduje</u>');*/
			}
		};
		this.categories_context_items.new_category_inside = {
			text: PC.i18n.dialog.gallery.action.new_category_inside, iconCls: 'gallery_upload',
			handler: function(){
				if (PC.dialog.gallery.categories_context.contextNode.attributes.id == 'bin') return;
				PC.dialog.gallery.categories_context.contextNode.expand();
				var New_category_node = PC.dialog.gallery.categories_context.contextNode.appendChild(new Ext.tree.TreeNode({
					iconCls: 'gallery_folder', size: '0.0', disabled: true
				}));
				setTimeout(function(){
					PC.dialog.gallery.categories.getSelectionModel().select(New_category_node);
					setTimeout(function(){
						PC.dialog.gallery.categories_editor.editNode = New_category_node;
						PC.dialog.gallery.categories_editor.startEdit(New_category_node.ui.textNode);
					}, 250);
				}, 100);
			}
		};
		this.categories_context_items.cut = {
			text: PC.i18n.dialog.gallery.action.cut, iconCls: 'gallery_cut',
			handler: function(){
				PC.dialog.gallery.categories_context.contextNode.disable();
				//PC.dialog.gallery.cutted_category_node = PC.dialog.gallery.categories_context.contextNode;
			}
		};
		this.categories_context_items.copy = {text: PC.i18n.dialog.gallery.action.copy};
		this.categories_context_items.paste = {
			text: PC.i18n.dialog.gallery.action.paste,
			disabled: true
		};
		this.categories_context_items.rename = {
			text: PC.i18n.dialog.gallery.action.rename, iconCls: 'gallery_edit',
			handler: function() {
				PC.dialog.gallery.categories_editor.editNode = PC.dialog.gallery.categories_context.contextNode;
				PC.dialog.gallery.categories_editor.startEdit(PC.dialog.gallery.categories_context.contextNode.ui.textNode);
			}
		};
		this.categories_context_items.clear_thumb_cache = {
			text: PC.i18n.dialog.gallery.action.clear_thumb_cache, iconCls: 'gallery_clear_cache',
			handler: function() {
				Ext.Msg.show({
					title: PC.i18n.dialog.gallery.clear_thumb_cache.category.confirmation.title,
					msg: PC.i18n.dialog.gallery.clear_thumb_cache.category.confirmation.message,
					buttons: {
						ok: PC.i18n.dialog.gallery.button.ok,
						cancel: PC.i18n.dialog.gallery.button.cancel
					},
					fn: function(bid) {
						if (bid == 'ok') {
							Ext.Ajax.request({
								url: 'ajax.gallery.php?action=clear_thumb_cache',
								method: 'POST',
								params: {
									category_id: PC.dialog.gallery.categories_context.contextNode.attributes.id
								},
								success: function(result){
									var json_result = Ext.util.JSON.decode(result.responseText);
									if (json_result.success) {
									
									}
									else {
										PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.clear_thumb_cache.category.error_title, json_result.errors);
									}
								},
								failure: function(){
									PC.dialog.gallery.show_connection_error();
								}
							});
						}
					}
				});
			}
		};
		this.categories_context_items._delete = {
			text: PC.i18n.dialog.gallery.action._delete, iconCls: 'gallery_delete',
			handler: function() {
				Ext.Msg.show({
					title: PC.i18n.dialog.gallery.trash.category.confirmation.title,
					msg: PC.i18n.dialog.gallery.trash.category.confirmation.message,
					buttons: {
						ok: PC.i18n.dialog.gallery.button.ok,
						cancel: PC.i18n.dialog.gallery.button.cancel
					},
					fn: function(bid) {
						if (bid == 'ok') {
							Ext.Ajax.request({
								url: 'ajax.gallery.php?action=trash_category',
								method: 'POST',
								params: {
									category_id: PC.dialog.gallery.categories_context.contextNode.attributes.id
								},
								success: function(result){
									var json_result = Ext.util.JSON.decode(result.responseText);
									if (json_result.success) {
										if (PC.ux.gallery.SelectedCategory == PC.dialog.gallery.categories_context.contextNode.attributes.id) {
											PC.dialog.gallery.select_category(0);
										}
										var node = PC.dialog.gallery.categories_context.contextNode;
										var trash = PC.dialog.gallery.categories.getNodeById('bin');
										if (trash) {
											trash.reload();
											//node.attributes.trashed = 1;
											//trash.insertBefore(node, trash.firstChild);
										}
										node.remove();
									}
									else {
										PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.trash.category.error_title, json_result.errors);
									}
								},
								failure: function(){
									PC.dialog.gallery.show_connection_error();
								}
							});
						}
					}
				});
			}
		};
		
		// default context menu for categories tree gallery nodes
		this.categories_context = new Ext.menu.Menu({
			items: [
				PC.dialog.gallery.categories_context_items.open,
				{xtype: 'menuseparator'},
				PC.dialog.gallery.categories_context_items.new_category,
				PC.dialog.gallery.categories_context_items.new_category_inside,
				{xtype: 'menuseparator'},
				/*PC.dialog.gallery.categories_context_items.cut,
				PC.dialog.gallery.categories_context_items.copy,
				PC.dialog.gallery.categories_context_items.paste,
				{xtype: 'menuseparator'},*/
				PC.dialog.gallery.categories_context_items.rename,
				PC.dialog.gallery.categories_context_items.clear_thumb_cache,
				PC.dialog.gallery.categories_context_items._delete
			]
		});
		
		//
		this.categories_context_for_trashed_nodes = new Ext.menu.Menu({
			items: [
				PC.dialog.gallery.categories_context_items.open,
				{xtype: 'menuseparator'},
				{	text: PC.i18n.dialog.gallery.trash.title.restore,
					handler: function() {
						var node = PC.dialog.gallery.categories_context.contextNode;
						Ext.Ajax.request({
							url: 'ajax.gallery.php?action=restore_category',
							method: 'POST',
							params: {
								category_id: node.attributes.id
							},
							success: function(result){
								var json_result = Ext.util.JSON.decode(result.responseText);
								if (json_result.success) {
									node.attributes.trashed = 0;
									//PC.dialog.gallery.categories.getRootNode().reload();
									PC.dialog.gallery.categories.getLoader().load(PC.dialog.gallery.categories.getRootNode(), function(){
										PC.dialog.gallery.select_category(PC.ux.gallery.SelectedCategory);
										var node = PC.dialog.gallery.categories.getNodeById(PC.ux.gallery.SelectedCategory);
										if (node) node.ensureVisible();
									});
								}
								else {
									PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.trash.restore.category.error_title, json_result.errors);
								}
							},
							failure: function(){
								PC.dialog.gallery.show_connection_error();
							}
						});
					}
				},
				{
					text: PC.i18n.dialog.gallery.action._delete, iconCls: 'gallery_delete',
					handler: function() {
						Ext.Msg.show({
							title: PC.i18n.dialog.gallery.category._delete.confirmation.title,
							msg: PC.i18n.dialog.gallery.category._delete.confirmation.message,
							buttons: {
								ok: PC.i18n.dialog.gallery.button.ok,
								cancel: PC.i18n.dialog.gallery.button.cancel
							},
							fn: function(bid) {
								if (bid == 'ok') {
									Ext.Ajax.request({
										url: 'ajax.gallery.php?action=delete_category',
										method: 'POST',
										params: {
											category_id: PC.dialog.gallery.categories_context.contextNode.attributes.id
										},
										success: function(result){
											var json_result = Ext.util.JSON.decode(result.responseText);
											if (json_result.success) {
												if (PC.ux.gallery.SelectedCategory == PC.dialog.gallery.categories_context.contextNode.attributes.id) {
													PC.dialog.gallery.select_category(0);
												}
												var node = PC.dialog.gallery.categories_context.contextNode;
												var trash = PC.dialog.gallery.categories.getNodeById('bin');
												if (trash) {
													trash.reload();
													//node.attributes.trashed = 1;
													//trash.insertBefore(node, trash.firstChild);
												}
												node.remove();
											}
											else {
												PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.category._delete.error_title, json_result.errors);
											}
										},
										failure: function(){
											PC.dialog.gallery.show_connection_error();
										}
									});
								}
							}
						});
					}
				}
			]
		});
		
		// categories context menu for 'bin' node
		this.categories_context_for_bin = new Ext.menu.Menu({
			items: [
				PC.dialog.gallery.categories_context_items.open,
				{xtype: 'menuseparator'},
				{	text: PC.i18n.dialog.gallery.trash.title.empty,
					handler: function() {
						var g = PC.dialog.gallery;
						Ext.Msg.show({
							title: PC.i18n.dialog.gallery.trash.empty.confirmation.title,
							msg: PC.i18n.dialog.gallery.trash.empty.confirmation.message,
							buttons: {
								ok: PC.i18n.dialog.gallery.button.ok,
								cancel: PC.i18n.dialog.gallery.button.cancel
							},
							fn: function(bid) {
								if (bid == 'ok') {
									Ext.Ajax.request({
										url: 'ajax.gallery.php?action=empty_trash',
										method: 'POST',
										success: function(result){
											var json_result = Ext.util.JSON.decode(result.responseText);
											if (json_result.success) {
												Ext.Msg.hide();
												PC.dialog.gallery.categories.getLoader().load(PC.dialog.gallery.categories.getRootNode(), function(){
													var node = PC.dialog.gallery.categories.getNodeById(0);
													if (node) {
														var path = node.getPath();
														if (path) PC.dialog.gallery.categories.selectPath(path);
													}
													PC.dialog.gallery.select_category(0);
												});
											}
											else {
												PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.trash.empty.error_title, json_result.errors);
											}
										},
										failure: function(){
											PC.dialog.gallery.show_connection_error();
										}
									});
								}
							}
						});
					}
				}
			]
		});
		
		// categories context menu for '0' (root) node
		this.categories_context_for_root = new Ext.menu.Menu({
			items: [
				PC.dialog.gallery.categories_context_items.open,
				{xtype: 'menuseparator'},
				PC.dialog.gallery.categories_context_items.new_category_inside,
				/*{xtype: 'menuseparator'},
				PC.dialog.gallery.categories_context_items.paste*/
				PC.dialog.gallery.categories_context_items.clear_thumb_cache
			]
		});
		
		this.categories_treeloader = new Ext.tree.TreeLoader({
			dataUrl: 'ajax.gallery.php?action=get_categories',
			baseParams: {
				trashed: 0
			}
		});
		this.categories_treeloader.on('beforeload', function(loader, node, callback){
			/*callback = function() {
				alert('callback veikia!');
			}*/
			this.categories_treeloader.baseParams.trashed = (this.is_category_trashed(node.attributes.id)?1:0);
		}, this);
		
		this.categories = new Ext.ux.tree.TreeGrid({
			region: 'west',
			split: true,
			width: 250,
			border: true,
			useArrows: true,
			autoScroll: true,
			animate: true,
			enableSort: false,
			columns:[{
				header: PC.i18n.dialog.gallery.title.category,
				dataIndex: 'category',
				width: 175
			},{
				header: 'MB',
				width: 35,
				dataIndex: 'size',
				sortType: 'asFloat'
			}],
			loader: this.categories_treeloader,
			enableDD: true,
			ddGroup: 'gallery_files_dd',
			ddAppendOnly: true,
			Change: function(n, file_load_callback){
				/*if (n.attributes.id == 'bin') {
					PC.dialog.gallery.settings.show();
					PC.dialog.gallery.settings_tab_panel.setActiveTab('gallery_trash');
				}
				else {*/
					if (dialog.files_view.store.filter != '') {
						PC.dialog.gallery.set_files_store_filter('');
						PC.dialog.gallery.categories.getBottomToolbar().items.get('gallery_files_store_filter').reset();
					}
					n.expand();
					PC.dialog.gallery.select_category(n.attributes.id, file_load_callback);
				//}
			},
			listeners: {
				/*render: function(tree){
					if (PC.ux.gallery.SelectedCategory > 0) {
						setTimeout(function(){
							tree.getNodeById(PC.ux.gallery.SelectedCategory).ensureVisible();
						}, 300);
					}
				},*/
				render: dialog.Select_initial_file,
				beforeappend: function(tree, parent, node) {
					if (node.id == '0') {
						setTimeout(function(){
							node.setText(PC.i18n.dialog.gallery.title.gallery);
						}, 50);
					}
					else if (node.id == 'bin') {
						setTimeout(function(){
							node.setText(PC.i18n.dialog.gallery.title.trash);
						}, 50);
					}
				},
				contextmenu: function(node, e){
					PC.dialog.gallery.categories_context.contextNode = node;
					if (node.attributes.id == 'bin') {
						PC.dialog.gallery.categories_context_for_bin.showAt(e.getXY());
					}
					else if (node.attributes.id == '0') {
						PC.dialog.gallery.categories_context_for_root.showAt(e.getXY());
					}
					else if (PC.dialog.gallery.is_category_trashed(node.attributes.id)) {
						PC.dialog.gallery.categories_context_for_trashed_nodes.showAt(e.getXY());
					}
					else {
						PC.dialog.gallery.categories_context.showAt(e.getXY());
					}
				},
				click: function(n){
					this.Change(n);
				},
				/*beforemovenode: function(tree, node, oldParent, newParent, index) {
					Ext.Msg.alert('', '<b>Event:</b> movenode<br /><b>Node</b> '+node+'<br /><b>oldParent</b> '+oldParent+'<br /><b>newParent</b> '+newParent+'<br /><b>index</b> '+index);
				},*/
				beforenodedrop: function(ev) {
					if (ev.data.nodes != undefined) {
						var dropped_files = '';
						if (ev.data.single) {
							dropped_files += ev.data.nodes[0].getAttribute('id');
						}
						else {
							for (var a=0; ev.dropNode[a] != undefined; a=a+1) {
								dropped_files += ev.data.nodes[a].getAttribute('id') +',';
							}
						}
						Ext.Ajax.request({
							url: 'ajax.gallery.php?action=move_files',
							method: 'POST', params: {files: dropped_files, target: ev.target.id},
							success: function(result){
								var json_result = Ext.util.JSON.decode(result.responseText);
								if (json_result.success) {
									var target_node = ev.tree.getNodeById(ev.target.id);
									var target_path = target_node.getPath();
									//PC.dialog.gallery.categories.getRootNode().reload();
									PC.dialog.gallery.categories.getLoader().load(PC.dialog.gallery.categories.getRootNode());
									ev.tree.selectPath(target_path);
									PC.dialog.gallery.select_category(ev.target.id);
								}
								else {
									PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.category.create.error_title, json_result.errors);
								}
							},
							failure: function(){
								Editor.editNode.destroy();
								PC.dialog.gallery.show_connection_error();
							}
						});
						ev.dropStatus = true;
					} else {
						//console.log(ev); return;
						Ext.Ajax.request({
							url: 'ajax.gallery.php?action=move_category',
							method: 'POST', params: {
								category: ev.dropNode.id,
								target: ev.target.id,
								position: 0
							},
							success: function(result){
								var json_result = Ext.util.JSON.decode(result.responseText);
								if (json_result.success) {
									var target_node = ev.tree.getNodeById(ev.target.id);
									var target_path = target_node.getPath();
									PC.dialog.gallery.categories.getLoader().load(PC.dialog.gallery.categories.getRootNode(), function(){
										ev.tree.selectPath(target_path, null, function(){
											ev.tree.getNodeById(ev.target.id).expand();
											//PC.dialog.gallery.select_category(ev.dropNode.id);
										});
									});
								}
								else {
									PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.category.create.error_title, json_result.errors);
								}
							},
							failure: function(){
								Editor.editNode.destroy();
								PC.dialog.gallery.show_connection_error();
							}
						});
						ev.dropStatus = true;
					}
					ev.cancel = true;
				}
			},
			bbar: {
				items: [
					/*{icon: 'images/camera.png'},
					{icon: 'images/Compile.png'},
					{xtype:'tbfill'},*/
					{	text: PC.i18n.dialog.gallery.title.search, xtype: 'textfield',
						id: 'gallery_files_store_filter',
						name: 'search_gallery',
						emptyText: PC.i18n.dialog.gallery.search.empty_text,
						style: 'font-style: italic',
						listeners: {
							specialkey: function(field, e) {
								if (e.getKey() == e.ENTER) {
									if (field.getValue() != dialog.files_view.store.filter) {
										if (field.getValue().length > 0) {
											field.getEl().frame("999966", 1, { duration: 0.5});
											PC.dialog.gallery.set_files_store_filter(field.getValue());
											dialog.files_view.store.load();
										}
										else {
											PC.dialog.gallery.set_files_store_filter('');
											dialog.files_view.store.load();
										}
									}
								}
							}
						}
					},
					{	icon: 'images/Search.png',
						handler: function(b) {
							field = b.ownerCt.items.get('gallery_files_store_filter');
							var e = {
								getKey: function() {
									return Ext.EventManager.ENTER;
								}
							};
							field.fireEvent('specialkey', field, e);
						}
					},
					{	icon: 'images/zoom_out.png',
						handler: function(b) {
							var field = b.ownerCt.items.get('gallery_files_store_filter');
							field.setValue('');
							var e = {
								getKey: function() {
									return Ext.EventManager.ENTER;
								}
							};
							field.fireEvent('specialkey', field, e);
						}
					}
				]
			}
		});
		
		this.categories_editor = new Ext.tree.TreeEditor(PC.dialog.gallery.categories, {}, {
			editDelay: 350,
			listeners: {
				canceledit: function(Editor, value, start_value){
					if (Editor.editNode.disabled && start_value == '') {
						Editor.editNode.destroy();
					}
				},
				beforecomplete: function(Editor, value, start_value){
					if (Editor.editNode.disabled) {
						if (value == '') {
							value = PC.i18n.dialog.gallery.category.create.default_name;
							Editor.setValue(value);
							//Editor.editNode.destroy();
							//return;
						}
						Ext.Ajax.request({
							url: 'ajax.gallery.php?action=create_category',
							method: 'POST', params: {parent: Editor.editNode.parentNode.id, category: value},
							success: function(result){
								var json_result = Ext.util.JSON.decode(result.responseText);
								if (json_result.success) {
									Editor.editNode.enable();
									Editor.editNode.setId(json_result.id);
									PC.dialog.gallery.select_category(json_result.id);
								}
								else {
									Editor.editNode.destroy();
									PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.category.create.error_title, json_result.errors);
								}
							},
							failure: function(){
								Editor.editNode.destroy();
								PC.dialog.gallery.show_connection_error();
							}
						});
					}
					else {
						if (start_value == value) {
							return;
						}
						if (value == '') {
							Editor.setValue(start_value);
							return;
						}
						Ext.Ajax.request({
							url: 'ajax.gallery.php?action=rename_category',
							method: 'POST', params: {category_id: Editor.editNode.id, category: value, old_category: start_value},
							success: function(result){
								var json_result = Ext.util.JSON.decode(result.responseText);
								if (!json_result.success) {
									Editor.editNode.setText(start_value);
									PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.category.rename.error_title, json_result.errors);
									return false;
								}
							},
							failure: function(){
								Editor.editNode.setText(start_value);
								PC.dialog.gallery.show_connection_error();
								return false;
							}
						});
					}
				},
				startedit: function(el, value){
					if (value == '') {
						PC.dialog.gallery.categories_editor.setValue(PC.dialog.gallery.categories_editor.editNode.ui.getTextEl().innerHTML);
					}
				}
			}
		});
		
		this.filesStore = new PC.ux.gallery.files.Store();
		
		this.files_template = new PC.ux.gallery.files.Template({store: this.filesStore});
		
		this.files_view = new PC.ux.gallery.files.View({
			store: this.filesStore,
			tpl: PC.dialog.gallery.files_template,
			listeners: {
				dblclick: function(view, index, node) {
					var current = PC.ux.gallery.SelectedCategory;
					if (current != 0 && current != undefined) {
						if (current == 'bin') return;
						var node = PC.dialog.gallery.categories.getNodeById(current);
						if (node) if (node.attributes.trashed != undefined) if (node.attributes.trashed) return;
					}
					PC.dialog.gallery.insert_files('small/');
					if (PC.dialog.gallery.params.close_after_insert_forced) {
						PC.dialog.gallery.window.hide();
					}
				},
				containercontextmenu: function(view, e){
					//e.preventDefault();
					//PC.dialog.gallery.files_view_menu.showAt(e.getXY());
				},
				contextmenu: function(view, index, node, e) {
					e.preventDefault();
					PC.dialog.gallery.files_view_menu.showAt(e.getXY());
				},
				selectionchange: function(view, selections) {
					var gallery = PC.dialog.gallery;
					var actions = PC.ux.gallery.files.actions;
					//future: need to attract attention when items are enabled
					var files = selections.length;
					if (files > 0) {
						actions.Get('Restore', view).setDisabled(0);
						actions.Get('Edit', view).setDisabled(0);
						actions.Get('Insert', view).setDisabled(0);
						actions.Get('Rename', view).setDisabled(0);
						//actions.Get('preview', view).setDisabled(0);
						actions.Get('Delete', view).setDisabled(0);
						actions.Get('Trash', view).setDisabled(0);
						actions.Get('Insert', view).each(function(obj) {
							Ext.iterate(obj.menu.find('_class','thumbnail-type'), function(item){
								item.enable();
							});
						});
						var selected_files = gallery.files_view.getSelectedIndexes();
						if (files == 1) {
							actions.Get('CopyLink', view).setDisabled(0);
							var record = dialog.files_view.store.getAt(selected_files[0]);
							if (record.data.filetype != 'image') {
								actions.Get('CreateThumbnail', view).setDisabled(1);
								actions.Get('Insert', view).each(function(obj) {
									Ext.iterate(obj.menu.find('_class','thumbnail-type'), function(item){
										item.disable();
									});
								});
							} else {
								actions.Get('CreateThumbnail', view).setDisabled(0);
							}
						} else {
							actions.Get('CopyLink', view).setDisabled(1);
							actions.Get('CreateThumbnail', view).setDisabled(1);
							//actions.Preview.setDisabled(1);
							actions.Get('Rename', view).setDisabled(1);
							var images_included = false;
							Ext.each(selected_files, function(item){
								var record = dialog.files_view.store.getAt(item);
								if (record.data.filetype == 'image') {
									images_included = true;
								}
							});
							if (!images_included) {
								actions.Get('Insert', view).each(function(obj) {
									Ext.iterate(obj.menu.find('_class','thumbnail-type'), function(item){
										item.disable();
									});
								});
							}
						}
					}
					else {
						actions.Get('Restore', view).setDisabled(1);
						actions.Get('CopyLink', view).setDisabled(1);
						actions.Get('Edit', view).setDisabled(1);
						actions.Get('Insert', view).setDisabled(1);
						actions.Get('CreateThumbnail', view).setDisabled(1);
						actions.Get('Rename', view).setDisabled(1);
						//actions.Get('preview', view).setDisabled(1);
						actions.Get('Delete', view).setDisabled(1);
						actions.Get('Trash', view).setDisabled(1);
					}
				}
			}
		});
		
		this.files_view_menu = new Ext.menu.Menu({
			items: [
				new Ext.menu.Item(PC.ux.gallery.files.actions.Get('Insert', this.files_view)),
				new Ext.menu.Item(PC.ux.gallery.files.actions.Get('CreateThumbnail', this.files_view)),
				PC.ux.gallery.files.actions.Get('Rename', this.files_view),
				PC.ux.gallery.files.actions.Get('Delete', this.files_view),
				PC.ux.gallery.files.actions.Get('Trash', this.files_view),
				PC.ux.gallery.files.actions.Get('Restore', this.files_view),
				PC.ux.gallery.files.actions.Get('CopyLink', this.files_view)
			]
		});
		
		this.files_toolbar = new Ext.Toolbar({
			items: [
				PC.ux.gallery.files.actions.Get('Upload', this.files_view),
				{xtype:'tbseparator'},
				PC.ux.gallery.files.actions.Get('Restore', this.files_view),
				PC.ux.gallery.files.actions.Get('Insert', this.files_view),
				//PC.ux.gallery.files.actions.Get('Edit', this.files_view),
				PC.ux.gallery.files.actions.Get('CreateThumbnail', this.files_view),
				PC.ux.gallery.files.actions.Get('Rename', this.files_view),
				PC.ux.gallery.files.actions.Get('Trash', this.files_view),
				PC.ux.gallery.files.actions.Get('Delete', this.files_view),
				PC.ux.gallery.files.actions.Get('CopyLink', this.files_view),
				{xtype: 'tbfill'},
				PC.ux.gallery.files.actions.Get('Sorting', this.files_view),
				PC.ux.gallery.files.actions.Get('ChangeTemplate', this.files_view),
				PC.ux.gallery.files.actions.Get('Settings', this.files_view)
			]
		});
		
		//this.files_view.region = 'center';
		this.files_view.flex = 1;
		
		this.empty_view = new Ext.BoxComponent({
			width: 20,
			//height: 20,
			region: 'east'
		});
		
		this.files = new Ext.Panel({
			ref: '_files',
			region: 'center',
			//layout: 'table',
			
			layout: {
				type: 'table'
				//extraCls: 'pc_gallery_table'
			},
			
			//items: [this.files_view, this.empty_view],
			items: this.files_view,
			tbar: this.files_toolbar
		});
		
		var temp_window = false;
		
		if (PC.ux.gallery.CloseAfterClickOutside) {
			temp_window = true;
		}
		
		this.window = new PC.ux.Window({
			pc_temp_window: temp_window,
			title: PC.i18n.dialog.gallery.title.gallery,
			border: false,
			layout: 'border',
			//modal: true,
			resizable: true,
			closeAction: 'hide',
			maximizable: true,
			width: 910,
			height: 450,
			items: [PC.dialog.gallery.categories, PC.dialog.gallery.files],
			listeners: {
				//dont allow to drag gallery window outside viewable area (currently doesn't support draging out of view in the right or bottom)
				/*,deactivate: function(){
					if (!dialog.child_window_active) dialog.window.hide();
				}*/
			}
		});

		// Gallery settings dialog
		// Thumbnail settings dialog
		this.thumbnail_type = Ext.data.Record.create([
			{name: 'thumbnail_type', type: 'string'},
			{name: 'thumbnail_max_w', type: 'int'},
			{name: 'thumbnail_max_h', type: 'int'},
			{name: 'thumbnail_quality', type: 'int'}
		]);
		this.thumbnailparams_toolbar = new Ext.Toolbar({
			items: [
				{	text: PC.i18n.add,
					icon: 'images/add.gif',
					handler: function() {
						dialog.thumbnailparams_editor.stopEditing();
						//add our new record as the first row, select it
						dialog.thumbnail_types_store.insert(0, new dialog.thumbnail_type({group: 0, _new: true}));
						dialog.thumbnailparams.getView().refresh();
						dialog.thumbnailparams.getSelectionModel().selectRow(0);
						dialog.thumbnail_types_store.getAt(0).set('thumbnail_quality', 76);
						//start editing our new type
						dialog.thumbnailparams_editor.startEditing(0);
					}
				},
				{	text: PC.i18n.edit,
					id: 'tt-edit', disabled: true,
					icon: 'images/edit.gif',
					handler: function() {
						var index = dialog.thumbnailparams.getSelectionModel().getSelected();
						if (index) dialog.thumbnailparams_editor.startEditing(index);
					}
				},
				{	text: PC.i18n.del,
					id: 'tt-delete', disabled: true,
					icon: 'images/delete.png',
					handler: function() {
						var record = PC.dialog.gallery.thumbnailparams.getSelectionModel().getSelected();
						if (!record) return;
						if (PC.dialog.gallery.is_default_thumbnail_type(record.data.thumbnail_type)) {
							var thumb_title = PC.i18n.dialog.gallery.thumbnails[record.data.thumbnail_type];
						}
						else {
							var thumb_title = record.data.thumbnail_type;
						}
						Ext.MessageBox.show({
							title: PC.i18n.msg.title.confirm,
							msg: String.format(PC.i18n.msg.perm_del, '"'+record.data.thumbnail_type+'"'),
							buttons: Ext.MessageBox.YESNO,
							icon: Ext.MessageBox.WARNING,
							fn: function(rslt) {
								switch (rslt) {
								case 'yes':
									Ext.Ajax.request({
										url: 'ajax.gallery.php?action=delete_thumbnail_type',
										method: 'POST',
										params: {
											thumbnail_type: record.data.thumbnail_type
										},
										callback: function(options, success, result){
											if (success) {
												var json_result = Ext.util.JSON.decode(result.responseText);
												if (json_result.success) {
													PC.dialog.gallery.thumbnail_types_store.remove(record);
												}
												else {
													PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.thumbnails._delete.error_title, json_result.errors);
												}
											}
											else {
												PC.dialog.gallery.show_connection_error();
											}
											var records = PC.dialog.gallery.thumbnail_types_store.getRange();
											PC.dialog.gallery.reload_thumbnail_types_in_menus(records.reverse());
										}
									});
									break;
								default: // case 'no':
								}
							}
						});
					}
				}
			]
		});
		this.thumbnail_types_store = new Ext.data.GroupingStore({
			url: 'ajax.gallery.php?action=get_thumbnail_types',
			reader: new Ext.data.JsonReader({
				fields: [
					'thumbnail_type',
					{name: 'type', mapping: 'thumbnail_type', convert: this.translate_thumbnail_type},
					{name: 'group', mapping: 'thumbnail_type', convert: this.is_default_thumbnail_type},
					'thumbnail_max_w', 'thumbnail_max_h', 'thumbnail_quality', 'use_adaptive_resize'
				]
			}),
			autoLoad: true,
			groupField: 'group',
			sortInfo: {
				field: 'group',
				direction: 'ASC'
			},
			listeners: {
				load: function(store, records) {
					PC.dialog.gallery.reload_thumbnail_types_in_menus(records);
				}
			}
		});
		this.thumbnailparams_editor = new Ext.ux.grid.RowEditor({
			saveText: PC.i18n.dialog.gallery.button.save,
			cancelText: PC.i18n.dialog.gallery.button.canceledit,
			clicksToEdit: 2,
			listeners: {
				beforeedit: function() {
					//return false;
				},
				canceledit: function(editor, button, record) {
					if (record.data._new) {
						PC.dialog.gallery.thumbnail_types_store.remove(record);
					}
					else if (record.data.type == undefined) {
						PC.dialog.gallery.thumbnail_types_store.rejectChanges();
						//PC.dialog.gallery.thumbnail_types_store.remove(record);
					}
				},
				afteredit: function(roweditor, changes, record, rowIndex) {
					if (record.data.type == undefined || record.data.type == '') {
						PC.dialog.gallery.thumbnail_types_store.remove(record);
						return;
					}
					if (record.data.group && changes['type']) {
						PC.dialog.gallery.thumbnail_types_store.rejectChanges();
						Ext.Msg.alert(PC.i18n.error, PC.i18n.dialog.gallery.error.change_default_type_name);
						PC.dialog.gallery.thumbnail_types_store.rejectChanges();
						return false;
					}
					if (false && record.data.group && changes['use_adaptive_resize'] != undefined) {
						Ext.Msg.alert(PC.i18n.error, PC.i18n.dialog.gallery.error.change_default_type_resize);
						PC.dialog.gallery.thumbnail_types_store.rejectChanges();
						return false;
					}
					if (changes.thumbnail_quality == undefined) {
						changes.thumbnail_quality = 76;
					}
					Ext.Ajax.request({
						url: 'ajax.gallery.php?action=' + (record.data._new?'create_thumbnail_type':'edit_thumbnail_type'),
						method: 'POST',
						params: {
							thumbnail_type: record.data.thumbnail_type,
							changes: Ext.util.JSON.encode(changes)
						},
						callback: function(options, success, result){
							if (success) {
								var json_result = Ext.util.JSON.decode(result.responseText);
								if (json_result.success) {
									if (record.data._new) {
										record.data.thumbnail_type = record.data.type;
										record.data._new = undefined;
									}
									PC.dialog.gallery.thumbnail_types_store.commitChanges();
									//record.data.thumbnail_type = json_result.type;
									PC.dialog.gallery.thumbnail_types_store.reload();
								}
								else {
									PC.dialog.gallery.thumbnail_types_store.rejectChanges();
									if (record.data._new) {
										PC.dialog.gallery.thumbnail_types_store.remove(record);
									}
									PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.thumbnails.edit.error_title, json_result.errors);
								}
							}
							else {
								PC.dialog.gallery.thumbnail_types_store.rejectChanges();
								if (record.data._new) {
									PC.dialog.gallery.thumbnail_types_store.remove(record);
								}
								PC.dialog.gallery.show_connection_error();
							}
							//PC.dialog.gallery.thumbnail_types_store.sort('group', 'DESC');
							var records = PC.dialog.gallery.thumbnail_types_store.getRange();
							PC.dialog.gallery.reload_thumbnail_types_in_menus(records.reverse());
						}
					});
				}
			}
		});
		this.thumbnailparams = new Ext.grid.GridPanel({
			layout: 'fit',
			plugins: this.thumbnailparams_editor,
			viewConfig: {
				forceFit: true
			},
			view: new Ext.grid.GroupingView({
				forceFit: true,
				groupTextTpl: '{[values.group?'
				+'PC.i18n.dialog.gallery.thumbnails.group._default'
				+':PC.i18n.dialog.gallery.thumbnails.group.custom]}'
			}),
			height: 350,
			store: this.thumbnail_types_store,
			colModel: new Ext.grid.ColumnModel({
				defaults: {
					sortable: true
				},
				columns: [
					{	header: PC.i18n.dialog.gallery.type,
						dataIndex: 'type',
						editor: {
							xtype: 'textfield',
							allowBlank: false,
							//regex: new RegExp('/^[a-z0-9]+$/'),
							//regexText: 'Netinkamas formatas',
							validator: function(value) {
								if (value != 'thumbnail' && value != 'small' && value != 'large') {
									return true;
								}
								else return PC.i18n.dialog.gallery.cannot_be_default_type;
							},
							minLength: 2, maxLength: 20,
							minLengthText: PC.i18n.dialog.gallery.too_short, maxLengthText: PC.i18n.dialog.gallery.too_long
						}
					},
					{	header: PC.i18n.dialog.gallery.max_w,
						dataIndex: 'thumbnail_max_w',
						editor: {
							xtype: 'numberfield',
							allowBlank: false,
							allowDecimals: false,
							allowNegative: false,
							maxValue: 2000,
							maxText: PC.i18n.dialog.gallery.width_cannot_be_more_than,
							minValue: 5,
							minText: PC.i18n.dialog.gallery.width_cannot_be_less_than,
							preventMark: true
							//invalidText - default invalid text
						},
						width: 40
					},
					{	header: PC.i18n.dialog.gallery.max_h,
						dataIndex: 'thumbnail_max_h',
						editor: {
							xtype: 'numberfield',
							allowBlank: false,
							allowDecimals: false,
							allowNegative: false,
							maxValue: 2000,
							maxText: PC.i18n.dialog.gallery.height_cannot_be_more_than,
							minValue: 5,
							minText: PC.i18n.dialog.gallery.height_cannot_be_less_than,
							preventMark: true
							//invalidText - default invalid text
						},
						width: 40
					},
					{	header: PC.i18n.dialog.gallery.quality_percentage,
						dataIndex: 'thumbnail_quality',
						editor: {
							xtype: 'numberfield',
							allowBlank: false,
							allowDecimals: false,
							allowNegative: false,
							maxValue: 100,
							maxText: PC.i18n.dialog.gallery.max_quality,
							minValue: 10,
							minText: PC.i18n.dialog.gallery.min_quality,
							preventMark: true
						},
						width: 40
					},
					{	header: PC.i18n.dialog.gallery.resize,
						dataIndex: 'use_adaptive_resize',
						editor: {
							xtype: 'combo',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								fields: ['status', 'name'],
								idIndex: 0,
								data: [
									[0, PC.i18n.dialog.gallery.normal],
									//[2, PC.i18n.dialog.gallery.semi_adaptive],
									[1, PC.i18n.dialog.gallery.adaptive]
								]
							},
							displayField: 'name',
							valueField: 'status',
							value: 1,
							editable: false,
							forceSelection: true,
							triggerAction: 'all',
							preventMark: true
						},
						renderer: function(value, metaData, record, rowIndex, colIndex, store) {
							if (value == '1') {return PC.i18n.dialog.gallery.adaptive}
							//else if (value == '2') {return PC.i18n.dialog.gallery.semi_adaptive}
							else return PC.i18n.dialog.gallery.normal;
						},
						width: 40
					},
					{	header: '#default group#',
						dataIndex: 'group',
						hidden: true
					}
				]
			}),
			tbar: PC.dialog.gallery.thumbnailparams_toolbar,
			listeners: {
				specialkey: function(field, e) {
					if (e.getKey() == e.ENTER) {
						alert('enter!');
						return;
						var nextRow = grid.lastEdit.row + 1;
						if (nextRow < grid.view.getRows().length) {
							grid.stopEditing();
							grid.startEditing(nextRow, contributions.lastEdit.col);
							grid.selModel.select(nextRow, grid.lastEdit.col);
						}
					}
				},
				afterrender: function(grid) {
					var selModel = grid.getSelectionModel();
					//add listeners to manipulate with actions toolbar
					selModel.on({
						rowselect: function(selModel, index, record){
							grid.getTopToolbar().findById('tt-edit').enable();
							if (!PC.dialog.gallery.is_default_thumbnail_type(record.data.thumbnail_type)) {
								grid.getTopToolbar().findById('tt-delete').enable();
							}
						},
						rowdeselect: function(selModel, index, record){
							grid.getTopToolbar().findById('tt-edit').disable();
							grid.getTopToolbar().findById('tt-delete').disable();
						},
						selectionchange: function(selModel){
							var index = PC.dialog.gallery.thumbnailparams.getSelectionModel().getSelected();
							if (!index) {
								grid.getTopToolbar().findById('tt-edit').disable();
								grid.getTopToolbar().findById('tt-delete').disable();
							}
						}
					});
				}
			}
		});
		this.settings = new Ext.Window({
			title: PC.i18n.dialog.gallery.title.settings,
			iconCls: 'gallery_panelparams',
			closeAction: 'hide',
			width: 600,
			items: [this.thumbnailparams]/*,
			listeners: {
				deactivate: function() {
					//PC.dialog.gallery.settings.hide();
				}
			}*/
		});
		
		this.window.pc_temp_window_children['settings'] = this.settings;
		this.window.pc_temp_window_children['settings_split_button'] = 'settings_split_button';
		this.window.pc_temp_window_children['change_template_split_button'] = 'change_template_split_button';
		this.window.pc_temp_window_children['sorting_split_button'] = 'sorting_split_button';
		
		if (typeof dialog.params.save_fn == 'function' && !dialog.params.show_insert) {
			PC.ux.gallery.files.actions.Get('Insert', this.files_view).hide();
		}
		else {
			//dialog.show_window(PC.ux.gallery.files.actions.Get('Insert', this.files_view));
			PC.ux.gallery.files.actions.Get('Insert', this.files_view).show();
		}
		this.window.show();
		
		this.files.body.setStyle('overflow', 'auto');
		//this.files.body.setStyle('height', '100%');
		this.files.body.addClass('pc_gallery_body');
		/*
		var files_view_el = Ext.get(this.files_view.id);
		if (files_view_el) {
			files_view_el.setStyle('display', 'table');
		}
		*/
		
		Ext.extend(PC.dialog.gallery.file_drag_zone, Ext.dd.DragZone, {
			// We don't want to register our file elements, so let's 
			// override the default registry lookup to fetch the file 
			// from the event instead
			getDragData : function(e){
				var target = e.getTarget('.thumb-wrap');
				if(target){
					var view = this.view;
					if(!view.isSelected(target)){
						if (e.button == 2) {
							view.onClick(e);
						}
					}
					var selNodes = view.getSelectedNodes();
					var dragData = {
						nodes: selNodes
					};
					if(selNodes.length == 1){
						dragData.ddel = target;
						//dragData.ddel = Ext.Element(selNodes[0]).child('.drag-img').dom;
						dragData.single = true;
					}else{
						var div = document.createElement('div'); // create the multi element drag "ghost"
						div.className = 'multi-proxy';
						for(var i = 0, len = selNodes.length; i < len; i++){
							//console.log(selNodes[i]);
							div.appendChild(selNodes[i].firstChild.firstChild.cloneNode(true)); // file nodes only
							if((i+1) % 4 == 0){
								div.appendChild(document.createElement('br'));
							}
						}
						var count = document.createElement('div'); // selected file count
						count.innerHTML = i + PC.i18n.dialog.gallery.files_selected_suffix;
						div.appendChild(count);
						
						dragData.ddel = div;
						dragData.multi = true;
					}
					return dragData;
				}
				return false;
			},

			// this method is called by the TreeDropZone after a node drop
			// to get the new tree node (there are also other way, but this is easiest)
			getTreeNode: function(){
				var treeNodes = [];
				var nodeData = this.view.getRecords(this.dragData.nodes);
				for(var i = 0, len = nodeData.length; i < len; i++){
					var data = nodeData[i].data;
					treeNodes.push(new Ext.tree.TreeNode({
						text: data.name,
						data: data,
						leaf: true,
						cls: 'image-node'
					}));
				}
				return treeNodes;
			},
			
			// the default action is to "highlight" after a bad drop
			// but since an image can't be highlighted, let's frame it 
			afterRepair: function(){
				for(var i = 0, len = this.dragData.nodes.length; i < len; i++){
					Ext.fly(this.dragData.nodes[i]).frame('#8db2e3', 1);
				}
				this.dragging = false;    
			},
			// override the default repairXY with one offset for the margins and padding
			getRepairXY : function(e){
				if(!this.dragData.multi){
					var xy = Ext.Element.fly(this.dragData.ddel).getXY();
					xy[0]+=3;xy[1]+=3;
					return xy;
				}
				return false;
			}
		});
		
		PC.dialog.gallery.dragzone = new PC.dialog.gallery.file_drag_zone(PC.dialog.gallery.files_view, {containerScroll:true, ddGroup: 'gallery_files_dd'});
	},
	preview_image: function(index) {
		var dialog = PC.dialog.gallery;
		Ext.Msg.show({
			title: PC.i18n.msg.title.loading,
			width: 200,
			wait: true,
			waitConfig: {interval:75}
		});
		var record = dialog.files_view.store.getAt(index);
		var image_src = PC.global.BASE_URL +'gallery/';
		if (this.is_category_trashed()) {
			image_src += PC.global.ADMIN_DIR +'/';
		}
		image_src += record.data.path+'large/'+record.data.name;
		var image = new Image();
		image.onerror = function(){
			Ext.Msg.hide();
		}
		image.onload = function(){
			/*if (image.width > 500 || image.height > 500) {
				var resize_ratio = 500 / Math.max(image.width, image.height);
				alert(resize_ratio);
				Ext.get('gallery_image_preview').set({width: image.width * resize_ratio, height: image.height * resize_ratio});
			}*/
			var image_preview = new Ext.Window({
				title: record.data.name,
				modal: true,
				border: false,
				resizable: false,
				width: image.width+13,
				height: image.height+30,
				items: {
					xtype: 'panel',
					width: image.width,
					height: image.height,
					html: '<img id="gallery_image_preview" src="'+ image_src +'" alt="" />'
				}
				//autoScroll: true,
				/*listeners: {
					deactivate: function() {
						image_preview.close();
					}
				},*/
				/*keys: new Ext.KeyMap(image_preview.getEl(), [
					{	key: Ext.EventObject.LEFT,
						fn: function() {
							alert('left');
						}
					},
					{
						key: Ext.EventObject.RIGHT,
						fn: function() {
							alert('right');
						}
					}
				])*/
			});
			dialog.window.pc_temp_window_children['preview'] = image_preview;
			image_preview.show();
			//dialog.show_window(image_preview);
			//close preview when image is clicked
			Ext.get('gallery_image_preview').on('click', function(){
				image_preview.close();
			});
			Ext.Msg.hide();
		};
		image.src = image_src;
	},
	change_files_template: function(template, button) {
		PC.ux.gallery.FilesCurrentTemplate = template;
		PC.utils.setCookie('admin_files_current_template', template);
		PC.dialog.gallery.files_view.refresh();
	},
	set_files_store_filter: function(filter) {
		this.files_view.store.filter = filter;
		this.files_view.store.setBaseParam('filter', filter);
	},
	/*short_name_for_detailed_tpl: function(name, record) {
		return PC.ux.gallery.files.GetShortName(name, record, 'detailed');
	},*/
	show_uploader: function() {
		var dialog = this;
		var extraPostData = {
			action: 'upload_file',
			category_id: PC.ux.gallery.SelectedCategory,
			//needs edit: session cookie name should be returned by php's function session_name()
			phpsessid: PC.utils.getCookie(PC.global['session.name'])
		};
		if (dialog.params) {
			if (dialog.params.type) {
				extraPostData['dialog_type'] = dialog.params.type;
			}
		}
		this.awesome_uploader = {
			xtype: 'awesomeuploader',
			standardUploadUrl: 'ajax.gallery.php?action=upload_file&category_id=' + PC.ux.gallery.SelectedCategory,
			flashUploadUrl: 'ajax.gallery.php?action=upload_file&category_id=' + PC.ux.gallery.SelectedCategory,
			xhrUploadUrl: 'ajax.gallery.php?action=upload_file&category_id=' + PC.ux.gallery.SelectedCategory,
			extraPostData: extraPostData,
			listeners: {
				//scope: this,
				/*render: function(){
					//alert(this.swfUploader);
				},*/
				fileupload: function(uploader, success, result) {
					if (success) {
						dialog.files_view.store.load();
					}
				},
				allfilesuploadedsuccessfully: function(){
					setTimeout(function(){
						PC.dialog.gallery.awesome_uploader_window.close();
					}, 250);
					
					var path = PC.dialog.gallery.categories.getNodeById(PC.ux.gallery.SelectedCategory).getPath();
						
					PC.dialog.gallery.categories.getLoader().load(PC.dialog.gallery.categories.getRootNode(), function(){
						//var n = PC.dialog.gallery.categories.getNodeById(PC.ux.gallery.SelectedCategory);
						//if (n) n.ensureVisible();
						if (path) {
							PC.dialog.gallery.categories.selectPath(path);
						}
					});
					
				}
			}
		};
		if (PC.dialog.gallery.awesome_uploader_window) PC.dialog.gallery.awesome_uploader_window.destroy();
		this.awesome_uploader_window = new Ext.Window({
			title: PC.i18n.dialog.gallery.title.uploader,
			closeAction: 'hide',
			width: 800,
			height: 400,
			layout: 'anchor',
			border: false,
			items: this.awesome_uploader,
			resizable: false
		});
		dialog.window.pc_temp_window_children['uploader'] = this.awesome_uploader_window;
		this.awesome_uploader_window.show();
		//dialog.show_window(PC.dialog.gallery.awesome_uploader_window);
	},
	file_drag_zone: function(view, config){
		this.view = view;
		PC.dialog.gallery.file_drag_zone.superclass.constructor.call(this, view.getEl(), config);
	},
	insert_files: function(type, files) {
		var dialog = this;
		if (files == undefined) {
			var temp = this.files_view.getSelectedNodes();
			var selected_files = [];
			Ext.iterate(temp, function(n){
				selected_files.push(dialog.files_view.store.getById(n.id));
			});
		}
		else {
			var selected_files = new Array();
			selected_files[0] = files;
		}
		//console.log(selected_files);
		var record;
		if (dialog.image_preview) {
			if (!dialog.image_preview.hidden) {
				var preview_reshow = true;
				dialog.image_preview.hide();
			} else if (preview_reshow) preview_reshow = false;
		}
		var callbackAfterInsert = function(){
			dialog.window.hide();
		}
		if (typeof dialog.params.save_fn == 'function') {
			//record = dialog.files_view.store.getAt(selected_files[0]);
			
			var number_of_files = selected_files.length;
			 
			for (var a=0; a < number_of_files; a++) {
				record = selected_files[a];
				if (record.data.filetype != 'image') {
					var file_src = 'gallery/'+ PC.global.ADMIN_DIR +'/id/'+ record.data.id;
				}
				else {
					//var file_src = PC.global.BASE_URL +'gallery/'+record.data.path +'large/'+ record.data.name;
					var file_src = 'gallery/'+ PC.global.ADMIN_DIR +'/id/';
					if (typeof dialog.params.thumbnail_type!='undefined') {
						if (dialog.params.thumbnail_type != null) {
							if (dialog.params.thumbnail_type != '') {
								file_src += dialog.params.thumbnail_type + '/';
							}
						}
					}
					else if (type) {
						file_src += type;
					}
					else file_src += 'small/';
					file_src += record.data.id;
				}
				if (a == number_of_files - 1 || !dialog.params.show_insert) {
					dialog.params.save_fn(file_src, record, callbackAfterInsert, dialog.params);
					if (dialog.params.close_after_insert_forced) {
						dialog.window.hide();
					}
					return;
				}
				else {
					dialog.params.save_fn(file_src, record, null, dialog.params);
				}
			}
			if (dialog.params.close_after_insert_forced) {
				dialog.window.hide();
			}
			return;
		}
		callbackAfterInsert();
		var _insert_file = function(record) {
			if (record.data.filetype == 'image' && type != 'links') {
				//var image_link = PC.global.BASE_URL +'gallery/'+ record.data.path +'large/'+ record.data.name;
				var image_link = 'gallery/'+ PC.global.ADMIN_DIR +'/id/large/'+ record.data.id;
				//var image_src = PC.global.BASE_URL +'gallery/'+ record.data.path + type + record.data.name;
				var image_src = 'gallery/'+ PC.global.ADMIN_DIR +'/id/'+ type + record.data.id;
				
				var insert_code = '<a href="'+ image_link +'" target="blank"><img src="'+ image_src +'" alt="" '+(type=='large/'?'rel="nopopup" ':'');
				var width = '';
				var height = '';
				var _img = document.createElement('img');
				_img.onload = function(){
					var width = _img.width;
					var height = _img.height;
					insert_code += 'width="'+ width +'" height="'+ height +'"/></a>';
					if (tinyMCE.activeEditor.selection.getNode().parentNode) {
						var parent_tag_name = tinyMCE.activeEditor.selection.getNode().parentNode.tagName;
						if (parent_tag_name == 'A' || parent_tag_name == 'a') {
							tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.selection.getNode().parentNode);
						}
					}
					tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,insert_code);
				}
				_img.onerror = function(){
					insert_code += '"/></a>';
					tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,insert_code);
				}
				_img.src = PC.global.BASE_URL + image_src;
			}
			else if (record.data.extension == 'swf') {
				var file_src = 'gallery/'+ PC.global.ADMIN_DIR +'/id/'+ record.data.id;
				var insert_code = '<object style="" width="320" height="240" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"'
						+' codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0">'
						+'<param name="src" value="'+ file_src +'">'
						+'<embed src="'+ file_src +'" type="application/x-shockwave-flash" width="320" height="240"></embed>'
					+'</object>';
				tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,insert_code);
			}
			else {
				if (record.data.filetype != 'image') {
					//var file_src = PC.global.BASE_URL +'gallery/'+ record.data.path + record.data.name;
					var file_src = PC.global.BASE_URL +'gallery/'+ PC.global.ADMIN_DIR +'/id/'+ record.data.id;
				}
				else {
					//var file_src = PC.global.BASE_URL +'gallery/'+record.data.path +'large/'+ record.data.name;
					var file_src = PC.global.BASE_URL +'gallery/'+ PC.global.ADMIN_DIR +'/id/large/'+ record.data.id;
				}
				var insert_code = '<a href="'+ file_src +'" target="blank">'+record.data.name+'</a>';
				if (a > 0) insert_code = ', '+ insert_code;
				tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,insert_code);
			}
			
		}
		for (var a=0; selected_files[a] != undefined; a=a+1) {
			//record = dialog.files_view.store.getAt(selected_files[a]);
			record = selected_files[a];
			//console.log(record.data.filename);
			_insert_file(record);
		}
		if (!PC.ux.gallery.CloseAfterInsert) {
			setTimeout(function() {
				dialog.window.show();
				if (preview_reshow) dialog.show_window(dialog.image_preview);// dialog.image_preview.show();
			}, 300);
		}
	},
	restore_trash: function() {
		var gallery = PC.dialog.gallery
		var indexes = gallery.files_view.getSelectedIndexes();
		//format file_ids string concated from indexes separated by commas (server-side desirable format)
		var file_ids = '';
		if (indexes.length < 1) {
			return false;
		}
		var record;
		for (var a=0; indexes[a] != undefined; a++) {
			record = gallery.files_view.store.getAt(indexes[a]);
			file_ids += record.data.id +',';
		}
		file_ids = file_ids.slice(0, -1);
		var i18n = PC.i18n.dialog.gallery;
		var message = i18n.files.restore.confirmation.message;
		Ext.Msg.show({
			title: i18n.files.remove.confirmation.title,
			msg: message,
			buttons: {
				ok: i18n.button.ok,
				cancel: i18n.button.cancel
			},
			fn: function(bid) {
				if (bid == 'ok') {
					Ext.Ajax.request({
						url: 'ajax.gallery.php?action=restore_files',
						method: 'POST',
						params: {
							file_ids: file_ids,
							category: PC.ux.gallery.SelectedCategory
						},
						success: function(result){
							var json_result = Ext.util.JSON.decode(result.responseText);
							if (json_result.success) {
								//reload filelist in the category
								gallery.files_view.store.load();
								//reload category sizes in the tree
								gallery.categories.getLoader().load(gallery.categories.getRootNode());
								setTimeout(function(){
									var node = gallery.categories.getNodeById(PC.ux.gallery.SelectedCategory);
									if (node) node.ensureVisible();
								}, 500);
							}
							else {
								gallery.show_request_errors(i18n.categories.remove.error_title, json_result.errors, true);
							}
						},
						failure: function(){
							gallery.show_connection_error();
						}
					});
				}
			}
		});
	},
	delete_trash: function() {
		Ext.Msg.show({
			title: PC.i18n.dialog.gallery.trash._delete.confirmation.title,
			msg: PC.i18n.dialog.gallery.trash._delete.confirmation.message,
			buttons: {
				ok: PC.i18n.dialog.gallery.button.ok,
				cancel: PC.i18n.dialog.gallery.button.cancel
			},
			fn: function(bid) {
				if (bid == 'ok') {
					var selected = PC.dialog.gallery.trash.getSelectionModel().getSelected();
					Ext.Ajax.request({
						url: 'ajax.gallery.php?action=delete_trash',
						method: 'POST',
						params: {
							trash: selected.data.trash
						},
						success: function(result){
							var json_result = Ext.util.JSON.decode(result.responseText);
							if (!json_result.success) {
								PC.dialog.gallery.show_request_errors(json_result.errors, PC.i18n.dialog.gallery.trash._delete);
							}
						},
						failure: function(){
							PC.dialog.gallery.show_connection_error();
						}
					});
				}
			}
		});
	},
	show_request_results: function(title, results, show_failed_only) {
		/*var message = 'Items succeeded: '+results.succeeded+'<br />'
		+'Items failed: '+results.failed+'<br /><br />';*/
		var message = '';
		// List all items and their results
		for (var a=0; results['items'][a] != undefined; a++) {
			if (!results['items'][a].succeeded) {
				//show failed item
				message += '<br /><img class="ico" src="images/cross.png" alt="" /> <b>'+ results['items'][a].title +'</b>';
				//list item errors
				for (var b=0; results['items'][a].errors[b] != undefined; b++) {
					message += '<br />&nbsp; <img class="ico" src="images/cancel.png" alt="" /> ';
					message += eval('PC.i18n.dialog.gallery.error.'+ results['items'][a].errors[b]);
				}
				if (results['items'][a+1] != undefined) {
					message += '<br /><br />';
				}
			}
			else if (!show_failed_only) {
				//show succeeded item
				message += '<img class="ico" src="images/tick.png" alt="" /> <b>'+ results['items'][a].title +'</b>';
				//list item data
				for (data_item in results['items'][a].data) {
					message += '<br />'+ data_item.name +': '+ data_item.value;
				}
				if (results['items'][a+1] != undefined) {
					message += '<br />';
				}
			}
		}
		Ext.Msg.show({
			title: '<img class="ico" src="images/info.png" alt="" /> '+ title,
			msg: message,
			width: 400,
			buttons: {
				ok: PC.i18n.dialog.gallery.button.close
			}
		});
	},
	show_request_errors: function(title, errors) {
		var error_list = '';
		for (var a=0; errors[a] != undefined; a=a+1) {
			error_list += '<img class="ico" src="images/cancel.png" alt="" /> '+ eval('PC.i18n.dialog.gallery.error.'+ errors[a]);
			if (errors[a+1] != undefined) error_list += '<br />';
		}
		Ext.Msg.alert(
			'<img class="ico" src="images/info.png" alt="" /> '+ title,
			error_list
		);
	},
	show_connection_error: function() {
		Ext.Msg.alert(
			PC.i18n.dialog.gallery.error.connection.title,
			PC.i18n.dialog.gallery.error.connection.message
		);
	},
	select_category: function(id, file_load_callback) {
		var dialog = this;
		var previous = PC.ux.gallery.SelectedCategory;
		PC.utils.setCookie('admin_selected_category', id);
		PC.ux.gallery.SelectedCategory = id;
		if (this.is_category_trashed()) {
			dialog.files_view.store.setBaseParam('trashed', 1);
		}
		else {
			dialog.files_view.store.setBaseParam('trashed', 0);
		}
		dialog.files_view.store.setBaseParam('category_id', id);
		var params = {};
		if (typeof file_load_callback == 'function') params.callback = file_load_callback;
		dialog.files_view.store.load(params);
		var actions = PC.ux.gallery.files.actions;
		var view = this.files_view;
		if (this.is_category_trashed(id)) {
			if (!this.is_category_trashed(previous) || previous == 0) {
				actions.Get('Upload', view).hide();
				actions.Get('Restore', view).hide();
				actions.Get('Insert', view).hide();
				actions.Get('Edit', view).hide();
				actions.Get('CreateThumbnail', view).hide();
				actions.Get('Rename', view).hide();
				actions.Get('Trash', view).hide();
				actions.Get('Delete', view).hide();
				actions.Get('CopyLink', view).hide();
				actions.Get('Restore', view).show();
				actions.Get('Delete', view).show();
			}
		}
		else {
			if (this.is_category_trashed(previous) || actions.Get('Upload', view).isHidden()) {
				actions.Get('Upload', view).show();
				actions.Get('Restore', view).show();
				actions.Get('Insert', view).show();
				actions.Get('Edit', view).show();
				actions.Get('CreateThumbnail', view).show();
				actions.Get('Rename', view).show();
				actions.Get('Trash', view).show();
				actions.Get('CopyLink', view).show();
				actions.Get('Restore', view).hide();
				actions.Get('Delete', view).hide();
			}
		}
	},
	Select_initial_file: function(){
		var dialog = PC.dialog.gallery;
		if (dialog.params.select_id != undefined) {
			dialog.get_file_data(dialog.params.select_id, function(path){
				var full_path = '/'+dialog.categories.getRootNode().id +'/0';
				if (path.length) full_path += '/'+path;
				dialog.categories.selectPath(full_path, null, function(success, node){
					if (success) dialog.categories.Change(node, function(r, options, success){
						if (success) dialog.files_view.select(dialog.params.select_id);
					});
				});
			});
		}
	},
	get_file_data: function(id, callback){
		Ext.Ajax.request({
			url: 'ajax.gallery.php?action=get_file',
			method: 'POST',
			params: {
				id: id
			},
			success: function(result){
				var json_result = Ext.util.JSON.decode(result.responseText);
				if (json_result.success) {
					if (typeof callback == 'function') {
						callback(json_result.filedata.path_id);
					}
				}
				else {
					PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.file.remove.error_title, json_result.errors);
				}
			},
			failure: function(){
				PC.dialog.gallery.show_connection_error();
			}
		});
	},
	translate_thumbnail_type: function(type) {
		if (type == 'thumbnail' || type == 'small' || type == 'large') {
			return eval('PC.i18n.dialog.gallery.thumbnails.'+ type);
		}
		else return type;
	},
	is_default_thumbnail_type: function(type) {
		if (type == 'thumbnail' || type == 'small' || type == 'large') {
			return true;
		}
		else return false;
	},
	// files
	delete_file: function(file_id, record) {
		var dialog = this;
		var message = PC.i18n.dialog.gallery.file.remove.confirmation.message;
		if (record.data.in_use) {
			message += '<br />'+ PC.i18n.dialog.gallery.file.in_use;
		}
		Ext.Msg.show({
			title: PC.i18n.dialog.gallery.file.remove.confirmation.title,
			msg: message,
			buttons: {
				ok: PC.i18n.dialog.gallery.button.ok,
				cancel: PC.i18n.dialog.gallery.button.cancel
			},
			fn: function(bid) {
				if (bid == 'ok') {
					Ext.Ajax.request({
						url: 'ajax.gallery.php?action=delete_file',
						method: 'POST',
						params: {
							file_id: file_id
						},
						success: function(result){
							var json_result = Ext.util.JSON.decode(result.responseText);
							if (json_result.success) {
								// file deleted. Reload filelist in the category
								dialog.files_view.store.load();
								// reload category sizes in the tree
								//PC.dialog.gallery.categories.getRootNode().reload();
								PC.dialog.gallery.categories.getLoader().load(PC.dialog.gallery.categories.getRootNode(), function(){
									var n = PC.dialog.gallery.categories.getNodeById(PC.ux.gallery.SelectedCategory);
									if (n) n.ensureVisible();
								});
							}
							else {
								PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.file.remove.error_title, json_result.errors);
							}
						},
						failure: function(){
							PC.dialog.gallery.show_connection_error();
						}
					});
				}
			}
		});
	},
	delete_files: function(indexes) {
		var dialog = this;
		//format file_ids string concated from indexes separated by commas (server-side desirable format)
		var file_ids = '';
		if (indexes.length < 1) {
			return false;
		}
		var record;
		var contains_files_in_use = false;
		for (var a=0; indexes[a] != undefined; a++) {
			record = dialog.files_view.store.getAt(indexes[a]);
			file_ids += record.data.id +',';
			if (record.data.in_use) contains_files_in_use = true;
		}
		file_ids = file_ids.slice(0, -1);
		var message = PC.i18n.dialog.gallery.files.remove.confirmation.message;
		if (contains_files_in_use) {
			message += '<br />'+ PC.i18n.dialog.gallery.files.in_use;
		}
		Ext.Msg.show({
			title: PC.i18n.dialog.gallery.files.remove.confirmation.title,
			msg: message,
			buttons: {
				ok: PC.i18n.dialog.gallery.button.ok,
				cancel: PC.i18n.dialog.gallery.button.cancel
			},
			fn: function(bid) {
				if (bid == 'ok') {
					Ext.Ajax.request({
						url: 'ajax.gallery.php?action=delete_files',
						method: 'POST',
						params: {
							file_ids: file_ids
						},
						success: function(result){
							var json_result = Ext.util.JSON.decode(result.responseText);
							if (json_result.success) {
								if (json_result.results != undefined) {
									PC.dialog.gallery.show_request_results(
										PC.i18n.dialog.gallery.files.remove.request_results_title,
										json_result.results,
										false
									);
								}
								//reload filelist in the category
								dialog.files_view.store.load();
								//reload category sizes in the tree
								//PC.dialog.gallery.categories.getRootNode().reload();
								PC.dialog.gallery.categories.getLoader().load(PC.dialog.gallery.categories.getRootNode(), function(){
									var n = PC.dialog.gallery.categories.getNodeById(PC.ux.gallery.SelectedCategory);
									if (n) n.ensureVisible();
								});
							}
							else {
								PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.categories.remove.error_title, json_result.errors, true);
							}
						},
						failure: function(){
							PC.dialog.gallery.show_connection_error();
						}
					});
				}
			}
		});
	},
	trash_file: function(file_id) {
		var dialog = this;
		if (PC.ux.gallery.SelectedCategory == 'bin') return;
		Ext.Msg.show({
			title: PC.i18n.dialog.gallery.trash.file.confirmation.title,
			msg: PC.i18n.dialog.gallery.trash.file.confirmation.message,
			buttons: {
				ok: PC.i18n.dialog.gallery.button.ok,
				cancel: PC.i18n.dialog.gallery.button.cancel
			},
			fn: function(bid) {
				if (bid == 'ok') {
					Ext.Ajax.request({
						url: 'ajax.gallery.php?action=trash_file',
						method: 'POST',
						params: {
							file_id: file_id
						},
						success: function(result){
							var json_result = Ext.util.JSON.decode(result.responseText);
							if (json_result.success) {
								// file trashed. reload filelist in the category
								dialog.files_view.store.load();
							}
							else {
								PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.trash.file.error_title, json_result.errors);
							}
						},
						failure: function(){
							PC.dialog.gallery.show_connection_error();
						}
					});
				}
			}
		});
	},
	trash_files: function(indexes) {
		var dialog = this;
		if (PC.ux.gallery.SelectedCategory == 'bin') return;
		//format file_ids string concatenated from indexes separated by commas (server-side desirable format)
		var file_ids = '';
		if (indexes.length < 1) {
			return false;
		}
		var record;
		for (var a=0; indexes[a] != undefined; a++) {
			record = dialog.files_view.store.getAt(indexes[a]);
			file_ids += record.data.id +',';
		}
		file_ids = file_ids.slice(0, -1);
		Ext.Msg.show({
			title: PC.i18n.dialog.gallery.trash.files.confirmation.title,
			msg: PC.i18n.dialog.gallery.trash.files.confirmation.message,
			buttons: {
				ok: PC.i18n.dialog.gallery.button.ok,
				cancel: PC.i18n.dialog.gallery.button.cancel
			},
			fn: function(bid) {
				if (bid == 'ok') {
					Ext.Ajax.request({
						url: 'ajax.gallery.php?action=trash_files',
						method: 'POST',
						params: {
							file_ids: file_ids
						},
						success: function(result){
							var json_result = Ext.util.JSON.decode(result.responseText);
							if (json_result.success) {
								if (json_result.results != undefined) {
									PC.dialog.gallery.show_request_results(
										PC.i18n.dialog.gallery.trash.files.request_results_title,
										json_result.results,
										false
									);
								}
								//reload filelist in the category.
								dialog.files_view.store.load();
							}
							else {
								PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.trash.files.error_title, json_result.errors);
							}
						},
						failure: function(){
							PC.dialog.gallery.show_connection_error();
						}
					});
				}
			}
		});
	},
	restore_file: function(file_id) {
		var dialog = this;
		Ext.Ajax.request({
			url: 'ajax.gallery.php?action=restore_file',
			method: 'POST',
			params: {
				file_id: file_id
			},
			success: function(result){
				var json_result = Ext.util.JSON.decode(result.responseText);
				if (json_result.success) {
					dialog.files_view.store.load();
				}
				else {
					PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.trash.restore.file.error_title, json_result.errors);
				}
			},
			failure: function(){
				PC.dialog.gallery.show_connection_error();
			}
		});
	},
	// categories
	/*create_category: function(category_id) {},
	delete_album: function(category_id) {},
	trash_album: function(category_id) {},*/
	reload_thumbnail_types_in_menus: function(records) {
		var dialog = this;
		// update 'create thumbnail' action
		PC.ux.gallery.files.actions.Get('CreateThumbnail', dialog.files_view).each(function(obj){
			obj.menu = new Ext.menu.Menu();
			var count = 0;
			var type_spacer_added = false;
			Ext.iterate(records, function(record){
				//console.log(record.data.thumbnail_type);
				if (record.data.thumbnail_type == 'thumbnail') return;
				//insert separator between default and custom thumbnail types
				if (!type_spacer_added) if (!PC.dialog.gallery.is_default_thumbnail_type(record.data.thumbnail_type)) {
					type_spacer_added = true;
					obj.menu.insert(count, {
						xtype:'menuseparator'
					});
					count++;
				}
				//insert thumb type
				obj.menu.insert(count, {
					//icon: (record.data.thumbnail_type=='small')?'images/tick.png':undefined,
					//text: record.data.type + (record.data.thumbnail_type=='small'?'<span style="font-size: 8pt; color: #666"> (default)</span>':'') +'<b>:</b> <span style="font-size: 8pt; color: #666">'+ record.data.thumbnail_max_w +'x'+ record.data.thumbnail_max_h +'</span>',
					text: record.data.type +'<b>:</b> <span style="font-size: 8pt; color: #666">'+ record.data.thumbnail_max_w +'x'+ record.data.thumbnail_max_h +(record.data.thumbnail_type=='small'?' (default)':'')+ '</span>',
					handler: function() {
						var selected_indexes = PC.dialog.gallery.files_view.getSelectedIndexes();
						if (selected_indexes.length < 1) return;
						PC.dialog.gallery.edit_image(selected_indexes[0], record.data.thumbnail_type);
					}
				});
				count++;
			});
		});
		// update 'insert' file action
		PC.ux.gallery.files.actions.Get('Insert', dialog.files_view).each(function(obj){
			Ext.iterate(obj.menu.find('_class', 'thumbnail-type'), function(item){
				obj.menu.remove(item);
			});
			Ext.iterate(obj.menu.find('_class', 'group-separator'), function(item){
				obj.menu.remove(item);
			});
			var total_thumbnail_types = 0;
			//original size photo
			obj.menu.insert(total_thumbnail_types, {
				text: dialog.ln.action.insert.original +'<b>:</b> <span style="font-size: 8pt; color: #666">'+ dialog.ln.action.insert.as_uploaded +'</span>',
				_class: 'thumbnail-type',
				handler: function() {
					PC.dialog.gallery.insert_files('');
				}
			});
			total_thumbnail_types++;
			//thumbnail types
			var type_spacer_added = false;
			Ext.iterate(records, function(record){
				if (record.data.thumbnail_type == 'thumbnail') return;
				//insert separator between default and custom thumbnail types
				if (!type_spacer_added) if (!PC.dialog.gallery.is_default_thumbnail_type(record.data.thumbnail_type)) {
					type_spacer_added = true;
					obj.menu.insert(total_thumbnail_types, {
						xtype:'menuseparator',
						_class: 'group-separator'
					});
					total_thumbnail_types++;
				}
				//insert thumb type
				obj.menu.insert(total_thumbnail_types, {
					//icon: (record.data.thumbnail_type=='small')?'images/tick.png':undefined,
					//text: record.data.type + (record.data.thumbnail_type=='small'?'<span style="font-size: 8pt; color: #666"> (default)</span>':'') +'<b>:</b> <span style="font-size: 8pt; color: #666">'+ record.data.thumbnail_max_w +'x'+ record.data.thumbnail_max_h +'</span>',
					text: record.data.type +'<b>:</b> <span style="font-size: 8pt; color: #666">'+ record.data.thumbnail_max_w +'x'+ record.data.thumbnail_max_h +(record.data.thumbnail_type=='small'?' (default)':'')+ '</span>',
					_class: 'thumbnail-type',
					handler: function() {
						if (record.data.group) {
							PC.dialog.gallery.insert_files(record.data.thumbnail_type +'/');
						}
						else PC.dialog.gallery.insert_files('thumb-'+ record.data.thumbnail_type +'/');
					}
				});
				total_thumbnail_types++;
			});
		});
	},
	edit_image: function(index, thumbnail_type) {
		Ext.ns('PC.dialog.gallery.cropper');
		var dialog = PC.dialog.gallery;
		var cropper = dialog.cropper;
		Ext.Msg.show({
			title: PC.i18n.msg.title.loading,
			msg: PC.i18n.dialog.gallery.cropper_loading,
			width: 200,
			wait: true,
			waitConfig: {interval:300}
		});
		var record = dialog.files_view.store.getAt(index);
		// filetype must be image
		if (record.data.filetype != 'image') {
			Ext.Msg.hide(); return;
		}
		var thumbnail_type_index = PC.dialog.gallery.thumbnail_types_store.findExact('thumbnail_type', thumbnail_type);
		if (thumbnail_type_index == -1) {
			// thumbnail type was not found in the store
			Ext.Msg.hide(); return;
		}
		var thumbnail_type_record = PC.dialog.gallery.thumbnail_types_store.getAt(thumbnail_type_index);
		var image_id = record.data.id;
		var image_url = PC.global.BASE_URL +'gallery/admin/cropper/'+ record.data.path + record.data.name;
		var image = new Image();
		image.onerror = function(){
			Ext.Msg.hide();
		}
		image.onload = function() {
			Ext.Ajax.request({
				url: 'ajax.gallery.php?action=get_resize_ratio_for_cropping_image',
				method: 'POST',
				params: {
					image_path: record.data.path,
					image_name: record.data.name,
					thumbnail_type: thumbnail_type
				},
				success: function(result){
					var json_result = Ext.util.JSON.decode(result.responseText);
					if (!json_result) {
						Ext.Msg.hide();
						PC.dialog.gallery.show_connection_error();
						return;
					}
					if (json_result.success) {
						cropper.preserve_ratio = true;
						//calculate ratios
						cropper.cropping_image_ratio = json_result.ratio;
						cropper.original_size = json_result.original_size;
						cropper.crop_data = new Array;
						if (json_result.crop_data != undefined) {
							cropper.crop_data = json_result.crop_data;
							cropper.preserve_ratio = false;
						}
						//create window
						var window_ui = Ext.extend(Ext.Window, {
							resizable: false,
							title: PC.i18n.dialog.gallery.title.image_cropper +' - <span style="color: #101010">'+ thumbnail_type_record.data.type +'</span> - '+ record.data.name,
							width: 600,
							height: 500,
							modal: true,
							initComponent: function() {
								window_ui.superclass.initComponent.call(this);
							}
						});
						//pre-invert
						cropper.preserve_ratio = !cropper.preserve_ratio;
						cropper.toggle_preserve_ratio = function(crop_data) {
							//console.log(cropper);
							cropper.preserve_ratio = !cropper.preserve_ratio;
							var imgLoad = image;
							imgLoad.onload = (function(){
								//cropper.window.setSize(Math.max(imgLoad.width, thumbnailType.width) + 15, imgLoad.height + 60);
								//cropper.window.doLayout();
								cropper.window.setSize(Math.max(imgLoad.width + 15, 300), Math.max(imgLoad.height + 60, 200));
								if (typeof cropper.crop == 'object') cropper.crop.destroy();
								if (cropper.cropping_image_ratio < 1) {
									var min_thumb_width = thumbnail_type_record.data.thumbnail_max_w * cropper.cropping_image_ratio;
									var min_thumb_height = thumbnail_type_record.data.thumbnail_max_h * cropper.cropping_image_ratio;
								}
								else {
									var min_thumb_width = thumbnail_type_record.data.thumbnail_max_w;
									var min_thumb_height = thumbnail_type_record.data.thumbnail_max_h;
								}
								// prevent cropper resizable area from sticking out of the image boundaries
								min_thumb_width = (min_thumb_width > image.width)? image.width : (parseInt(min_thumb_width));
								min_thumb_height = (min_thumb_height > image.height)? image.height : (parseInt(min_thumb_height));
								
								cropper.crop = new Ext.ux.ImageCrop({
									imageUrl: cropper.window.imageUrl,
									initialWidth: imgLoad.width,
									initialHeight: imgLoad.height,
									minWidth: min_thumb_width,
									minHeight: min_thumb_height,
									//custom
									originalImageSize: cropper.original_size,
									crop_data: Ext.ux.util.clone(crop_data),
									// ratio of the image resized for the cropping, needed to calculate the exact cropped size
									originalImageSize: cropper.original_size,
									croppingImageRatio: cropper.cropping_image_ratio,
									thumbnailDimensions: {
										width: thumbnail_type_record.data.thumbnail_max_w,
										height: thumbnail_type_record.data.thumbnail_max_h
									},
									preserveRatio: cropper.preserve_ratio,
									thumbnailType: thumbnail_type_record.data,
									listeners: {
										cropAreaDoubleClick: function() {
											cropper.save_crop_area();
										}
									}
								});
								cropper.window.add(cropper.crop);
								cropper.window.doLayout();
							}).createDelegate(cropper.window);
							imgLoad.src = image.src;
						}
						var cropperWindow = Ext.extend(window_ui, {
						  cropData: null,
						  imageUrl: '',
						  initComponent: function() {
							cropperWindow.superclass.initComponent.call(this);
							setTimeout(function(){
								cropper.toggle_preserve_ratio(cropper.crop_data);
							},1);
						  }
						});
						cropper.window = new cropperWindow({
							imageUrl: image.src,
							bbar: {
								items: [
									{	text: 'Save', icon: 'images/disk.png',
										handler: function() {
											cropper.save_crop_area();
										}
									},
									{	text: 'Cancel', icon: 'images/delete.png',
										handler: function() {
											cropper.window.close();
										}
									},
									{xtype:'tbfill'},
									{	boxLabel: dialog.ln.proportions, xtype: 'checkbox',
										checked: !cropper.preserve_ratio,
										handler: function() {
											cropper.toggle_preserve_ratio(cropper.crop_data);
										}
									}
								]
							},
							listeners: {
								save: function(){
								  // handler if a crop was successfull, and the window was closed
								},
								scope: this
							}
						});
						dialog.window.pc_temp_window_children['cropper'] = cropper.window;
						cropper.window.show();
						//dialog.show_window(cropper.window);
						Ext.Msg.hide();
						cropper.save_crop_area = function(){
							var crop_data = cropper.crop.getCropData();
							//alert('Width: '+ crop_data.width +'\nHeight: '+ crop_data.height +'\nX start: '+ crop_data.x +'\nY start: '+ crop_data.y +'\n');
							start_x = crop_data.x;
							start_y = crop_data.y;
							width = crop_data.width;
							height = crop_data.height;
							Ext.Ajax.request({
								url: 'ajax.gallery.php?action=crop_image',
								method: 'POST',
								params: {
									image_id: image_id,
									thumbnail_type: thumbnail_type,
									start_x: start_x,
									start_y: start_y,
									width: width,
									height: height
								},
								success: function(result){
									var json_result = Ext.util.JSON.decode(result.responseText);
									if (json_result.success) {
										cropper.window.close();
										//force thumbnail to refresh in the view
										if (thumbnail_type == 'small') {
											record.data.replace_name = record.data.name +'?'+ Math.floor(Math.random()*1000);
											PC.dialog.gallery.files_view.refresh();
										}
									}
									else {
										PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.image.edit.error_title, json_result.errors);
									}
								},
								failure: function(){
									PC.dialog.gallery.show_connection_error();
								}
							});
						}
					}
					else {
						Ext.Msg.hide();
					}
				},
				failure: function(){
					Ext.Msg.hide();
					PC.dialog.gallery.show_connection_error();
				}
			});
		}
		image.src = image_url;
	},
	is_category_trashed: function(id) {
		if (this.categories == undefined) return false;
		if (!id) id = PC.ux.gallery.SelectedCategory;
		if (id == 'bin') return true;
		var current_node = this.categories.getNodeById(id);
		if (!current_node) return false;
		if (current_node.attributes.trashed) return true;
		return false;
	},
	get_large_url: function(url) {
		if (!url) return false;
		var end = url.lastIndexOf('/');
		var start = url.substring(0, end).lastIndexOf('/')+1;
		var type = url.substring(start, end);
		if (/^(thumbnail|small|large|thumb-.*)$/.test(type)) {
			url = url.substring(0, start) +'large'+ url.substr(end);
		}
		else url = url.substring(0, end+1) + 'large'+ url.substr(end);
		return url;
	},
	show_window: function(window, options) {
		var dialog = PC.dialog.gallery;
		dialog.child_window_active = true;
		setTimeout(function(){
			alert(dialog.child_window_active);
			window.show(options);
		}, 100);
		if (window != Ext.Msg) {
			window.addListener('hide', function(panel){
				dialog.child_window_active = false;
			});
			window.addListener('hide', function(panel){
				dialog.child_window_active = false;
			});
		}
	}
};

Ext.DataView.DragSelector = function(cfg){
    cfg = cfg || {};
    var view, proxy, tracker;
    var rs, bodyRegion, dragRegion = new Ext.lib.Region(0,0,0,0);
    var dragSafe = cfg.dragSafe === true;

    this.init = function(dataView){
        view = dataView;
        view.on('render', onRender);
    };

    function fillRegions(){
        rs = [];
        view.all.each(function(el){
            rs[rs.length] = el.getRegion();
        });
        bodyRegion = view.el.getRegion();
    }

    function cancelClick(){
        return false;
    }

    function onBeforeStart(e){
        return !dragSafe || e.target == view.el.dom;
    }

    function onStart(e){
        view.on('containerclick', cancelClick, view, {single:true});
        if(!proxy){
            proxy = view.el.createChild({cls:'x-view-selector'});
        }else{
            if(proxy.dom.parentNode !== view.el.dom){
                view.el.dom.appendChild(proxy.dom);
            }
            proxy.setDisplayed('block');
        }
        fillRegions();
        view.clearSelections();
    }

    function onDrag(e){
        var startXY = tracker.startXY;
        var xy = tracker.getXY();

        var x = Math.min(startXY[0], xy[0]);
        var y = Math.min(startXY[1], xy[1]);
        var w = Math.abs(startXY[0] - xy[0]);
        var h = Math.abs(startXY[1] - xy[1]);

        dragRegion.left = x;
        dragRegion.top = y;
        dragRegion.right = x+w;
        dragRegion.bottom = y+h;

        dragRegion.constrainTo(bodyRegion);
        proxy.setRegion(dragRegion);

        for(var i = 0, len = rs.length; i < len; i++){
            var r = rs[i], sel = dragRegion.intersect(r);
            if(sel && !r.selected){
                r.selected = true;
                view.select(i, true);
            }else if(!sel && r.selected){
                r.selected = false;
                view.deselect(i);
            }
        }
    }

    function onEnd(e){
        if (!Ext.isIE) {
            view.un('containerclick', cancelClick, view);    
        }        
        if(proxy){
            proxy.setDisplayed(false);
        }
    }

    function onRender(view){
        tracker = new Ext.dd.DragTracker({
            onBeforeStart: onBeforeStart,
            onStart: onStart,
            onDrag: onDrag,
            onEnd: onEnd
        });
        tracker.initEl(view.el);
    }
};
