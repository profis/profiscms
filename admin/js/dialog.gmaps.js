Ext.ns('PC.dialog');

Dialog_categories_tree_crud = Ext.extend(PC.ux.LocalTreeCrud, {
	
	get_add_form_fields: function() {
		return [
			{	
				fieldLabel: 'Category name',
				name: 'name',
				ref: '../_name'
			},

			{	fieldLabel: 'Icon image',
				xtype:'trigger', 
				ref: '../_image',
				name: 'image',
				triggerClass: 'x-form-search-trigger',
				selectOnFocus: true,
				onTriggerClick: function() {
					var field = this;
					var params = {
						callee: 'image',
						close_after_insert_forced: true,
						show_insert: true,
						save_fn: function(url){
							field.setValue(url);
						}
					};
					var src = field.getValue();
					if (/^gallery\//.test(src)) {
						params.select_id = src.substring(src.lastIndexOf('/')+1);
					}
					PC.dialog.gallery.show(params);
				}
			}
		];
	}
	
});

Dialog_markers_crud = Ext.extend(PC.ux.LocalCrud, {
	no_ln_fields: true,
	edit_window_width: 500,
	
	get_button_for_edit: function() {
		var button = Dialog_markers_crud.superclass.get_button_for_edit.call(this);
		button.text = '';
		return button;
	},
	
	get_button_for_del: function() {
		var button = Dialog_markers_crud.superclass.get_button_for_del.call(this);
		button.text = '';
		return button;
	},
	
	get_dynamic_buttons: function(button_container) {
		if (!button_container) {
			button_container = this;
		}
		var buttons = Dialog_markers_crud.superclass.get_dynamic_buttons.call(button_container, this);
		buttons.push(button_container._action_center);
		buttons.push(button_container._action_center_marker);
		return buttons;
	},
	
	get_button_for_center: function() {
		return {	
			disabled: true,
			ref: '../_action_center',
			text: '',
			icon: 'images/eye.png',
			handler: Ext.createDelegate(this.button_handler_for_center, this)
		};
	},
	
	button_handler_for_center: function() {
		var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
		map_object.set_map_center_to_marker(PC.dialog.gmaps.panel.map, this.selected_record._marker);
	},
	
	get_button_for_center_marker: function() {
		return {	
			disabled: true,
			ref: '../_action_center_marker',
			text: '',
			icon: 'images/gmaps_marker.png',
			handler: Ext.createDelegate(this.button_handler_for_center_marker, this)
		};
	},
	
	button_handler_for_center_marker: function() {
		var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
		map_object.set_marker_position(this.selected_record._marker, PC.dialog.gmaps.panel.map.getCenter());
		
		var position = map_object.get_marker_position(this.selected_record._marker);
		
		this.selected_record.set('latitude', map_object.get_lat_from_pos(position));
		this.selected_record.set('longitude', map_object.get_lng_from_pos(position));
		this.selected_record.commit();
	},
	
	get_tbar_buttons: function() {
		var buttons = Dialog_markers_crud.superclass.get_tbar_buttons.call(this);
		buttons.push(this.get_button_for_center());
		buttons.push(this.get_button_for_center_marker());
		return buttons;
	},
	
	get_cell_dblclick_handler: function() {
		return Ext.createDelegate(this.button_handler_for_center, this);
	},
	
	get_store_fields: function() {
		return [
				'id', 'latitude', 'longitude', 'options', 'icon', 'category', 'text', 'marker_link'
		];
	},
	
	get_add_form_fields: function() {
		return [
			{	
				xtype: 'textarea',
				anchor: '100%',
				_fld: 'text',
				fieldLabel: 'Text',//this.ln.bg_image,
				ref: '../../_gmap_marker_text'
			},
			{	
				xtype: 'textarea',
				anchor: '100%',
				_fld: 'options',
				fieldLabel: 'Marker options (separated by comma)',//this.ln.bg_image,
				ref: '../../_gmap_marker_options'
			},
			{	anchor: '100%',
				fieldLabel: 'Icon image',
				_fld: 'icon',
				xtype:'trigger', 
				ref: '../../_gmap_marker_image',
				triggerClass: 'x-form-search-trigger',
				selectOnFocus: true,
				onTriggerClick: function() {
					var field = this;
					var params = {
						callee: 'image',
						close_after_insert_forced: true,
						show_insert: true,
						save_fn: function(url){
							field.setValue(url);
						}
					};
					var src = field.getValue();
					if (/^gallery\//.test(src)) {
						params.select_id = src.substring(src.lastIndexOf('/')+1);
					}
					PC.dialog.gallery.show(params);
				}
			},
			{	anchor: '100%',
				fieldLabel: 'Category',
				_fld: 'category',
				xtype:'trigger', 
				ref: '../../_gmap_marker_category',
				triggerClass: 'x-form-search-trigger',
				selectOnFocus: true,
				onTriggerClick: function () {
					PC.dialog.gmaps.markers_crud.open_category_select_window(this);
				}
			},
			PC.view_factory.get_shortcut_field({
				id: 'gmap_dialog_marker_link_field',
				fieldLabel: 'Link',
				ref: '../../_gmap_marker_link',
				_fld: 'marker_link',
				labelAlign: 'left',
				anchor: '100%'
			})

		];
	},
	
	open_category_select_window: function(field) {
		this.marker_category_field = field;
		if (true || !this.category_selector) {
			var categories = [];
						
			var serializer = new Ext.tree.JsonTreeSerializer(PC.dialog.gmaps.categories_panel.tree);
						
			categories = Ext.util.JSON.decode(serializer.toString(true));

			if (!categories.length) {
				categories = PC.dialog.gmaps.options.categories;
			}
		
			var tree = new Ext.tree.TreePanel( {
				height: 300,
				width: 200,
				
				animate:true,
				enableDD: false,
				loader: new Ext.tree.TreeLoader(), // Note: no dataurl, register a TreeLoader to make use of createNode()
				root: new Ext.tree.AsyncTreeNode({
					text: 'root',
					draggable:false,
					id:'source',
					children: categories
				}),
				rootVisible:false,
				listeners: {
					afterrender: function() {
						this.expandAll();
					}
				}
			});
			
			var window = new PC.ux.Window({
				modal: true,
				items: tree,
				width: 200,
				layout: 'fit',
				closeAction: 'hide',
				bbar: [
					{xtype:'tbfill'},
					{	text: PC.i18n.save,
						icon: 'images/disk.png',
						handler: Ext.createDelegate(this.set_marker_category, this)
					}
				]
			});
			this.category_selector_tree = tree;
			this.category_selector = window;
					
		}
		this.category_selector.show();
	},
	
	set_marker_category: function() {
		var selected_node = this.category_selector_tree.selModel.getSelectedNode();
		if (selected_node && this.marker_category_field) {
			this.marker_category_field.setValue(selected_node.attributes._data.name + ' [' + selected_node.id + ']');
			this.category_selector.hide();
		}
	},
	
	get_grid: function() {
		var grid = Dialog_markers_crud.superclass.get_grid.call(this);
		
		grid.on('mouseover', function (e, t) {
			var me = this;
			var rowIndex = me.getView().findRowIndex(t);
			var record = me.store.getAt(rowIndex);
			if (record && record._marker) {
				var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
				map_object.highlight_marker(record._marker);
				}
			
		});
		grid.on('mouseout', function (e, t) {
			var me = this;
			var rowIndex = me.getView().findRowIndex(t);
			var record = me.store.getAt(rowIndex);
			if (record && record._marker) {
				var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
				map_object.unhighlight_marker(record._marker);
			}
			
		});
		return grid;
	},
	
	get_button_handler_for_delete: function() {
		return Ext.createDelegate(this.button_handler_for_del, this);
	},
		
	button_handler_for_del: function() {
		Ext.MessageBox.show({
			buttons: Ext.MessageBox.YESNO,
			title: this.ln._delete.confirm_title,
			msg: this.ln._delete.confirm_message,
			icon: Ext.MessageBox.WARNING,
			maxWidth: 320,
			fn: Ext.createDelegate(this.button_handler_for_del_submit, this)
		});
	},
	
	button_handler_for_del_submit: function(btn_id) {
		if (btn_id == 'yes') {
			var selected_records = this.grid.getSelectionModel().getSelections();
			var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
			Ext.each(selected_records ,function(record, index) {
				this.store.remove(record);
				map_object.delete_marker(record._marker, PC.dialog.gmaps.panel.map);
			}, this);
		}
	},
	
	get_button_for_add: function() {
		return {	
			ref: '../action_add',
			text: this.ln.button._add?this.ln.button._add:PC.i18n.add,
			icon: 'images/add.png',
			handler: Ext.createDelegate(this.button_handler_for_add, this)
		};
	},
	
	button_handler_for_add: function() {
		this.max_id++;
		var marker_id = this.max_id;
		var callback_args = [marker_id];
		var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
		var pos = map_object.add_marker_to_map_center(this.map_panel.map, Ext.createDelegate(this.marker_drop, this), callback_args, Ext.createDelegate(this.marker_click, this), Ext.createDelegate(this.button_handler_for_edit, this));
		
		var new_record = new this.store.recordType({
			id: this.max_id,
			latitude: map_object.get_lat_from_pos(pos),
			longitude: map_object.get_lng_from_pos(pos)
		}, this.max_id);
		new_record._marker = map_object.last_new_marker;
		this.store.add([new_record]);
	},
	
	clear_data: function() {
		var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
		var records = this.store.getRange();
		this.max_id = 0;
		Ext.each(records, function(record, index) {
			map_object.delete_marker(record._marker, PC.dialog.gmaps.panel.map);
			this.store.remove(record);
		}, this);
	},
	
	add_markers_on_map: function() {
		var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
		var records = this.store.getRange();
		this.max_id = 0;
		Ext.each(records, function(record, index) {
			this.max_id++;
			var callback_args = [this.max_id];
			map_object.add_marker_to_lat_lng(this.map_panel.map, record.data.latitude, record.data.longitude, Ext.createDelegate(this.marker_drop, this), callback_args, Ext.createDelegate(this.marker_click, this),  Ext.createDelegate(this.button_handler_for_edit, this));
			
			record._marker =  map_object.last_new_marker;
		}, this);
	},
		
	load_data: function(data) {
		
		var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
		var markers = {};
		Ext.each(data.list, function(marker_data, index) {
			this.max_id++;
			data.list[index].id = this.max_id;
			var callback_args = [this.max_id];
			map_object.add_marker_to_lat_lng(this.map_panel.map, marker_data.latitude, marker_data.longitude, Ext.createDelegate(this.marker_drop, this), callback_args, Ext.createDelegate(this.marker_click, this), Ext.createDelegate(this.button_handler_for_edit, this));
			markers[this.max_id] =  map_object.last_new_marker;
		}, this);
		
		Dialog_markers_crud.superclass.load_data.call(this, data);
		
		for (var i=1; i <= this.max_id; i++) { 
			var record = this.store.getAt(i - 1);
			if (record) {
				record._marker = markers[i];
			}
		}
	},
	
	marker_drop: function(id, marker) {
		var map_object = PC.dialog.maps[PC.dialog.gmaps.map_type];
		var record = this.store.getById(id);
		
		var position = map_object.get_marker_position(marker);
		
		record.set('latitude', map_object.get_lat_from_pos(position));
		record.set('longitude', map_object.get_lng_from_pos(position));
		record.commit();
	},
	
	marker_click: function (id, marker) {
		var record = this.store.getById(id);
		this.grid.selModel.selectRecords([record]);
	}
	
});

