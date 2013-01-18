Ext.ns('PC.tree');
PC.tree.Empty_trash = function(cb) {
	Ext.Ajax.request({
		url: 'ajax.page.php?action=empty_trash',
		params: {'site_id':PC.global.site},
		method: 'POST',
		callback: function(opts, success, rspns) {
			if (success && rspns.responseText) {
				try {
					var data = Ext.decode(rspns.responseText);
					if (data.success) {
						cb();
						return; // OK
					}
				} catch(e) {};
			}
			Ext.MessageBox.show({
				title: PC.i18n.error,
				msg: PC.i18n.msg.error.trash.empty,
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
		}
	});
}
PC.tree.UpdateNodes = function() {
	var tree = PC.tree.component;
	var root = tree.getRootNode();
	//add new page button
	var childs = root.childNodes.length;
	var create = tree.getNodeById('create');

	var search_pages = false;
	if (typeof this.loader == 'object') {
		if (this.loader.baseParams.searchString != undefined) {
			if (this.loader.baseParams.searchString.length > 0) {
				search_pages = true;
			}
		}
	}
	
	if (create) {
		if (childs <= 2) {
			if (!search_pages) {
				create.ui.show();
			}
			
		}
		else {
			create.ui.hide();
		}
	}
};
PC.tree.IsNodeDeleted = function(n) {
	if (n) do {
		n = n.parentNode;
		if (n && n.id == -1) return true;
	} while (n);
	return false;
}
PC.tree.Append = function(parentNode, newNodeData, callback) {
	var append = function() {
		var newNode = PC.tree.component.getNodeById(newNodeData.id);
		if (newNode == undefined) {
			var n = parentNode.lastChild;
			if (n && n.id<0) {
				while (n.previousSibling && n.previousSibling.id<0)
					n = n.previousSibling;
				var newNode = parentNode.insertBefore(newNodeData, n);
				if (typeof callback == 'function') callback(newNode);
				return newNode;
			};
			var newNode = parentNode.appendChild(newNodeData);
			PC.tree.component.localizeNode(newNode);
		}
		if (typeof callback == 'function') callback(newNode);
		return newNode;
	}
	if (parentNode.expanded) return append();
	return parentNode.expand(false, true, append);
}
PC.tree.actions = {
	Preview: new Ext.Action({
		text: PC.i18n.menu.preview,
		icon: 'images/eye.png',
		handler: function() {
			var n = PC.tree.menus.current_node;
			Preview_page(n);
		}
	}),
	ShortcutTo: new Ext.Action({
		text: PC.i18n.menu.shortcut_to,
		icon: 'images/link.png',
		handler: function() {
			var node = PC.tree.menus.current_node;
			if (PC.global.pid == node.id) {
				PC.admin._editor_ln_select.setActiveTab('db_tab_properties');
				var field = Ext.getCmp('db_fld_redirect');
				Show_redirect_page_window(function(value){
					field.setValue(value);
				}, {select_node_path:field.getValue()});
				return;
			}
			save_prompt(function() {
				PC.global.pid = node.id;
				Load_page();
				PC.tree.component.getSelectionModel().select(node);
				PC.admin._editor_ln_select.enable();
				PC.admin._editor_ln_select.setActiveTab('db_tab_properties');
				var field = Ext.getCmp('db_fld_redirect');
				Show_redirect_page_window(function(value){
					field.setValue(value);
				}, {select_node_path: field.getValue()});
			});
		}
	}),
	CreatePage: new Ext.Action({
		text: PC.i18n.menu.new_page,
		icon: 'images/folder_add.png',
		handler: PC.pages.Create
	}),
	CreateSubpage: new Ext.Action({
		text: PC.i18n.menu.new_subpage,
		icon: 'images/folder_add.png',
		handler: function() {
			Ext.Ajax.request({
				url: 'ajax.pagetree.php',
				params: {
					'new': PC.tree.menus.current_node.id,
					'site': PC.global.site
				},
				method: 'POST',
				callback: function(opts, success, rspns) {
					if (success && rspns.responseText) {
						try {
							var data = Ext.decode(rspns.responseText);
							var n = PC.tree.component.getNodeById(opts.params['new']);
							n.expand();
							var nn = PC.tree.Append(n, data);
							if (nn) {
								nn.loaded = true;
								nn.expand();
								node_rename_menu(nn, true);
							}
							return; // OK
						} catch(e) {};
					}
					Ext.MessageBox.show({
						title: PC.i18n.error,
						msg: PC.i18n.msg.error.page.create,
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.ERROR
					});
				}
			});
		}
	}),
	Rename: new Ext.Action({
		text: PC.i18n.menu.rename,
		icon: 'images/edit.gif',
		handler: function() {
			node_rename_menu(PC.tree.menus.current_node);
		}
	}),
	Delete: new Ext.Action({
		text: PC.i18n.del,
		icon: 'images/delete.gif',
		handler: function() {
			var n = PC.tree.menus.current_node;
			// delete permanently
			if (PC.tree.IsNodeDeleted(n)) {
				Ext.MessageBox.show({
					title: PC.i18n.msg.title.confirm,
					msg: String.format(PC.i18n.msg.perm_del, '"'+n.text+'"'),
					buttons: Ext.MessageBox.YESNO,
					icon: Ext.MessageBox.WARNING,
					fn: function(rslt) {
						switch (rslt) {
							case 'yes':
								Ext.Ajax.request({
									url: 'ajax.pagetree.php',
									params: {
										del: n.id
									},
									method: 'POST',
									callback: function(opts, success, rspns) {
										if (success && rspns.responseText) {
											try {
												var data = Ext.decode(rspns.responseText);
												//reload file list in the gallery (update 'files in use' marking)
												if (PC.dialog.gallery.window) {
													PC.dialog.gallery.files_store.load();
												}
												if (data.totrash) {
													var trash = PC.tree.component.getNodeById(-1);
													if (trash) {
														if (!trash.childNodes.length)
															trash.collapse();
														trash.insertBefore(n, trash.firstChild);
														return; // OK
													}
												}
												n.remove();
												return; // OK
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
				return;
			}
			if (n.attributes.redirects_from > 0) {
				Ext.Msg.show({
					title: PC.i18n.attention,
					msg: PC.i18n.delete_page_that_has_shortcuts,
					buttons: Ext.MessageBox.YESNO,
					icon: Ext.MessageBox.WARNING,
					fn: function(r) {
						if (r == 'yes') PC.trash_page(n);
					}
				});
			}
			else PC.trash_page(n);
		}
	}),
	Properties: new Ext.Action({
		text: PC.i18n.tab.properties,
		icon: 'images/Compile.png',
		handler: function(menu_node, event) {
			var node = PC.tree.menus.current_node;
			PC.tree.component.getSelectionModel().select(node);
			PC.tree.component.fireEvent('click', node);
			PC.admin._editor_ln_select.setActiveTab('db_tab_properties');
		}
	}),
	EmptyBin: new Ext.Action({
		text: PC.i18n.pages.empty_bin,
		icon: 'images/trashe.png',
		handler: function(menu_node, event) {
			Ext.MessageBox.show({
				title: PC.i18n.msg.title.confirm,
				msg: PC.i18n.msg.empty_trash,
				buttons: Ext.MessageBox.YESNO,
				icon: Ext.MessageBox.WARNING,
				fn: function(clicked) {
					if (clicked == 'yes') {
						PC.tree.Empty_trash(function(){
							PC.tree.component.getNodeById(-1).reload();
						});
					}
				}
			});
		}
	})
}
PC.tree.menus = {
	home: new Ext.menu.Menu({
		id: 'pc_tree_menu_home',
		items: [
			PC.tree.actions.Preview,
			PC.tree.actions.ShortcutTo,
			'-',
			PC.tree.actions.CreatePage,
			'-',
			PC.tree.actions.Rename,
			PC.tree.actions.Properties
		]
	}),
	pages: new Ext.menu.Menu({
		id: 'pc_tree_menu_pages',
		items: [
			PC.tree.actions.Preview,
			PC.tree.actions.ShortcutTo,
			'-',
			PC.tree.actions.CreatePage,
			PC.tree.actions.CreateSubpage,
			'-',
			PC.tree.actions.Rename,
			PC.tree.actions.Delete,
			PC.tree.actions.Properties
		]
	}),
	bin: new Ext.menu.Menu({
		id: 'pc_tree_menu_bin',
		items: [
			PC.tree.actions.EmptyBin
		]
	})
}
PC.tree.component = new PC.ux.PageTree({
	enableDD: true,
	hlDrop: false,
	ddGroup: 'tree_pages',
	dropConfig: {
		ddGroup: 'tree_pages',
		expandDelay: 1000
	},
	additionalBaseParams: {
		load_children: true
	},
	//dragConfig: {},
	selModel: new Ext.tree.DefaultSelectionModel({
		listeners: {
			selectionchange: function(selModel, n){
				var tree = PC.tree.component;
				if (n != undefined) {
					PC.tree.node = n;
					if (tree.loader.baseParams.searchString != undefined) {
						tree.search_last_page_clicked = n.id;
					}
				}
			}
		}
	}),
	bbar: {
		items: [
			{	xtype: 'textfield',
				ref: '../_search',
				name: 'search_pages',
				emptyText: PC.i18n.search_pages,
				style: 'font-style:italic;',
				width: 252,
				listeners: {
					specialkey: function(field, e) {
						if (e.getKey() == e.ENTER) {
							var tree = PC.tree.component;
							var newSearchString = field.getValue();
							if (newSearchString != tree.loader.baseParams.searchString) {
								if (newSearchString.length > 0) {
									field.getEl().frame("999966", 1, { duration: 0.5});
									if (tree.loader.baseParams.searchString == undefined) {
										var selNode = tree.selModel.getSelectedNode();
										if (selNode != undefined) {
											if (!selNode.ownerTree) {
												selNode.ownerTree = PC.tree.component;
											}
											tree.last_path = selNode.getPath();
										}
									}
									tree.loader.baseParams.searchString = newSearchString;
									tree.loader.load(tree.getRootNode());
								}
								else {
									tree.loader.baseParams.searchString = '';
									tree.loader.load(tree.getRootNode(), tree.restore_last_path);
								}
							}
						}
					}
				}
			},
			{	icon: 'images/zoom.png',
				handler: function(b) {
					field = PC.tree.component._search;
					var e = {
						getKey: function() {
							return Ext.EventManager.ENTER;
						}
					};
					field.fireEvent('specialkey', field, e);
				}
			},
			{	icon:'images/zoom_out.png',
				handler: function() {
					var tree = PC.tree.component;
					tree.loader.baseParams.searchString = '';
					tree.fireEvent('beforeload', tree.getRootNode());
					tree.loader.load(tree.getRootNode(), tree.restore_last_path);
					tree._search.setValue('');
				}
			}
		]
	},
	restore_last_path: function(){
		var tree = PC.tree.component;
		if (tree.search_last_page_clicked != undefined) {
			Get_page_path(tree.search_last_page_clicked, function(path){
				tree.selectPath(path+'/'+tree.search_last_page_clicked);
			});
		}
		else if (tree.last_path != undefined) tree.selectPath(tree.last_path);
		//tree.last_path = undefined;
		//tree.search_last_page_clicked = undefined;
	},
	allowDD: function(){
		var tree = PC.tree.component;
		var search = tree.loader.baseParams.searchString;
		if (search == undefined || search == null) return true;
		if (!search.length) return true;
		return false;
	},
	listeners: {
		beforeclick: function(n, e) {
			/* Module tree rendering
			arba reik ieskot kito evento onexpand :?
			if (n.controller != undefined) {
				if node not expanded then do this:
				n.appendChild([
					{iconCls: 'images/brick.png'}
				]); return;
			}
			*/
			if (n.attributes.id == 'create') {
				PC.pages.Create(true);
			}
			else if (n.attributes._nosel) return false;
		},
		click: function(n, e) {
			//PC.global.pid
			PC.admin._editor_ln_select.enable();
			if (PC.global.pid == n.id) return;
			return save_prompt(function() {
				PC.global.selected_node = n;
				PC.global.pid = n.id;
				PC.tree.component.selModel.select(n);
				n.expand();
				Load_page();
				Reload_page_controller_list(n);
			});
		},
		contextmenu: function(n, e) {
			if (n.disabled) {
				return false;
			}
			PC.tree.menus.current_node = n;
			var attr = n.attributes;
			//bin
			if (n.id == '-1') {
				PC.tree.menus.bin.showAt(e.getXY());
				return true;
			}
			//page menu
			if (!n.attributes._nosel) {
				if (n.attributes._front) {
					PC.tree.actions.CreateSubpage.disable();
					PC.tree.actions.Delete.disable();
					PC.tree.actions.Rename.disable();
				}
				else {
					PC.tree.actions.CreateSubpage.enable();
					PC.tree.actions.Delete.enable();
					PC.tree.actions.Rename.enable();
				}
				//custom menu by controller
				if (attr.controller != undefined) {
					var CustomMenuController = attr.controller;
				}
				else if (/^[a-z0-9\-_]+\//.test(attr.id)) {
					var CustomMenuController = attr.id.substring(0, attr.id.indexOf('/'));
				}
				if (CustomMenuController != undefined) {
					var hook = 'core/tree/menu/'+ CustomMenuController;
					if (PC.hooks.Count(hook)) {
						var params = {
							node: n,
							event: e,
							menu: null,
							showMenu: true
						};
						PC.hooks.Init(hook, params);
						if (params.showMenu === true) {
							if (params.menu == undefined || typeof params.menu.showAt != 'function') params.menu = PC.tree.menus.pages;
							params.menu.showAt(params.event.getXY());
						}
						return true;
					}
				}
				PC.tree.menus.pages.showAt(e.getXY());
				return true;
			}
		},
		//startdrag: function(tree, node, ev) {},
		nodedragover: function(ev) {
			var plugin = PC.getPluginFromID(ev.dropNode.id);
			var newParent = ev.point=='append' ? ev.target : ev.target.parentNode;
			// DENY into disabled
			if (newParent.disabled)
				return false;
			if (plugin) {
				if (PC.hooks.Count('core/tree/nodedragover/'+ plugin)) {
					ev.cancel = true;
					var params = {
						event: ev,
						newParent: newParent
					};
					PC.hooks.Init('core/tree/nodedragover/'+ plugin, params);
					return;
				}
			}
			// DENY below recycle bin
			if (ev.point=='below' && ev.target.id==-1)
				return false;
			// DENY into deleted
			if (PC.tree.IsNodeDeleted(newParent))
				return false;
			// DENY deleted into recycle bin
			if (PC.tree.IsNodeDeleted(ev.dropNode) && newParent.id==-1)
				return false;
			// DENY moving menu inside other leafs
			if (ev.dropNode.attributes.controller == 'menu') {
				if (ev.point == 'append')
					return false;
				if (ev.target.getDepth() > 1)
					return false;
			}
			if (!PC.tree.component.allowDD()) return false;
		},
		beforenodedrop: function(ev) {
			if (ev.dropNode.attributes.controller == 'menu') {
				var r = confirm(PC.i18n.msg.move_menu_warning);
				if (!r) return false;
			}
			ev.dropNode._oldparent = ev.dropNode.parentNode;
			ev.dropNode._oldnsib = ev.dropNode.nextSibling;
			ev.dropNode.draggable = false;
			
			var plugin = PC.getPluginFromID(ev.dropNode.id);
			if (plugin) {
				var newParent = ev.point=='append' ? ev.target : ev.target.parentNode;
				if (PC.hooks.Count('core/tree/beforenodedrop/'+ plugin)) {
					var params = {
						event: ev,
						newParent: newParent
					};
					PC.hooks.Init('core/tree/beforenodedrop/'+ plugin, params);
				}
			}
		},
		nodedrop: function(ev) {
			var newParent = ev.point=='append' ? ev.target : ev.target.parentNode;
			
			var plugin = PC.getPluginFromID(ev.dropNode.id);
			/* PLUGIN NODE MOVE */
			if (plugin) {
				ev.cancel = true;
				if (PC.hooks.Count('core/tree/nodedrop/'+ plugin)) {
					var params = {
						event: ev,
						newParent: newParent,
						moveBack: function() {
							ev.dropNode._oldparent.insertBefore(ev.dropNode, ev.dropNode._oldnsib);
						}
					};
					PC.hooks.Init('core/tree/nodedrop/'+ plugin, params);
				}
				ev.dropNode.draggable = true;
				return;
			}
			/* MOVE TO RECYCLE BIN */
			else if (newParent.id == -1) {
				if (newParent.firstChild != ev.dropNode)
					newParent.insertBefore(ev.dropNode, newParent.firstChild);
				Ext.Ajax.request({
					url: 'ajax.page.php?action=delete',
					params: {
						site: PC.global.site,
						id: ev.dropNode.id,
						old_idp: ev.dropNode.parentNode.id
					},
					method: 'POST',
					callback: function(opts, success, rspns) {
						ev.dropNode.draggable = true;
						if (success && rspns.responseText) {
							try {
								var data = Ext.decode(rspns.responseText);
								if (data.success) return; // OK
							} catch(e) {};
						}
						// move node back
						ev.dropNode._oldparent.insertBefore(ev.dropNode, ev.dropNode._oldnsib);
						Ext.MessageBox.show({
							title: PC.i18n.error,
							msg: String.format(PC.i18n.msg.error.page.del, '"'+ev.dropNode.text+'"'),
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				});
				return;
			}
			/* NORMAL MOVE */
			else {
				Ext.Ajax.request({
					url: 'ajax.page.php?action=move',
					params: {
						id: ev.dropNode.id,
						idp: newParent.id,
						old_idp: ev.dropNode._oldparent.id,
						'new_order[]': Ext.pluck(Ext.partition(newParent.childNodes, function(n){ return n.id>0; })[0], 'id')
					},
					method: 'POST',
					callback: function(opts, success, rspns) {
						ev.dropNode.draggable = true;
						if (success && rspns.responseText) {
							try {
								var data = Ext.decode(rspns.responseText);
								if (data.success) {
									PC.hooks.Init('tree.drop', {dropEvent: ev});
									return; // OK
								}
							} catch(e) {};
						}
						//move node back
						ev.dropNode._oldparent.insertBefore(ev.dropNode, ev.dropNode._oldnsib);
						Ext.MessageBox.show({
							title: PC.i18n.error,
							msg: String.format(PC.i18n.msg.error.page.move, '"'+ev.dropNode.text+'"'),
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				});
			}
		},
		enddrag: function(tp, n, e) {
			tp.getSelectionModel().select(tp.getNodeById(PC.global.pid));
		},
		containercontextmenu: function(tree,e) {
			var defaults = {
				show_menu: true
			};
			PC.hooks.Init('tree.containercontextmenu', {
				tree: PC.tree.component,
				event: e,
				defaults: defaults
			}, function(count){
				if (defaults.show_menu) {
					var menu = new Ext.menu.Menu({
						items: [PC.tree.actions.CreatePage]
					});
					menu.showAt(e.getXY());
				}
			});
		},
		insert: PC.tree.UpdateNodes,
		remove: PC.tree.UpdateNodes,
		load: function(node) {
			if (node.getDepth() == 0) { //node=root
				Check_preview_action_availability();
				PC.tree.UpdateNodes();
			}
			PC.tree.component.localizeNode(node);
		},
		append: function(tree, parent, node, index){
			PC.tree.component.localizeNode(node);
		}
	}
});