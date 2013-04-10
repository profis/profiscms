Ext.ns('PC.dialog');
if (!PC.dialog.maps) {
	PC.dialog.maps = {}
}

PC.dialog.maps.yandex = {
	panel: false,
	options: false,
	
	load_library: function (after_show_callback, do_not_show) {
		var dialog = this;
		if (typeof YMaps != 'object' || typeof YMaps.Map != 'function') {
			var callback_for_map_library = function() {
				var callback_for_maps = function() {
					if (!do_not_show) {
						dialog.show_when_js_loaded();
					}
					if (after_show_callback && typeof(after_show_callback) == "function") {
						after_show_callback();
					}
				}
				ymaps.ready(callback_for_maps);
			};
			//PC.utils.loadScript('http://api-maps.yandex.ru/1.1/index.xml?key=ANpUFEkBAAAAf7jmJwMAHGZHrcKNDsbEqEVjEUtCmufxQMwAAAAAAAAAAAAvVrubVT4btztbduoIgTLAeFILaQ==', callback_for_map_library);
			PC.utils.loadScript('http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU', callback_for_map_library);
			
		}
		else {
			if (!do_not_show) {
				dialog.show_when_js_loaded();
			}
			if (after_show_callback && typeof(after_show_callback) == "function") {
				after_show_callback();
			}
		}
	},
	
	get_default_options: function () {
		return {
			center: [55.17804878976065, 23.910986328124977],
			zoom: 7,
			behaviors: ['default', 'scrollZoom']
		}
	},
	
	get_default_options_when_library_loaded: function() {
		return {
			
		};
	},
	
	
	after_render: function (options, callback) {
		if (!options) {
			options = this.get_default_options();
		}
		Ext.apply(options, this.get_default_options_when_library_loaded());
		if (options.latitude && options.longitude) {
			options.center = this.get_position(options.latitude, options.longitude);
		}
		this.map = new ymaps.Map (this.container.dom.id, options);
		this.map.controls
			.add('zoomControl')
			//.add('miniMap')
			.add('typeSelector');
			//.add('mapTools');

		if (false) {
			this.marker = new ymaps.Placemark(options.center, { 
				draggable: true
				//content: 'Москва!', 
				//balloonContent: 'Столица России' 
			}, {
				draggable: true
			});

			this.map.geoObjects.add(this.marker);
			this.marker.events.add('dragend', Ext.createDelegate(this.updatePosition, this));
		}
      
		if (callback) {
			callback();
		}
	},
	
	add_marker_to_map_center: function(map, callback, callback_args, click_callback, double_click_callback) {
		var pos = map.getCenter();
		return this.add_marker_to_pos(map, pos, callback, callback_args, click_callback, double_click_callback);
	},
	
	add_marker_to_lat_lng: function(map, lat, lng, callback, callback_args, click_callback, double_click_callback) {
		var pos = this.get_position(lat, lng);
		return this.add_marker_to_pos(map, pos, callback, callback_args, click_callback, double_click_callback)
	},
	
	add_marker_to_pos: function(map, pos, callback, callback_args, click_callback, double_click_callback) {
		var marker = new ymaps.Placemark(pos, { 
			draggable: true
        }, {
			draggable: true
		});
		this.last_new_marker = marker;
		map.geoObjects.add(marker);
		
		if (callback) {
			callback_args.push(marker);
			marker.events.add('dragend', callback.createDelegate(null, callback_args));
			if (click_callback) {
				marker.events.add('click', click_callback.createDelegate(null, callback_args));
			}
			if (double_click_callback) {
				marker.events.add('dblclick', double_click_callback.createDelegate(null, callback_args));
			}
		}
		return pos;
	},
	
	delete_marker: function(marker, map) {
		try {
			map.geoObjects.remove(marker);
		}
		catch(err) {
		
		}
	},
	
	highlight_marker: function(marker) {

	},
	
	unhighlight_marker: function(marker) {

	},
	
	get_map_center_position: function(map) {
		return map.getCenter();
	},
	
	get_marker_position: function(marker) {
		return marker.geometry.getCoordinates();
	},
	
	set_marker_position: function (marker, pos) {
		marker.geometry.setCoordinates(pos);
	},
	
	set_map_center_to_marker: function (map, marker) {
		map.setCenter(this.get_marker_position(marker));
	},
	
	set_options: function(map, marker, options) {
		map.setCenter(options.center, options.zoom);
		if (marker) {
			marker.geometry.setCoordinates(options.center);
		}
	},
	
	get_lat_from_pos: function(pos) {
		return pos[0];
	},
	
	get_lng_from_pos: function(pos) {
		return pos[1];
	},
	
	get_position: function(latitude, longitude) {
		return [latitude, longitude];
	},
	
	get_map_type: function(map) {
		return map.getType();
	},
	
	search_address: function(address, callback, error_callback) {
		var myGeocoder = ymaps.geocode(address);
		myGeocoder.then(
			function (res) {
				if (res.geoObjects.getLength()) {
					var coords = res.geoObjects.get(0).geometry._n;
					if (!coords) {
						coords = res.geoObjects.get(0).geometry._zh;
					}
					callback(coords);
					return;
				}
				return false;
				
			},
			function (err) {
				if (error_callback) {
					error_callback(err);
				}
				return err;
				//debugger;
				// обработка ошибки
			}
		);
		return false;
	}

}