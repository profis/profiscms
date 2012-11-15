Ext.ns('PC.dialog');
PC.dialog.gmaps = {
	edit_mode: false,
	
	load_google: function (after_show_callback) {
		var dialog = this;
		if (typeof google != 'object' || typeof google.maps != 'object' || typeof google.maps.LatLng != 'function') {
			var callback_for_google = function() {
				var callback_for_maps = function() {
					dialog.show_when_js_loaded();
					if (after_show_callback && typeof(after_show_callback) == "function") {
						after_show_callback();
					}
				}
				google.load("maps", "3", {"callback" : callback_for_maps, "other_params": "sensor=false"});
			};
			PC.utils.loadScript('https://www.google.com/jsapi/?sensor=false', callback_for_google);
		}
		else {
			dialog.show_when_js_loaded();
			if (after_show_callback && typeof(after_show_callback) == "function") {
				after_show_callback();
			}
		}
	},
	
	
	show: function(after_show_callback) {
		this.load_google(after_show_callback);
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
                this.default_options = {
			center: new google.maps.LatLng(55.17804878976065, 23.910986328124977),
			zoom: 7,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			streetViewControl: false
		};
		this.options = this.default_options;
		var options = this.options;
		this.panel = new Ext.Panel({
			xtype: 'panel',
			plain: true,
			border: false,
			afterRender: function() {
				this.map = new google.maps.Map(this.container.dom, options);
				this.marker = new google.maps.Marker({
				  map: this.map,
				  draggable: true,
				  animation: google.maps.Animation.DROP,
				  position: options.center
				});
				this.updatePosition();
				google.maps.event.addListener(this.marker, 'dragend', this.updatePosition);
			},
			updatePosition: function() {
				var pos = PC.dialog.gmaps.panel.marker.getPosition();
				PC.dialog.gmaps.panel.map.setCenter(pos);
				var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
				toolbar.get('marker_position_latitude').setValue(Math.round(pos.lat()*1000000)/1000000);
				toolbar.get('marker_position_longitude').setValue(Math.round(pos.lng()*1000000)/1000000);
			}
		});
		this.window = new PC.ux.Window({
			title: this.ln.title,
			closeAction: 'hide',
			width: 800,
			height: 450,
			resizable: false,
			closeAction: 'hide',
			items: this.panel,
			bbar: new Ext.Toolbar({
				items: [
					{	icon: 'images/gmaps_marker.png',
						handler: function() {
							var pos = PC.dialog.gmaps.panel.map.getCenter();
							PC.dialog.gmaps.panel.marker.setPosition(pos);
							var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
							toolbar.get('marker_position_latitude').setValue(Math.round(pos.lat()*1000000)/1000000);
							toolbar.get('marker_position_longitude').setValue(Math.round(pos.lng()*1000000)/1000000);
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
							var geocoder = new google.maps.Geocoder();
							var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
							var address = toolbar.get('marker_position_geocoder').getValue();
							geocoder.geocode({'address': address}, function(results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									PC.dialog.gmaps.panel.marker.setPosition(results[0].geometry.location);
									PC.dialog.gmaps.panel.map.setCenter(results[0].geometry.location);
									toolbar.get('marker_position_latitude').setValue(Math.round(results[0].geometry.location.lat()*1000000)/1000000);
									toolbar.get('marker_position_longitude').setValue(Math.round(results[0].geometry.location.lng()*1000000)/1000000);
								} else {
									alert(dialog.ln.geocoder_error + status);
								}
							});
						}
					},
					{xtype:'tbseparator'},
					{xtype:'tbtext', text: this.ln.latitude+': '},
					{	xtype: 'textfield',
						id: 'marker_position_latitude',
						value: PC.dialog.gmaps.options.latitude,
						width: 65,
						selectOnFocus: true
					},
					{	xtype:'tbtext',
						text: '&nbsp;'+this.ln.longitude+': '
					},
					{	xtype: 'textfield',
						id: 'marker_position_longitude',
						value: PC.dialog.gmaps.options.longitude,
						width: 65,
						selectOnFocus: true
					},
					{	icon: 'images/Search.png',
						style: 'margin-left:2px;',
						handler: function() {
							var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
							var latitude = toolbar.get('marker_position_latitude').getValue();
							var longitude = toolbar.get('marker_position_longitude').getValue();
							var pos = new google.maps.LatLng(latitude, longitude);
							PC.dialog.gmaps.panel.map.setCenter(pos);
							PC.dialog.gmaps.panel.marker.setPosition(pos);
						}
					},
					{xtype:'tbfill'},
					{xtype:'tbtext', text: this.ln.width+': '},
					{	xtype: 'textfield',
						id: 'map_width', value: '320',
						width: 45,
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
						width: 45,
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
								latitude: pos.lat(),
								longitude: pos.lng(),
								zoom: PC.dialog.gmaps.panel.map.getZoom(),
								map_type: PC.dialog.gmaps.panel.map.getMapTypeId()
							};
							var json_data = escape(Ext.util.JSON.encode(map_data));
							if (!PC.dialog.gmaps.edit_mode) {
								//insert new map
								var map_object = '<object classid="clsid:google-map" width="'+width+'" height="'+height+'" codebase="http://maps.google.com/">'
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
		PC.dialog.gmaps.panel.map.setOptions(options);
		PC.dialog.gmaps.panel.marker.setPosition(options.center);
		var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
		var pos = PC.dialog.gmaps.panel.map.getCenter();
		toolbar.get('marker_position_latitude').setValue(Math.round(pos.lat()*1000000)/1000000);
		toolbar.get('marker_position_longitude').setValue(Math.round(pos.lng()*1000000)/1000000);
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
			center: new google.maps.LatLng(settings.latitude, settings.longitude),
			zoom: settings.zoom,
			mapTypeId: eval('google.maps.MapTypeId.'+settings.map_type.toUpperCase()),
			streetViewControl: false
		};
		this.load_options(options);
		//w x h
		var toolbar = PC.dialog.gmaps.window.getBottomToolbar();
		
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