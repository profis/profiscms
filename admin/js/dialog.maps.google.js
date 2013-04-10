Ext.ns('PC.dialog');
if (!PC.dialog.maps) {
	PC.dialog.maps = {}
}
PC.dialog.maps.google = {
	load_library: function (after_show_callback, do_not_show) {
		var dialog = this;
		if (typeof google != 'object' || typeof google.maps != 'object' || typeof google.maps.LatLng != 'function') {
			var callback_for_google = function() {
				var callback_for_maps = function() {
					if (!do_not_show) {
						dialog.show_when_js_loaded();
					}
					if (after_show_callback && typeof(after_show_callback) == "function") {
						after_show_callback();
					}
				}
				google.load("maps", "3", {"callback" : callback_for_maps, "other_params": "sensor=false"});
			};
			PC.utils.loadScript('https://www.google.com/jsapi/?sensor=false', callback_for_google);
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
			zoom: 7,
			streetViewControl: false
		}
	},
	
	get_default_options_when_library_loaded: function() {
		return {
			center: new google.maps.LatLng(55.17804878976065, 23.910986328124977),
			mapTypeId: google.maps.MapTypeId.ROADMAP			
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
		this.map = new google.maps.Map(this.container.dom, options);
		if (false) {
			this.marker = new google.maps.Marker({
				map: this.map,
				draggable: true,
				animation: google.maps.Animation.DROP,
				position: options.center
			});
			google.maps.event.addListener(this.marker, 'dragend',  Ext.createDelegate(this.updatePosition, this));
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
	
	add_marker_to_pos: function(map, position, callback, callback_args, click_callback, double_click_callback) {
		var marker = new google.maps.Marker({
			map: map,
			draggable: true,
			animation: google.maps.Animation.DROP,
			position: position
		});
		this.last_new_marker = marker;
		if (callback) {
			callback_args.push(marker);
			google.maps.event.addListener(marker, 'dragend', callback.createDelegate(null, callback_args));
			if (click_callback) {
				google.maps.event.addListener(marker, 'click', click_callback.createDelegate(null, callback_args));
			}
			if (double_click_callback) {
				google.maps.event.addListener(marker, 'dblclick', double_click_callback.createDelegate(null, callback_args));
			}
			
		}
		return position;
	},
	
	delete_marker: function(marker) {
		try {
			marker.setMap(null);
		}
		catch(err) {
		
		}
	},
	
	highlight_marker: function(marker) {
		marker.setAnimation(google.maps.Animation.BOUNCE);
	},
	
	unhighlight_marker: function(marker) {
		marker.setAnimation(null);
	},
	
	get_map_center_position: function(map) {
		return map.getCenter();
	},
	
	get_marker_position: function(marker) {
		return marker.getPosition();
	},
	
	set_marker_position: function (marker, pos) {
		marker.setPosition(pos);
	},
	
	set_map_center_to_marker: function (map, marker) {
		map.setCenter(marker.getPosition());
	},
	
	set_options: function(map, marker, options) {
		map.setOptions(options);
		if (marker) {
			marker.setPosition(options.center);
		}
	},
	
	get_lat_from_pos: function(pos) {
		return pos.lat();
	},
	
	get_lng_from_pos: function(pos) {
		return pos.lng();
	},
	
	get_position: function(latitude, longitude) {
		return new google.maps.LatLng(latitude, longitude);
	},
	
	get_map_type: function(map) {
		return map.getMapTypeId();
	},
	
	search_address: function(address, callback, error_callback) {
		var geocoder = new google.maps.Geocoder();
							
		geocoder.geocode({'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				callback(results[0].geometry.location);
			} else {
				if (error_callback) {
					error_callback(status);
				}
				return status;
			}
		});
		return false;
	}
}