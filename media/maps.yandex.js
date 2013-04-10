PC_yandex_maps = {
	pc_maps_show_marker: function (marker, map) {
		map.geoObjects.add(marker);
	},

	pc_maps_hide_marker: function (marker) {
		marker.getMap().geoObjects.remove(marker)
	}
}
