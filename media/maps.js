function pc_maps_filter(map_manager, map, filters, category_markers, id, this_checked) {
	var checked_count = 0;
	filters.each(function(index, filter){
		if (filter.checked) {
			checked_count++;
		}
	});
	if (!checked_count) {
		//Show all markers
		$.each(category_markers, function(index, category){
			pc_maps_show_category(map_manager, category, map);
		});
	}
	else if (checked_count == 1 && this_checked) {
		//Hide all categories
		$.each(category_markers, function(index, category){
			pc_maps_hide_category(map_manager, category);
		});
	}
	
	if (this_checked) {
		//Show only checked category
		pc_maps_show_category(map_manager, category_markers[id], map);
	}
	else if (!this_checked && checked_count) {
		//Hide only unchecked category
		pc_maps_hide_category(map_manager, category_markers[id]);
	}
}

function pc_maps_show_category(map_manager, markers, map) {
	$(markers).each(function(index, marker){
		map_manager.pc_maps_show_marker(marker, map);
	});
	
	if (typeof pc_maps_show_category_hook == 'function') {
		pc_maps_show_category_hook(map_manager, markers, map);
	}
}

function pc_maps_hide_category(map_manager, markers) {
	$(markers).each(function(index, marker){
		map_manager.pc_maps_hide_marker(marker);
	});
	if (typeof pc_maps_hide_category_hook == 'function') {
		pc_maps_hide_category_hook(map_manager, markers);
	}
}
