Ext.ns('PC.ux.gallery');

Ext.ns('PC.ux.gallery.files');

PC.ux.gallery.files.Template = function(config) {
	Ext.ns('PC.dialog.gallery');
	if (PC.dialog.gallery.params == undefined) PC.dialog.gallery.params = {};
	var tpl = '<tpl if="this.reset()"></tpl>'
		// search header
		+'<tpl if="this.store.filter != \'\'">'
			+'<div class="gallery_search">Searching for "{[this.store.filter]}"</div>'
		+'</tpl>'
		+'<tpl for=".">'
			// group search results by categories
			+'<tpl if="this.store.filter != \'\' && this.next_category(path)">'
				+'<tpl if="path != \'\'">'
					+'<div class="gallery-category">'
						+'{category}'
					+'</div>'
				+'</tpl>'
			+'</tpl>'
			// template: Icons
			+'<tpl if="PC.ux.gallery.FilesCurrentTemplate  == \'icons\' && !(PC.dialog.gallery.params.callee==\'image\' && filetype!=\'image\')">'
				// item selector
				+'<div class="thumb-wrap template-icons" id="{id}">'
					// mark unused files
					+'<tpl if="!in_use && PC.ux.gallery.MarkUnusedFiles"><div class="gallery_unused_file"></tpl>'
					// template for images
					+'<tpl if="filetype==\'image\'">'
						+'<div class="thumb">'
							//format image
							+'<img class="drag-img" src="../gallery/'
								+'<tpl if="PC.dialog.gallery.is_category_trashed()>admin/</tpl>'
								+'{path}thumbnail/'
								+'<tpl if="replace_name.length">{replace_name}</tpl>'
								+'<tpl if="!replace_name.length">{name}</tpl>'
							+'" alt="" title="{name}" />'
						+'</div>'
					+'</tpl>'
					// template for other files
					+'<tpl if="filetype!=\'image\'">'
						+'<div class="file">'
							+'<tpl if="filetype==\'document\'">'
								+'<tpl if="extension==\'pdf\'">'
									+'<img class="drag-img" src="images/filetypes/File-Pdf-48.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'doc\' || extension==\'docx\'">'
									+'<img class="drag-img" src="images/filetypes/Word-48.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'xls\' || extension==\'xlsx\'">'
									+'<img class="drag-img" src="images/filetypes/File-Excel-48.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'ppt\' || extension==\'pptx\'">'
									+'<img class="drag-img" src="images/filetypes/File-PowerPoint-48.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'txt\' || extension==\'cdr\'">'
									+'<img class="drag-img" src="images/filetypes/File-48.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'swf\'">'
									+'<img class="drag-img" src="images/filetypes/File_swf-48.png" alt="" title="{name}" />'
								+'</tpl>'
							+'</tpl>'
							+'<tpl if="filetype==\'audio\'">'
								+'<img class="drag-img" src="images/filetypes/Audio-48.png" alt="" title="{name}" />'
							+'</tpl>'
							+'<tpl if="filetype==\'video\'">'
								+'<img class="drag-img" src="images/filetypes/Video-48.png" alt="" title="{name}" />'
							+'</tpl>'
							+'<tpl if="filetype==\'archive\'">'
								+'<img class="drag-img" src="images/filetypes/Zip-48.png" alt="" title="{name}" />'
							+'</tpl>'
							+'<tpl if="filetype==\'executable\'">'
								+'<img class="drag-img" src="images/filetypes/Exec-48.png" alt="" title="{name}" />'
							+'</tpl>'
						+'</div>'
					+'</tpl>'
					// shortname of the filename
					+'<span class="gallery-file-title">{short_name}</span>'
					// end marking of unused files
					+'<tpl if="!in_use && PC.ux.gallery.MarkUnusedFiles"></div></tpl>'
				+'</div>'
			+'</tpl>'
			// template: Detailed
			+'<tpl if="PC.ux.gallery.FilesCurrentTemplate  == \'detailed\' && !(PC.dialog.gallery.params.callee==\'image\' && filetype!=\'image\')">'
				// item selector
				+'<div class="thumb-wrap template-detailed" id="{id}">'
					// mark unused files
					+'<tpl if="!in_use && PC.ux.gallery.MarkUnusedFiles"><div class="gallery_unused_file"></tpl>'
					// template for images
					+'<tpl if="filetype==\'image\'">'
						+'<div class="thumb">'
							//format image
							+'<img class="drag-img" src="../gallery/'
								+'<tpl if="PC.dialog.gallery.is_category_trashed()>admin/</tpl>'
								+'{path}thumbnail/'
								+'<tpl if="replace_name.length">{replace_name}</tpl>'
								+'<tpl if="!replace_name.length">{name}</tpl>'
							+'" alt="" title="{name}" />'
							//'<img class="drag-img" src="../gallery/{path}thumbnail/{name}" alt="" title="{name}" />',
						+'</div>'
					+'</tpl>'
					// template for other files
					+'<tpl if="filetype!=\'image\'">'
						+'<div class="file">'
							+'<tpl if="filetype==\'document\'">'
								+'<tpl if="extension==\'pdf\'">'
									+'<img class="drag-img" src="images/filetypes/File-Pdf-32.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'doc\' || extension==\'docx\'">'
									+'<img class="drag-img" src="images/filetypes/Word-32.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'xls\' || extension==\'xlsx\'">'
									+'<img class="drag-img" src="images/filetypes/File-Excel-32.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'ppt\' || extension==\'pptx\'">'
									+'<img class="drag-img" src="images/filetypes/File-PowerPoint-32.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'txt\' || extension==\'cdr\'"">'
									+'<img class="drag-img" src="images/filetypes/File-32.png" alt="" title="{name}" />'
								+'</tpl>'
								+'<tpl if="extension==\'swf\'">'
									+'<img class="drag-img" src="images/filetypes/File_swf-32.png" alt="" title="{name}" />'
								+'</tpl>'
							+'</tpl>'
							+'<tpl if="filetype==\'audio\'">'
								+'<img class="drag-img" src="images/filetypes/Audio-32.png" alt="" title="{name}" />'
							+'</tpl>'
							+'<tpl if="filetype==\'video\'">'
								+'<img class="drag-img" src="images/filetypes/Video-32.png" alt="" title="{name}" />'
							+'</tpl>'
							+'<tpl if="filetype==\'archive\'">'
								+'<img class="drag-img" src="images/filetypes/Zip-32.png" alt="" title="{name}" />'
							+'</tpl>'
							+'<tpl if="filetype==\'executable\'">'
								+'<img class="drag-img" src="images/filetypes/Exec-32.png" alt="" title="{name}" />'
							+'</tpl>'
						+'</div>'
					+'</tpl>'
					// shortname of the filename
					+'<span class="gallery-file-title">{name}</span>'
					+'<span class="filedata">{size}<br />{modified}</span>'
					// end marking of unused files
					+'<tpl if="!in_use && PC.ux.gallery.MarkUnusedFiles"></div></tpl>'
				+'</div>'
			+'</tpl>'
		+'</tpl>';
	//call parent constructor
	PC.ux.gallery.files.Template.superclass.constructor.call(this, tpl, config);
};

