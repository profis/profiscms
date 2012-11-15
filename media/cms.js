$.fn.assignPrettyPhoto = function() {
	if(this.length == 0) return this;
	var overlay_gallery = true;
	if (this.length == 1) {
		var overlay_gallery = false;
	}
	
	this.prettyPhoto({
		social_tools: '',
		show_title: false,
		theme: 'facebook',
		deeplinking: false,
		//overlay_gallery: overlay_gallery,
		//allow_resize: true,
		markup: '<div class="pp_pic_holder"> \
					<div class="ppt">&nbsp;</div> \
					<div class="pp_top"> \
						<div class="pp_left"></div> \
						<div class="pp_middle"></div> \
						<div class="pp_right"></div> \
					</div> \
					<div class="pp_content_container"> \
						<div class="pp_left"> \
						<div class="pp_right"> \
							<div class="pp_content"> \
								<div class="pp_loaderIcon"></div> \
								<div class="pp_fade"> \
									<a href="#" class="pp_expand" title="Expand the image">Expand</a> \
									<div class="pp_hoverContainer"> \
										<a class="pp_close" style="position:absolute;top:2px;right:2px"></a> \
										<a class="pp_next" href="#">next</a> \
										<a class="pp_previous" href="#">previous</a> \
									</div> \
									<div id="pp_full_res"></div> \
									<div class="pp_details"> \
									</div> \
								</div> \
							</div> \
						</div> \
						</div> \
					</div> \
					<div class="pp_bottom"> \
						<div class="pp_left"></div> \
						<div class="pp_middle"></div> \
						<div class="pp_right"></div> \
					</div> \
				</div> \
				<div class="pp_overlay"></div>'
	});
	return this;
};

$.fn.setCursorPosition = function(position){
	if(this.length == 0) return this;
	return $(this).setSelection(position, position);
}

$.fn.setSelection = function(selectionStart, selectionEnd) {
	if(this.length == 0) return this;
	input = this[0];

	if (input.createTextRange) {
		var range = input.createTextRange();
		range.collapse(true);
		range.moveEnd('character', selectionEnd);
		range.moveStart('character', selectionStart);
		range.select();
	} else if (input.setSelectionRange) {
		input.focus();
		input.setSelectionRange(selectionStart, selectionEnd);
	}

	return this;
}

$.fn.focusEnd = function(){
	this.setCursorPosition(this.val().length);
}

$(document).ready(function(){
	var rel_groups = {};
	var rel_links = {};
	$(".pc_content a").not('.nolightbox a').each(function(index, link){
		var a = $(link);
		var rel = a.attr('rel');
		var defaulf_group = 'content';
		var group = defaulf_group;
		if (!(/^(no)?lightbox/.test(rel))) {
			var href = a.attr('href');
			var rel = a.attr('rel');
			if (rel && rel != '') {
				group = rel;
			}
			if (/(.jpg|.jpeg|.pjpeg|.png|.gif)$/i.test(href)) {
				//full_rel = 'lightbox[' + group + ']';
				//a.attr('rel', full_rel);
				
				if (rel_groups[group]) {
					rel_groups[group]++;
				}
				else {
					rel_groups[group] = 1;
				}

				if (!rel_links[group]) {
					rel_links[group] = new Array;
				}
				rel_links[group].push(a);
				
			}
		}
		
	});
	
	$.each(rel_groups, function(index, value) { 
		var rel = index;
		if (value > 1) {
			rel = 'lightbox[' + rel + ']';
		}
		else {
			rel = 'lightbox_' + rel;
		}
		$.each(rel_links[index], function(i, a) { 
			a.attr('rel', rel);
		});
	});
	
	$(".pc_content a[rel^=lightbox]").assignPrettyPhoto();
}); 