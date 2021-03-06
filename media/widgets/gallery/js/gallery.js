(function($) {
	var defaultOptions = {
	};

	var getWidgetData = function($widget) {
		var data = $widget.data('pc_gallery_widget');
		if( data == null ) {
			data = {};
			data.$thumbsWrap = $widget.find('.pcgw-thumbs-wrap2');
			data.$thumbs = data.$thumbsWrap.find('.pcgw-thumbs');
			data.$highlighter = data.$thumbs.find('.pcgw-highlighter-wrap');
			data.$imageWrap = $widget.find('.pcgw-image-wrap');
			$widget.data('pc_gallery_widget', data);
		}
		return data;
	};

	var selectImage = function($widget, index, transition) {
		if( typeof(transition) != 'string' )
			transition = 'fade';
		var data = getWidgetData($widget);

		data.$thumbs.find('.active').removeClass('active');
		var $thumb = data.$thumbs.find('.pcgw-thumb:nth(' + Math.round(index) + ')');
		$thumb.addClass('active');

		var pos = $thumb.position();
		data.$highlighter.css({left: pos.left + 'px', top: pos.top + 'px'});

		var $currentImg = data.$imageWrap.find('.pcgw-image:not(.pcgw-disposed)');

		$currentImg.addClass('.pcgw-disposed').css({opacity: 0});
		setTimeout(function() { $currentImg.remove(); }, 10000); // remove disposed images in 10 seconds

		var $newImg = $('<div />').addClass('pcgw-image').css({
			opacity: 0,
			'background-size': $widget.data('previewmode'),
			'background-image': 'url(' + $thumb.data('preview') + ')'
		});

		switch(transition) {
			default:
				$newImg.css({left: 0, top: 0});
		}

		data.$imageWrap.append($newImg);
		data.$imageWrap.offset(); // force redrawing
		$newImg.css({opacity: '', left: '0', top: '0'});

		$widget.trigger('pc.widgets.gallery.imagechanged');
	};

	var selectPrevImage = function($widget) {
		var data = getWidgetData($widget);
		var count = data.$thumbs.find('.pcgw-thumb').length;
		if( count > 0 ) {
			var index = getSelectedIndex($widget) - 1;
			if( index < 0 )
				index = count - 1;
			selectImage($widget, index);
		}
	};

	var selectNextImage = function($widget) {
		var data = getWidgetData($widget);
		var count = data.$thumbs.find('.pcgw-thumb').length;
		if( count > 0 ) {
			var index = getSelectedIndex($widget) + 1;
			if( index >= count )
				index = 0;
			selectImage($widget, index);
		}
	};

	var getSelectedIndex = function($widget) {
		return getSelectedThumbnail($widget).index();
	};

	var getSelectedThumbnail = function($widget) {
		var data = getWidgetData($widget);
		return data.$thumbs.find('.active');
	};

	var zoomImageIn = function($widget) {
		var $thumb = getSelectedThumbnail($widget);
		$thumb.find('a').click();
	};

	$.fn.pc_gallery_widget = function() {
		if( typeof(arguments[0]) == 'string' ) {
			var args = arguments;
			switch(args[0]) {
				case 'selectImage':
					$(this).each(function() {
						selectImage($(this).closest('.pc_gallery'), args[1], args[2]);
					});
					break;
				case 'nextImage':
					$(this).each(function() {
						selectNextImage($(this).closest('.pc_gallery'));
					});
					break;
				case 'previousImage':
					$(this).each(function() {
						selectPrevImage($(this).closest('.pc_gallery'));
					});
					break;
				case 'getSelectedIndex':
					var ret = null;
					$(this).each(function() {
						ret = getSelectedIndex($(this).closest('.pc_gallery'));
					});
					return ret;
				case 'zoomIn':
					var ret = null;
					$(this).each(function() {
						ret = zoomImageIn($(this).closest('.pc_gallery'));
					});
					return ret;
			}
		}
		else {
			var options = $.extend(defaultOptions, arguments[0]);
			this
				.each(function() {
					var $widget = $(this);
					var data = getWidgetData($widget);
					var $thumb = getSelectedThumbnail($widget);
					var pos = $thumb.position();
					data.$highlighter.css({left: pos.left + 'px', top: pos.top + 'px', display: 'block'});
				})
				.on('click', '.pcgw-preview .pcgw-left', function(e) {
					e.preventDefault();
					e.stopPropagation();
					var $widget = $(this).closest('.pc_gallery');
					selectPrevImage($widget);
				})
				.on('click', '.pcgw-preview .pcgw-right', function(e) {
					e.preventDefault();
					e.stopPropagation();
					var $widget = $(this).closest('.pc_gallery');
					selectNextImage($widget);
				})
				.on('click', '.pcgw-controls .pcgw-left', function(e) {
					e.preventDefault();
					e.stopPropagation();
					var data = getWidgetData($(e.target).closest('.pc_gallery'));
					var pos = data.$thumbs.position();
					if( pos.left < 0 ) {
						pos.left += Math.round(data.$thumbsWrap.width() * 0.3);
						if( pos.left > 0 )
							pos.left = 0;
						data.$thumbs.css({left: pos.left + 'px'});
					}
				})
				.on('click', '.pcgw-controls .pcgw-right', function(e) {
					e.preventDefault();
					e.stopPropagation();
					var data = getWidgetData($(e.target).closest('.pc_gallery'));
					var pos = data.$thumbs.position();
					var w = data.$thumbsWrap.width();
					var min = w - data.$thumbs.width();
					if( pos.left > min ) {
						pos.left -= Math.round(w * 0.3);
						if( pos.left <= min )
							pos.left = min;
						data.$thumbs.css({left: pos.left + 'px'});
					}
				})
				.on('click', '.pcgw-thumb', function(e) {
					e.preventDefault();
					e.stopPropagation();
					e.returnValue = false;
					var $thumb = $(e.target);
					var $widget = $thumb.closest('.pc_gallery');
					selectImage($widget, $thumb.index());
					return false;
				})
				.on('click', '.pcgw-zoom', function(e) {
					e.preventDefault();
					e.stopPropagation();
					var $widget = $(this).closest('.pc_gallery');
					zoomImageIn($widget);
				})
				.on('touchstart', '.pcgw-thumbs', function(e) {
					e.preventDefault();
					e.stopPropagation();
					e.returnValue = false;
					var touches = e.originalEvent.changedTouches;
					var $this = $(this);
					$this.data('touchOffset', touches[0].pageX);
					$this.data('touchOriginalPos', $this.position().left);
					$this.css({
						'transition': 'none',
						'-o-transition': 'none',
						'-ms-transition': 'none',
						'-moz-transition': 'none',
						'-webkit-transition': 'none'
					});
				})
				.on('touchmove', '.pcgw-thumbs', function(e) {
					e.preventDefault();
					e.stopPropagation();
					e.returnValue = false;
					var touches = e.originalEvent.changedTouches;
					var $this = $(this);
					var offs = $this.data('touchOffset');
					if( offs ) {
						var delta = touches[0].pageX - offs;
						var pos = $this.data('touchOriginalPos') + delta;
						var min = $this.closest('.pcgw-thumbs-wrap2').width() - $this.width();
						if( pos < min )
							pos = min;
						if( pos > 0 )
							pos = 0;

						$this.css({left: pos + 'px'});
					}
				})
				.on('touchend', '.pcgw-thumbs', function(e) {
					e.preventDefault();
					e.stopPropagation();
					e.returnValue = false;
					var touches = e.originalEvent.changedTouches;
					var $this = $(this);
					var offs = $this.data('touchOffset');
					$this.data('touchOffset', null);
					$this.data('touchOriginalPos', null);
					$this.css({
						'transition': '',
						'-o-transition': '',
						'-ms-transition': '',
						'-moz-transition': '',
						'-webkit-transition': ''
					});
					if( Math.abs(touches[0].pageX - offs) < 8 ) {
						var pos = touches[0].pageX - $this.offset().left;
						$this.find('.pcgw-thumb').each(function() {
							var $thumb = $(this);
							var x = $thumb.position().left;
							var w = $thumb.width();
							if( pos >= x && pos < x + w ) {
								$thumb.trigger('click');
								return false;
							}
						});
					}
				});
		}
		return this;
	};

	$(function() {
		$('.pc_gallery').pc_gallery_widget();
	});
})(jQuery);

