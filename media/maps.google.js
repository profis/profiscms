PC_google_maps = {
	pc_maps_show_marker: function (marker) {
		marker.setVisible(true);
		if (marker._pc_infowindow) {
			marker._pc_infowindow.open(marker.map, marker);
		}
	},

	pc_maps_hide_marker: function (marker) {
		marker.setVisible(false);
		if (marker._pc_infowindow) {
			marker._pc_infowindow.close();
		}
	}
}