PC.utils.is_array = function(value) {
   return typeof(value)=='object'&&(value instanceof Array);
};

PC.dialog.gmaps = {
	edit_mode: false,
	
	//map_type: 'yandex',
	map_type: 'google',
	
	load_library: function (after_show_callback) {
		PC.dialog.maps[this.map_type].load_library.defer(0, this, [after_show_callback]);
	},
	
	
	show: function(after_show_callback, el) {
		var original_callback = after_show_callback;
		if (el) {
			var attributes = Ext.util.JSON.decode('{'+el.title+'}');
			if (attributes.map_type) {
				if (this.panel && this.map_type != attributes.map_type) {
					after_show_callback = Ext.createDelegate(function() {
						PC.dialog.maps[this.map_type].load_library(
							Ext.createDelegate(PC.dialog.maps[this.map_type].after_render, PC.dialog.gmaps.panel, [false, original_callback]),
							true
						);
					}, this)					
				}
				this.map_type = attributes.map_type;
			}
		}
		else if (!after_show_callback) {
			after_show_callback = Ext.createDelegate(this.reset_map_data, this);
		}
		this.load_library(after_show_callback);
	},
	show_when_js_loaded: function() {
		this.ln = PC.i18n.dialog.gmaps;
		var dialog = this;
		//if gmaps window is already created, just show it and return
		if (this.window) {
			this.window.show();
			if (this.edit_mode) {
				this.edit_mode = false;
				this.toggle_edit_mode();
			}
			else {
				//debugger;
			}
			return;
		}
        this.default_options = PC.dialog.maps[this.map_type].get_default_options();
		this.options = this.default_options;
		var options = this.options;
		this.panel = new Ext.Panel({
			xtype: 'panel',
			//plain: true,
			border: false,
			flex: 1,
			autoHeight: true,
			ref: '../../../_map_panel',
			afterRender: function() {
				//debugger;
			},
			afterRender_: function() {
				PC.dialog.maps[dialog.map_type].after_render.defer(0, this, [options]);
			},
			listeners: {
				afterrender: function(panel) {
							
				},
				afterlayout: function(panel, layout) {
					debugger;
				},
				afterLayout: function(panel, layout) {
					debugger;
				},
				resize: function(panel, adjWidth, adjHeight, rawWidth, rawHeight) {
					debugger;
				},
				expand: function() {
					debugger;
				},
				bodyresize: function() {
					debugger;
				}
			},
			updatePosition: function(e) {
				//var pos = PC.dialog.maps[dialog.map_type].get_marker_position(this.marker);
				var pos = PC.dialog.maps[dialog.map_type].get_map_center_position(this.map);
				//this.map.setCenter(pos);
				var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
				toolbar.get('marker_position_latitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lat_from_pos(pos)*1000000)/1000000);
				toolbar.get('marker_position_longitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lng_from_pos(pos)*1000000)/1000000);
			},
			get_default_options: function() {
				return PC.dialog.maps[dialog.map_type].get_default_options();
			},
			get_default_options_when_library_loaded: function() {
				return PC.dialog.maps[dialog.map_type].get_default_options_when_library_loaded();
			},
			get_position: function(latitude, longitude) {
				return PC.dialog.maps[dialog.map_type].get_position(latitude, longitude);
			}
		});
		
		var panel = this.panel;
		
		this.markers_crud = new Dialog_markers_crud({
			width: 180
		});
		
		this.markers_crud.map_panel = this.panel;
		
		this.panel_container = new Ext.Panel({
			xtype: 'panel',
			layout: 'fit',
			//plain: true,
			border: false,
			flex: 1,
			items: this.panel
		});
		
		this.map_tab = {
			title: 'Map',
			layout: {
				type: 'hbox',
				align: 'stretch'
			},
			items: [
				this.panel_container,
				this.markers_crud
			]
		};
		
		this.options_tab = {
			title: 'Options',
			layout: 'form',
			padding: '6px 9px 0 3px',
			border: false,
			autoScroll: true,
			bodyCssClass: 'x-border-layout-ct',
			//labelWidth: 120,
			labelAlign: 'top',
			defaults: {anchor: '100%', xtype:'textarea'},
			items: [
				{	
					fieldLabel: 'Map options (separated by comma)',//this.ln.bg_image,
					ref: '../../_gmap_map_options'
				},
				
				{	
					fieldLabel: 'Marker options (separated by comma)',//this.ln.bg_image,
					ref: '../../_gmap_marker_options'
				},
				{	fieldLabel: 'Icon image',
					xtype:'trigger', 
					ref: '../../_gmap_marker_image',
					triggerClass: 'x-form-search-trigger',
					selectOnFocus: true,
					onTriggerClick: function() {
						var field = this;
						var params = {
							callee: 'image',
							close_after_insert_forced: true,
							show_insert: true,
							save_fn: function(url){
								field.setValue(url);
							}
						};
						var src = field.getValue();
						if (/^gallery\//.test(src)) {
							params.select_id = src.substring(src.lastIndexOf('/')+1);
						}
						PC.dialog.gallery.show(params);
					}
				}
			]
		};
		
		var json = [];
	
		this.categories_panel = new Dialog_categories_tree_crud({
			json: json
			//height: 250
		});
	
		
		this.categories_tab = {
			title: 'Categories',
			layout: 'fit',
			items: [
				this.categories_panel
				//tree
			]
		};
		
		this.advanced_tab = {
			title: 'Advanced',
			layout: 'form',
			padding: '6px 3px 3px 3px',
			border: false,
			autoScroll: true,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor: '96%', xtype:'textfield'},
			items: [
				{	ref: '../../_map_class',
					fieldLabel: 'Class',
					xtype: 'combo', 
					mode: 'local',
					store: PC.utils.Get_classes_array('div'),
					triggerAction: 'all'
				},
				{	fieldLabel: 'Style',
					ref: '../../_map_style'
				}
			]
		};
		
		this.tabs = {
			xtype: 'tabpanel',
			activeTab: 0,
			//width: 700,
			//height: 400,
			flex: 1,
			items: [this.map_tab, this.options_tab, this.categories_tab, this.advanced_tab],
			border: false
		};
		
		this.window = new PC.ux.Window({
			title: this.ln.title,
			layout: 'vbox',
			layoutConfig: {
				align: 'stretch'
			},
			closeAction: 'hide',
			width: 800,
			height: 450,
			resizable: false,
			//items: this.panel,
			items: this.tabs,
			listeners: {
				afterrender: function(window) {
					PC.dialog.maps[dialog.map_type].after_render.defer(0, panel, [options]);
					panel.updatePosition();
				}
			},
			bbar: new Ext.Toolbar({
				items: [
					{	
						width: 65,
						xtype: 'combo',
						mode: 'local',
						ref: 'map_type_select',
						store: {
							xtype: 'arraystore',
							fields: ['value', 'name'],
							idIndex: 0,
							data: [
								['google', 'Google'],
								['yandex', 'Yandex']
							]
						},
						displayField: 'name',
						//tpl: '<tpl for="."><div class="x-combo-list-item" style="text-align:{align}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
						valueField: 'value',
						value: dialog.map_type,
						triggerAction: 'all',
						listeners:	{
							select: function(combo, record, index) {
								if (PC.dialog.gmaps.map_type != record.id) {
									PC.dialog.gmaps.map_type = record.id;
									var new_options = panel.get_default_options();
									new_options.zoom = PC.dialog.gmaps.panel.map.getZoom();
									var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
									new_options.latitude = toolbar.get('marker_position_latitude').getValue();
									new_options.longitude = toolbar.get('marker_position_longitude').getValue();
									//new_options.center = PC.dialog.maps[dialog.map_type].get_position(latitude, longitude);
									PC.dialog.maps[dialog.map_type].load_library(
										Ext.createDelegate(PC.dialog.maps[dialog.map_type].after_render, panel, [new_options, Ext.createDelegate(PC.dialog.gmaps.markers_crud.add_markers_on_map, PC.dialog.gmaps.markers_crud)]),
										true
									);
								}
							}
						}
					},
					/*
					{	icon: 'images/gmaps_marker.png',
						handler: function() {
							var pos = PC.dialog.gmaps.panel.map.getCenter();
							PC.dialog.maps[dialog.map_type].set_marker_position(panel.marker, pos);
							var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
							toolbar.get('marker_position_latitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lat_from_pos(pos)*1000000)/1000000);
							toolbar.get('marker_position_longitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lng_from_pos(pos)*1000000)/1000000);
						}
					},
					*/
					{xtype:'tbseparator'},
					{	xtype: 'textfield',
						id: 'marker_position_geocoder',
						emptyText: this.ln.geocoder_emptytext,
						width: 110,
						listeners: {
							specialkey: function(fld, e) {
								if (e.getKey() == e.ENTER) {
									PC.dialog.gmaps.search();
								}
							}
						}
					},
					{	icon: 'images/Search.png',
						id: 'search_map_button',
						handler: function() {
							PC.dialog.gmaps.search();							
						}
					},
					{xtype:'tbseparator'},
					{xtype:'tbtext', text: this.ln.latitude+': '},
					{	xtype: 'textfield',
						id: 'marker_position_latitude',
						value: PC.dialog.gmaps.options.latitude,
						width: 55,
						selectOnFocus: true
					},
					{	xtype:'tbtext',
						text: '&nbsp;'+this.ln.longitude+': '
					},
					{	xtype: 'textfield',
						id: 'marker_position_longitude',
						value: PC.dialog.gmaps.options.longitude,
						width: 55,
						selectOnFocus: true
					},
					{	icon: 'images/Search.png',
						style: 'margin-left:2px;',
						handler: function() {
							var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
							var latitude = toolbar.get('marker_position_latitude').getValue();
							var longitude = toolbar.get('marker_position_longitude').getValue();
							var pos = PC.dialog.maps[dialog.map_type].get_position(latitude, longitude);
							PC.dialog.gmaps.panel.map.setCenter(pos);
							//PC.dialog.maps[dialog.map_type].set_marker_position(panel.marker, pos);
						}
					},
					{xtype:'tbfill'},
					{xtype:'tbtext', text: this.ln.width+': '},
					{	xtype: 'textfield',
						id: 'map_width', value: '320',
						width: 40,
						selectOnFocus: true
					},
					{	id: 'w_unit',
						xtype: 'combo', 
						width: 40,
						style: 'margin-left:2px;',
						mode: 'local',
						store: ['px','%'],
						value: 'px',
						editable: false,
						triggerAction: 'all'
					},
					{xtype:'tbtext', text: '&nbsp;'+this.ln.height+': ', style: 'margin-left:3px'},
					{	xtype: 'textfield',
						id: 'map_height', value: '240',
						width: 40,
						selectOnFocus: true
					},
					{	id: 'h_unit',
						xtype: 'combo', 
						width: 40,
						style: 'margin-left:2px;',
						mode: 'local',
						store: ['px','%'],
						value: 'px',
						editable: false,
						triggerAction: 'all'
					},
					{xtype:'tbseparator', style:'margin-left:5px'},
					{	id: 'gmaps-submit',
						text: '&nbsp;<b>'+this.ln.insert+'</b>', icon: 'images/Paste.png',
						handler: function() {
							var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
							var width = toolbar.get('map_width').getValue();
							var w_unit = toolbar.get('w_unit').getValue();
							if (w_unit != 'px') {
								width += w_unit;
							}
							var height = toolbar.get('map_height').getValue();
							var h_unit = toolbar.get('h_unit').getValue();
							if (h_unit != 'px') {
								height += h_unit;
							}
							var pos = PC.dialog.gmaps.panel.map.getCenter();
							var new_categories = '[]';
							if (PC.dialog.gmaps.categories_panel.tree.rendered) {
								var serializer = new Ext.tree.JsonTreeSerializer(PC.dialog.gmaps.categories_panel.tree);
								new_categories = Ext.util.JSON.decode(serializer.toString(true));
							}
							else if (PC.dialog.gmaps.original_options_json) {
								new_categories = Ext.util.JSON.decode(PC.dialog.gmaps.original_options_json);
							}
							//serializer.jsonAttributes.push('text');
							//debugger;
							var map_data = {
								latitude: PC.dialog.maps[dialog.map_type].get_lat_from_pos(pos),
								longitude: PC.dialog.maps[dialog.map_type].get_lng_from_pos(pos),
								zoom: PC.dialog.gmaps.panel.map.getZoom(),
								map_type: PC.dialog.maps[dialog.map_type].get_map_type(PC.dialog.gmaps.panel.map),
								map_options: PC.dialog.gmaps.window._gmap_map_options.getValue(),
								map_class: PC.dialog.gmaps.window._map_class.getValue(),
								map_style: PC.dialog.gmaps.window._map_style.getValue(),
								marker_options: PC.dialog.gmaps.window._gmap_marker_options.getValue(),
								marker_image: PC.dialog.gmaps.window._gmap_marker_image.getValue(),
								categories: new_categories,
								markers: PC.dialog.gmaps.markers_crud.get_store_data()
							};
							//debugger;
							//var json_data = escape(Ext.util.JSON.encode(map_data));
							var json_data = encodeURI(Ext.util.JSON.encode(map_data));
							//var json_data = Ext.util.JSON.encode(map_data);
							if (!PC.dialog.gmaps.edit_mode) {
								//insert new map
								var map_object = '<object alt="'+dialog.map_type+'" title="'+dialog.map_type+'" classid="clsid:google-map" width="'+width+'" height="'+height+'" codebase="http://maps.google.com/">'
												   +'<param name="map_type" value="'+dialog.map_type+'" />'
												  +'<param name="map_data" value="'+json_data+'" />'
												  +'<embed src="maps.google.com" type="application/google-map" width="'+width+'" height="'+height+'" map_data="'+json_data+'"></embed>'
												   +'</object>';
								tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent", false, map_object);
							} else {
								//update dimensions
								PC.dialog.gmaps.edit_data.element.setAttribute('width', width);
								PC.dialog.gmaps.edit_data.element.setAttribute('height', height);
								//update map data
								PC.dialog.gmaps.edit_data.title.map_type = dialog.map_type;
								PC.dialog.gmaps.edit_data.title.map_data = json_data;
								PC.dialog.gmaps.edit_data.element.title = Ext.util.JSON.encode(PC.dialog.gmaps.edit_data.title).replace(/[{}]/g, '');
							}
							PC.dialog.gmaps.window.hide();
						}
					}
				]
			})
		});
		this.window.show();
	},
	
	add_default_marker: function() {
		var options = this.default_options;
		if (!this.markers_crud.max_id) {
			this.markers_crud.load_data({list:[{id:1, latitude:PC.dialog.maps[this.map_type].get_lat_from_pos(options.center), longitude:PC.dialog.maps[this.map_type].get_lng_from_pos(options.center)}]});
		}
	},
	
	
	reset_map_data: function() {
		var options = this.default_options;
		options.markers = [];
		options.categories = [];
		
		this.load_options(options);
		this.add_default_marker();
	},
	load_options: function(options) {
		PC.dialog.maps[this.map_type].set_options(PC.dialog.gmaps.panel.map, PC.dialog.gmaps.panel.marker, options);
		var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
		var pos = PC.dialog.gmaps.panel.map.getCenter();
		toolbar.get('marker_position_latitude').setValue(Math.round(PC.dialog.maps[this.map_type].get_lat_from_pos(pos)*1000000)/1000000);
		toolbar.get('marker_position_longitude').setValue(Math.round(PC.dialog.maps[this.map_type].get_lng_from_pos(pos)*1000000)/1000000);
		PC.dialog.gmaps.window._gmap_map_options.setValue(options.map_options);
		PC.dialog.gmaps.window._map_class.setValue(options.map_class);
		PC.dialog.gmaps.window._map_style.setValue(options.map_style);
		PC.dialog.gmaps.window._gmap_marker_options.setValue(options.marker_options);
		PC.dialog.gmaps.window._gmap_marker_image.setValue(options.icon);
		this.original_options_json = Ext.util.JSON.encode([]);
		if (options.categories) {
			this.original_options_json = Ext.util.JSON.encode(options.categories);
			PC.dialog.gmaps.categories_panel.set_children(options.categories);
		}
		if (!options.markers) {
			var old_marker = {
				id: 1,
				latitude: PC.dialog.maps[this.map_type].get_lat_from_pos(pos),
				longitude: PC.dialog.maps[this.map_type].get_lng_from_pos(pos),
				options: '',
				icon: '',
				category: '',
				marker_link: '',
				text: ''
			};
			options.markers = [old_marker];
		}
		if (options.markers) {
			PC.dialog.gmaps.markers_crud.clear_data();
			PC.dialog.gmaps.markers_crud.load_data({list:options.markers});
		}
		this.options = options;
		return true;
	},
	/*
	edit_map: function(el) {
		this.load_google('edit_map_when_js_loaded', el);
	},
	*/
	
	edit_map: function(el) {
		this.edit_element = el;
		this.edit_data = {
			element: el,
			title: Ext.util.JSON.decode('{'+el.title+'}')
		};
		var settings = {};
		try {
			settings = Ext.util.JSON.decode(decodeURI(this.edit_data.title.map_data));
		} catch(err) {
			settings = Ext.util.JSON.decode(unescape(this.edit_data.title.map_data));	
		}
		//debugger;
		var options = {
			center: PC.dialog.maps[this.map_type].get_position(settings.latitude, settings.longitude),
			zoom: settings.zoom,
			//mapTypeId: eval('google.maps.MapTypeId.'+settings.map_type.toUpperCase()),
			streetViewControl: false,
			map_options: settings.map_options,
			map_class: settings.map_class,
			map_style: settings.map_style,
			marker_options: settings.marker_options,
			categories: settings.categories,
			markers: settings.markers,
			icon: settings.marker_image
		};
		this.load_options(options);
		//w x h
		var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
		
		if (toolbar.map_type_select.getValue() != this.map_type) {
			toolbar.map_type_select.setValue(this.map_type);
		}
		
		//width
		var w = (el.getAttribute('width')+'').split(/^([0-9]+)([a-z%]*)$/i);
		toolbar.get('map_width').setValue(w[1]);
		if (w[2] != undefined) if (w[2].length) toolbar.get('w_unit').setValue(w[2]);
		
		//height
		var h = (el.getAttribute('height')+'').split(/^([0-9]+)([a-z%]*)$/i);
		toolbar.get('map_height').setValue(h[1]);
		if (h[2] != undefined) if (h[2].length) toolbar.get('h_unit').setValue(h[2]);
		
		//enable edit mode
		this.edit_mode = true;
		this.toggle_edit_mode();
	},
	
	toggle_edit_mode: function() {
		if (this.edit_mode) {
			var toolbar = this.window.getBottomToolbar();
			var submit_button = toolbar.get('gmaps-submit');
			submit_button.setText('<b>'+this.ln.update+'</b>');
		} else {
			var toolbar = this.window.getBottomToolbar();
			var submit_button = toolbar.get('gmaps-submit');
			submit_button.setText('<b>'+this.ln.insert+'</b>');
		}
	},
	
	search: function() {
		var dialog = this;
		var panel = this.panel;
		
		var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
		var address = toolbar.get('marker_position_geocoder').getValue();

		var callback = function(pos) {
			if (pos) {
				if (typeof(pos) != 'string') {
					//PC.dialog.maps[dialog.map_type].set_marker_position(panel.marker, pos);
					panel.map.setCenter(pos);
					toolbar.get('marker_position_latitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lat_from_pos(pos)*1000000)/1000000);
					toolbar.get('marker_position_longitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lng_from_pos(pos)*1000000)/1000000);
				}
			}
		};

		var error_callback = function(err) {
			alert(dialog.ln.geocoder_error + err);
		};

		PC.dialog.maps[dialog.map_type].search_address(address, callback, error_callback);
		return;
	}
};