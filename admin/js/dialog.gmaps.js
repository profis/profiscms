Ext.ns('PC.dialog');

PC.utils.is_array = function(value) {
   return typeof(input)=='object'&&(input instanceof Array);
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
			return;
		}
        this.default_options = PC.dialog.maps[this.map_type].get_default_options();
		this.options = this.default_options;
		var options = this.options;
		this.panel = new Ext.Panel({
			xtype: 'panel',
			plain: true,
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
				var pos = PC.dialog.maps[dialog.map_type].get_marker_position(this.marker);
				this.map.setCenter(pos);
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
		
		this.map_tab = {
			title: 'Map',
			layout: 'vbox',
			items: [
				this.panel
			]
		}
		
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
			]
		};
		
		this.tabs = {
			xtype: 'tabpanel',
			activeTab: 0,
			//width: 700,
			//height: 400,
			flex: 1,
			items: [this.map_tab, this.options_tab],
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
			closeAction: 'hide',
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
										Ext.createDelegate(PC.dialog.maps[dialog.map_type].after_render, panel, [new_options]),
										true
									);
								}
							}
						}
					},
					{	icon: 'images/gmaps_marker.png',
						handler: function() {
							var pos = PC.dialog.gmaps.panel.map.getCenter();
							PC.dialog.maps[dialog.map_type].set_marker_position(panel.marker, pos);
							var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
							toolbar.get('marker_position_latitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lat_from_pos(pos)*1000000)/1000000);
							toolbar.get('marker_position_longitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lng_from_pos(pos)*1000000)/1000000);
						}
					},
					{xtype:'tbseparator'},
					{	xtype: 'textfield',
						id: 'marker_position_geocoder',
						emptyText: this.ln.geocoder_emptytext,
						width: 110
					},
					{	icon: 'images/Search.png',
						handler: function() {
							var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
							var address = toolbar.get('marker_position_geocoder').getValue();
							
							var callback = function(pos) {
								if (pos) {
									if (typeof(pos) != 'string') {
										PC.dialog.maps[dialog.map_type].set_marker_position(panel.marker, pos);
										panel.map.setCenter(pos);
										toolbar.get('marker_position_latitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lat_from_pos(pos)*1000000)/1000000);
										toolbar.get('marker_position_longitude').setValue(Math.round(PC.dialog.maps[dialog.map_type].get_lng_from_pos(pos)*1000000)/1000000);
									}
								}
							}
							
							var error_callback = function(err) {
								alert(dialog.ln.geocoder_error + pos);
							}
							
							PC.dialog.maps[dialog.map_type].search_address(address, callback, error_callback);
							
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
							PC.dialog.maps[dialog.map_type].set_marker_position(panel.marker, pos);
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
							var map_data = {
								latitude: PC.dialog.maps[dialog.map_type].get_lat_from_pos(pos),
								longitude: PC.dialog.maps[dialog.map_type].get_lng_from_pos(pos),
								zoom: PC.dialog.gmaps.panel.map.getZoom(),
								map_type: PC.dialog.maps[dialog.map_type].get_map_type(PC.dialog.gmaps.panel.map),
								map_options: PC.dialog.gmaps.window._gmap_map_options.getValue(),
								marker_options: PC.dialog.gmaps.window._gmap_marker_options.getValue(),
								marker_image: PC.dialog.gmaps.window._gmap_marker_image.getValue()
							};
							var json_data = escape(Ext.util.JSON.encode(map_data));
							if (!PC.dialog.gmaps.edit_mode) {
								//insert new map
								var map_object = '<object classid="clsid:google-map" width="'+width+'" height="'+height+'" codebase="http://maps.google.com/">'
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
	reset_map_data: function() {
		var options = this.default_options;
		return this.load_options(options);
	},
	load_options: function(options) {
		PC.dialog.maps[this.map_type].set_options(PC.dialog.gmaps.panel.map, PC.dialog.gmaps.panel.marker, options);
		var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
		var pos = PC.dialog.gmaps.panel.map.getCenter();
		toolbar.get('marker_position_latitude').setValue(Math.round(PC.dialog.maps[this.map_type].get_lat_from_pos(pos)*1000000)/1000000);
		toolbar.get('marker_position_longitude').setValue(Math.round(PC.dialog.maps[this.map_type].get_lng_from_pos(pos)*1000000)/1000000);
		PC.dialog.gmaps.window._gmap_map_options.setValue(options.map_options);
		PC.dialog.gmaps.window._gmap_marker_options.setValue(options.marker_options);
		PC.dialog.gmaps.window._gmap_marker_image.setValue(options.icon);
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
		var settings = Ext.util.JSON.decode(unescape(this.edit_data.title.map_data));
		var options = {
			center: PC.dialog.maps[this.map_type].get_position(settings.latitude, settings.longitude),
			zoom: settings.zoom,
			//mapTypeId: eval('google.maps.MapTypeId.'+settings.map_type.toUpperCase()),
			streetViewControl: false,
			map_options: settings.map_options,
			marker_options: settings.marker_options,
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
	}
};