Ext.extend(PC.ux.gallery.files.Template, Ext.XTemplate, {
	compiled: true,
	reset: function() {
		this.current_path = undefined;
	},
	next_category: function(path) {
		if (path != this.current_path) {
			this.current_path = path;
			return true;
		}
		return false;
	}
});

Ext.ComponentMgr.registerType('pc_gallery_files_template', PC.ux.gallery.files.Template);

PC.ux.gallery.FilesCurrentTemplate = 'icons';
PC.ux.gallery.MarkUnusedFiles = false;
PC.ux.gallery.CloseAfterInsert = false;
PC.ux.gallery.CloseAfterOutside = false;
PC.ux.gallery.CloseAfterClickOutside = false;
//

if (PC.utils.getCookie('admin_mark_unused_files')) {
	PC.ux.gallery.MarkUnusedFiles = true;
}
if (PC.utils.getCookie('admin_close_after_insert')) {
	PC.ux.gallery.CloseAfterInsert = true;
}
if (PC.utils.getCookie('admin_close_after_click_outside_gallery')) {
	PC.ux.gallery.CloseAfterClickOutside = true;
}

PC.ux.gallery.SelectedCategory = 0;

PC.ux.gallery.files.GetShortName = function(name, record, tpl) {
	if (tpl == undefined) tpl = PC.ux.gallery.FilesCurrentTemplate;
	if (tpl == 'icons') {
		if (name.length > 13) {
			return name.substr(0, 11) +'...';
		}
		return name;
	}
	else if (tpl == 'detailed') {
		if (name.length > 36) {
			return name.substr(0, 16) +'...';
		}
		return name;
	}
	else return name;
}

PC.ux.gallery.files.actions = {
	list: {
		Upload: {
			text: PC.i18n.dialog.gallery.title.upload, iconCls: 'gallery_upload',
			handler: function() {
				PC.dialog.gallery.show_uploader();
			}
		},
		Preview: {
			text: PC.i18n.dialog.gallery.action.preview,
			id: 'gallery_preview_menu',
			iconCls: 'gallery-eye-icon',
			view: null,
			handler: function() {
				this.view.el.frame();
				if (!this.view) this.setView(PC.dialog.gallery.files_view);
				var selected_files = this.view.getSelectedIndexes();
				var record = this.view.store.getAt(selected_files[0]);
				if (record.data.filetype == 'image')
				  PC.dialog.gallery.preview_image(selected_files[0]);
				else
				  window.open('../gallery/'+ record.data.path + record.data.name);
			},
			setView: function(view) {
				this.view = view;
			}
		},
		CreateThumbnail: {
			disabled: true,
			text: PC.i18n.dialog.gallery.action.edit_thumb, icon: 'images/edit.gif',
			handler: function() { return false; }, // don't close menu when item clicked
			menu: {
				items: [ // thumbnail types
					{text: PC.i18n.dialog.gallery.thumbnails.thumbnail},
					{text: PC.i18n.dialog.gallery.thumbnails.small},
					{text: PC.i18n.dialog.gallery.thumbnails.large}
				]
			}
		},
		Rename: {
			disabled: true,
			text: PC.i18n.dialog.gallery.action.rename,
			icon: 'images/edit.gif',
			handler: function(cmp) {
				var selected_indexes = cmp.view.getSelectedIndexes();
				var record = cmp.view.store.getAt(selected_indexes[0]);
				var file_id = record.data.id;
				//
				var nodeEl = cmp.view.getNode(selected_indexes[0]);
				var nodeTitle = Ext.query('span.gallery-file-title', nodeEl)[0];
				var current_filename = record.data.name.substr(0, record.data.name.lastIndexOf('.'));
				// Prompt for user data and process the result using a callback:
				Ext.Msg.prompt(PC.i18n.dialog.gallery.action.rename, PC.i18n.dialog.gallery.new_filename, function(btn, new_filename){
					if (btn == 'ok') {
						if (new_filename == current_filename) return;
						Ext.Ajax.request({
							url: 'ajax.gallery.php?action=rename_file',
							method: 'POST',
							params: {
								file_id: record.data.id,
								filename: new_filename
							},
							callback: function(options, success, result){
								if (success) {
									var json_result = Ext.util.JSON.decode(result.responseText);
									if (json_result.success) {
										record.set('name', json_result.name);
										setTimeout(function(){
											cmp.view.store.reload();
											cmp.view.refresh();
										}, 100);
										//nodeTitle.innerHTML = PC.ux.gallery.files.GetShortName(new_filename);
									}
									else {
										PC.dialog.gallery.show_request_errors(PC.i18n.dialog.gallery.file.rename.error_title, json_result.errors);
									}
								}
								else {
									PC.dialog.gallery.show_connection_error();
								}
							}
						});
					}
				}, null, null, current_filename);
			}
		},
		Delete: {
			hidden: true,
			disabled: true,
			text: PC.i18n.dialog.gallery.action._delete,
			icon: 'images/delete.png',
			handler: function() {
				var selected_indexes = this.view.getSelectedIndexes();
				if (selected_indexes.length == 1) {
					var record = this.view.store.getAt(selected_indexes[0]);
					var file_id = record.data.id;
					PC.dialog.gallery.delete_file(file_id, record);
				}
				else {
					PC.dialog.gallery.delete_files(selected_indexes);
				}
			}
		},
		InsertLink: {
			text: PC.i18n.dialog.gallery.action.insert.link,
			icon: 'images/link.png',
			handler: function() {
				PC.dialog.gallery.insert_files('links');
			}
		},
		Trash: {
			disabled: true,
			/*text: PC.i18n.dialog.gallery.action.move_to_trash,
			icon: 'images/trashe.png',*/
			text: PC.i18n.dialog.gallery.action._delete,
			icon: 'images/delete.png',
			handler: function(cmp) {
				var selected_indexes = cmp.view.getSelectedIndexes();
				if (selected_indexes.length == 1) {
					var record = cmp.view.store.getAt(selected_indexes[0]);
					var file_id = record.data.id;
					PC.dialog.gallery.trash_file(file_id);
				}
				else {
					PC.dialog.gallery.trash_files(selected_indexes);
				}
			}
		},
		Sorting: {
			id:  'sorting_split_button',
			xtype: 'splitbutton', icon: 'images/hmenu-asc.gif',
			tooltip: PC.i18n.dialog.gallery.action.view.view,
			menu: [
				{	text: PC.i18n.dialog.gallery.action.view.sort_by_name + ' ' + PC.i18n.dialog.gallery.action.view.asc,
					icon: 'images/arrow-down.gif',
					handler: function(button){
						PC.dialog.gallery.filesStore.sort('short_name', 'ASC');
						console.log(this);
						this.activate();
					},
					active: true,
					ref: '_sort_by_name_asc'
				},
				{	text: PC.i18n.dialog.gallery.action.view.sort_by_name + ' ' + PC.i18n.dialog.gallery.action.view.desc,
					icon: 'images/arrow-up.gif',
					handler: function(button){
						PC.dialog.gallery.filesStore.sort('short_name', 'DESC');
					},
					active: true,
					ref: '../_sort_by_name_dedc'
				},
				'-',
				{	text: PC.i18n.dialog.gallery.action.view.sort_by_size + ' ' + PC.i18n.dialog.gallery.action.view.asc,
					icon: 'images/arrow-down.gif',
					handler: function(button){
						PC.dialog.gallery.filesStore.sort('size_in_bytes', 'ASC');
					},
					ref: '../../_sort_by_name_dedc2'
				},
				{	text: PC.i18n.dialog.gallery.action.view.sort_by_size + ' ' + PC.i18n.dialog.gallery.action.view.desc,
					icon: 'images/arrow-up.gif',
					handler: function(button){
						PC.dialog.gallery.filesStore.sort('size_in_bytes', 'DESC');
					},
					ref: '../../../_sort_by_name_dedc3'
				},
				'-',
				{	text: PC.i18n.dialog.gallery.action.view.sort_by_modified + ' ' + PC.i18n.dialog.gallery.action.view.asc,
					icon: 'images/arrow-down.gif',
					handler: function(button){
						PC.dialog.gallery.filesStore.sort('modified', 'ASC');
					}
				},
				{	text: PC.i18n.dialog.gallery.action.view.sort_by_modified + ' ' + PC.i18n.dialog.gallery.action.view.desc,
					icon: 'images/arrow-up.gif',
					handler: function(button){
						PC.dialog.gallery.filesStore.sort('modified', 'DESC');
					}
				}
			],
			handler: function() {
				// choose next template in the menu
			}
		},
		ChangeTemplate: {
			id:  'change_template_split_button',
			xtype: 'splitbutton', icon: 'images/application_view_tile.png',
			tooltip: PC.i18n.dialog.gallery.action.view.view,
			menu: [
				{	text: PC.i18n.dialog.gallery.action.view.icons,
					icon: 'images/application_view_tile.png',
					checked: (PC.ux.gallery.FilesCurrentTemplate  == 'icons')?true:false,
					group: 'files_template',
					handler: function(button){
						PC.dialog.gallery.change_files_template('icons', button);
					}
				},
				{	text: PC.i18n.dialog.gallery.action.view.detailed,
					icon: 'images/application_view_columns.png',
					checked: (PC.ux.gallery.FilesCurrentTemplate  == 'detailed')?true:false,
					group: 'files_template',
					handler: function(button){
						PC.dialog.gallery.change_files_template('detailed', button);
					}
				}
			],
			handler: function() {
				// choose next template in the menu
			}
		},
		Settings: {
			id:  'settings_split_button',
			icon: 'images/Compile.png', xtype: 'splitbutton',
			tooltip: PC.i18n.dialog.gallery.title.settings,
			/*handler: function(){
				PC.dialog.gallery.settings.show();
			},*/
			menu: [
				new Ext.menu.CheckItem({
					text: PC.i18n.dialog.gallery.mark_unused,
					checked: PC.ux.gallery.MarkUnusedFiles,
					handler: function() {
						if (!PC.ux.gallery.MarkUnusedFiles) {
							PC.utils.setCookie('admin_mark_unused_files', true);
						}
						else {
							PC.utils.deleteCookie('admin_mark_unused_files');
						}
						PC.ux.gallery.MarkUnusedFiles = !PC.ux.gallery.MarkUnusedFiles;
						PC.dialog.gallery.files_view.refresh();
					}
				}),
				new Ext.menu.CheckItem({
					text: PC.i18n.dialog.gallery.close_after_insert,
					checked: PC.ux.gallery.CloseAfterInsert,
					/*
					handler: function() {
						if (!PC.ux.gallery.CloseAfterInsert) {
							PC.utils.setCookie('admin_close_after_insert', true);
						}
						else {
							PC.utils.deleteCookie('admin_close_after_insert');
						}
						PC.ux.gallery.CloseAfterInsert = !PC.ux.gallery.CloseAfterInsert;
					},
					*/
					listeners: {
						checkchange: function(check_item, checked){
							if (checked) {
								PC.utils.setCookie('admin_close_after_insert', true);
							}
							else {
								PC.utils.deleteCookie('admin_close_after_insert');
							}
							PC.ux.gallery.CloseAfterInsert = checked;
						}
					}
					
				}),
				new Ext.menu.CheckItem({
					text: PC.i18n.dialog.gallery.close_after_click_outside,
					checked: PC.ux.gallery.CloseAfterClickOutside,
					listeners: {
						checkchange: function(check_item, checked){
							if (checked) {
								PC.utils.setCookie('admin_close_after_click_outside_gallery', true);
							}
							else {
								PC.utils.deleteCookie('admin_close_after_click_outside_gallery');
							}
							PC.ux.gallery.CloseAfterClickOutside = checked;
							PC.dialog.gallery.window.pc_set_temp_window(checked);
						}
					}
					
				}),
				/*
				{	icon: 'images/filter.gif', text: PC.i18n.dialog.gallery.mark_unused,
					tooltip: PC.i18n.dialog.gallery.title.filter_unused,
					enableToggle: true,
					pressed: PC.ux.gallery.MarkUnusedFiles,
					handler: function(b){
						if (!PC.ux.gallery.MarkUnusedFiles) {
							PC.utils.setCookie('admin_mark_unused_files', true);
						}
						else {
							PC.utils.deleteCookie('admin_mark_unused_files');
						}
						PC.ux.gallery.MarkUnusedFiles = !PC.ux.gallery.MarkUnusedFiles;
						PC.dialog.gallery.files_view.refresh();
					}
				},
				{	icon: 'images/cross.png', text: 'Close gallery after insert',
					enableToggle: true,
					pressed: PC.ux.gallery.MarkUnusedFiles,
					handler: function(){
						if (!PC.ux.gallery.MarkUnusedFiles) {
							PC.utils.setCookie('admin_mark_unused_files', true);
						}
						else {
							PC.utils.deleteCookie('admin_mark_unused_files');
						}
						PC.ux.gallery.MarkUnusedFiles = !PC.ux.gallery.MarkUnusedFiles;
						PC.dialog.gallery.files_view.refresh();
					}
				},
				*/
				{	text: PC.i18n.dialog.gallery.sync,
					icon: 'images/arrow_refresh.png',
					handler: function() {
						Ext.Ajax.request({
							url: 'ajax.gallery.php?action=sync_category',
							method: 'POST',
							params: {id: PC.ux.gallery.SelectedCategory},
							callback: function(options, success, result){
								if (success) {
									var json_result = Ext.util.JSON.decode(result.responseText);
									//if (json_result.success) {}
									PC.ux.gallery.files.Store.reload();
									dialog.files_view.refresh();
								}
								else {
									PC.dialog.gallery.show_connection_error();
								}
							}
						});
					}
				},
				{	text: PC.i18n.dialog.gallery.title.settings,
					handler: function() {
						//dialog.show_window(PC.dialog.gallery.settings);
						PC.dialog.gallery.settings.show();
					}
				}
			]
		},
		CopyLink: {
			disabled: true,
			text: PC.i18n.dialog.gallery.action.copy_link,
			icon: 'images/link.png',
			handler: function() {
				var category = PC.dialog.gallery.categories.getNodeById(PC.ux.gallery.SelectedCategory);
				var path = '';
				if (category) if (category.attributes.path.length > 0) path = category.attributes.path+'/';
				
				
				//get selected item
				var records = PC.dialog.gallery.files_view.getSelectedRecords();
				var record = records[0];
				var url = PC.global.BASE_URL+'gallery/'+path+record.data.name;
				
				//init clipboard
				ZeroClipboard.setMoviePath('images/ZeroClipboard.swf');
				clip = new ZeroClipboard.Client();
				clip.setText(url);
				//show window
				var window = new PC.ux.Window({
					title: PC.i18n.dialog.gallery.action.copy_to_clipboard,
					closeAction: 'close',
					padding: '5px 5px 0 5px',
					width: 480,
					items: {
						xtype: 'form',
						bodyCssClass: 'x-border-layout-ct',
						border: false,
						defaults: {
							hideLabel: true
						},
						items: [{
							xtype: 'compositefield',
							border: false,
							items: [{
								xtype: 'textfield',
								value: url,
								flex: 1
							}, {
								xtype: 'button',
								text: PC.i18n.copy,
								listeners: {
									afterrender: function(button) {
										clip.addEventListener('onMouseDown', function(){
											window.close();
										});
										setTimeout(function(){
											clip.glue(button.btnEl.id);
										}, 150);
									}
								}
							}]
						}]
					},
					listeners: {
						deactivate: function(w) {
							w.close();
						}
					}
				});
				//dialog.show_window(window);
				PC.dialog.gallery.window.pc_temp_window_children['copy_link'] = window;
				window.show();
			}
		},
		Restore: {
			hidden: true,
			disabled: true,
			text: PC.i18n.dialog.gallery.trash.title.restore,
			iconCls: 'gallery_undo',
			handler: function() {
				PC.dialog.gallery.restore_trash();
			}
		}
	},
	cache: {},
	Get: function(action, view) {
		if (typeof this.list[action] != 'object') return false;
		if (view != undefined) {
			if (view.id == undefined) return false;
			var id = view.id.replace(/-/g, '_');
		}
		else var id = action;
		if (this.cache[id] != undefined) {
			if (this.cache[id][action] != undefined) return this.cache[id][action];
		}
		else this.cache[id] = {};
		var config = this.list[action];
		if (view != undefined) {
			config.view = view;
			if (config.menu != undefined) if (config.menu.items != undefined) {
				Ext.iterate(config.menu.items, function(item){
					item.view = view;
				});
			}
		}
		this.cache[id][action] = new Ext.Action(config);
		return this.cache[id][action];
	}
};
PC.ux.gallery.files.actions.list.Edit = {
	disabled: true,
	text: PC.i18n.dialog.gallery.action.edit.edit,
	iconCls: 'gallery_edit',
	menu: {
		items: [
			PC.ux.gallery.files.actions.Get('CreateThumbnail'),
			'-',
			PC.ux.gallery.files.actions.Get('Rename'),
			PC.ux.gallery.files.actions.Get('Trash'),
			PC.ux.gallery.files.actions.Get('CopyLink')
		]
	}
};
PC.ux.gallery.files.actions.list.Insert = {
	disabled: true,
	hidden: false,
	text: PC.i18n.dialog.gallery.action.insert.insert,
	iconCls: 'gallery_paste',
	menu: {
		items: [
			{xtype: 'menuseparator'},
			PC.ux.gallery.files.actions.Get('InsertLink')
		]
	}
};
/*
PC.ux.gallery.files.menus = {
	list: {
		
	},
	cache: {},
	Get: function(menu, view) {
		if (typeof this.list[action] != 'object') return false;
		if (view == undefined) return false;
		if (view.id == undefined) return false;
		var id = view.id.replace(/-/g, '_');
		if (this.cache[id] != undefined) {
			return this.cache[id];
		}
		this.cache[id] = new Ext.Action(this.list[action]);
		this.cache[id].view = view;
		Ext.Iterate(this.cache[id].menu.items, function(item){
			item.view = view;
		});
		return this.cache[id];
	}
};
*/



PC.ux.gallery.files._Store = function(config){
	this.config = {
		defaults: {
			
		}
	};
}

/*var test = new PC.ux.gallery.files._Store({
	url: PluginPath +'/api.php?action=get_media',
	baseParams: {
		id: null
	}
});*/

PC.ux.gallery.files.Store = function(config){
	if (typeof config != 'object') var config = {};
	Ext.applyIf(config, {
		filter: '',
		url: 'ajax.gallery.php?action=get_files',
		baseParams: {
			category_id: PC.ux.gallery.SelectedCategory
		},
		autoLoad: true,
		fields: [
			'id', 'name', 'extension', 'filetype', 'path', 'category', 'size', 'size_in_bytes', 'modified', 'in_use',
			'replace_name',
			{name: 'short_name', mapping: 'name', convert: PC.ux.gallery.files.GetShortName}
			//{name: 'short_name_for_detailed_tpl', mapping: 'name', convert: this.short_name_for_detailed_tpl}
		]
	});
	// call parent constructor
	PC.ux.gallery.files.Store.superclass.constructor.call(this, config);
};

Ext.extend(PC.ux.gallery.files.Store, Ext.data.JsonStore, {});

PC.ux.gallery.files.View = function(config) {
	if (typeof config != 'object') var config = {};
	//default config
	var defaults = {
		cls: 'gallery_files_view',
		//tpl: new PC.dialog.gallery.files.Template,
		emptyText: PC.i18n.dialog.gallery.no_files,
		emptyTextNormal: PC.i18n.dialog.gallery.no_files,
		emptyTextTrashed: PC.i18n.dialog.gallery.no_files_in_trashed,
		autoScroll: true,
		itemSelector: 'div.thumb-wrap',
		multiSelect: true,
		plugins: new Ext.DataView.DragSelector({dragSafe:true}),
		trackOver: true,
		listeners: {
			mouseenter: function(view, index, node, e) {
				view.fileOverPreviewButton = new Ext.Button(PC.ux.gallery.files.actions.Get('Preview', view));
				view.fileOverPreviewButton.setView(view);
				view.fileOverPreviewButton.addClass('gallery-file-over').setText('').render(Ext.get(node));
			},
			mouseleave: function(view, index, node, e) {
				view.fileOverPreviewButton.destroy();
			}
		}
	};
	if (typeof config.listeners == 'object') {
		Ext.applyIf(defaults.listeners, config.listeners);
		delete config.listeners;
	}
	Ext.applyIf(config, defaults);
	//call parent constructor
	PC.ux.gallery.files.View.superclass.constructor.call(this, config);
};

Ext.extend(PC.ux.gallery.files.View, Ext.DataView, {
	refresh: function() {
		if (this.getStore().baseParams.trashed) {
			this.emptyText = this.emptyTextTrashed;
		}
		else {
			this.emptyText = this.emptyTextNormal;
		}
		PC.ux.gallery.files.View.superclass.refresh.call(this);
	}
});

Ext.ComponentMgr.registerType('pc_gallery_files_view', PC.ux.gallery.files.View);


/*
PC.ux.gallery.files.Template = function(config) {
	//default config
	Ext.applyIf(config, {
		
	});
	//call parent constructor
	PC.ux.gallery.files.Template.superclass.constructor.call(tpl, config);
};

Ext.extend(PC.ux.gallery.files.Template, Ext.XTemplate);

Ext.ComponentMgr.registerType('pc_gallery_files_template', PC.ux.gallery.files.Template);
*/