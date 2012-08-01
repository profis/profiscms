/**
 * Licence:
 * You can use the Code as you like, only the URL http//www.thomas-lauria/ has to be in the used Files / Code 
 * @author Thomas Lauria
 * http://www.thomas-lauria.de
 * Heavily edited by ProfisCMS
 */

Ext.ns('Ext.ux');
Ext.ux.ImageCrop = Ext.extend(Ext.Panel, {
  quadratic: false,
  minWidth: 20,
  minHeight: 20,
  preserveRatio: true,
  cropData: {
    x: 0,
    y: 0,
    height: 0,
    width: 0
  },
  initComponent: function() {
    this.preserveRatio = this.quadratic || this.preserveRatio;
    Ext.ux.ImageCrop.superclass.initComponent.call(this);
  },
  onRender: function(ct, position){
    var c = {};
    if(this.quadratic) {
      c.width = c.height = Math.min(this.initialWidth, this.initialHeight);
      this.maxWidth = this.maxHeight = c.height;
    }
    else {
      this.maxWidth = c.width = this.initialWidth;
      this.maxHeight = c.height = this.initialHeight;
    }
    this.cropData.width = this.minWidth;
    this.cropData.height = this.minHeight;
    Ext.ux.ImageCrop.superclass.onRender.call(this, ct, position);
	this.el.setStyle({
      position: 'relative'
    }).setSize(this.initialWidth,this.initialHeight);
    //resize target (static dimensions)
    this.cropWrapper = this.el.insertFirst().setSize(this.initialWidth,this.initialHeight);
    // resizable (dynamic dimensions)
    this.cropWrapped = this.cropWrapper.insertFirst().setSize(this.minWidth, this.minHeight);
	this.cropWrapped.insertFirst({tag: "img", src: Ext.BLANK_IMAGE_URL, width: this.minWidth, height: this.minHeight});
	// show width & height of the crop area
	this.tip = new Ext.ToolTip({
        target: this.cropWrapper,
        title: this.originalImageSize['width'] +'x'+ this.originalImageSize['height'],
        trackMouse: true,
		autoHide: false,
        draggable: true,
		closable: false
    });
	this.cropWrapper.addListener('mouseleave', function() {
		this.tip.hide();
	}, this);
	// enable double click save-crop-area function
	this.cropWrapper.addListener('dblclick', function() {
		this.fireEvent('cropAreaDoubleClick');
	}, this);
	
    this.cropBgBox = this.el.insertFirst().setStyle({
      background: 'url('+this.imageUrl+') no-repeat left top',
      position: 'absolute',
      left: 0,
      top: 0
    }).setSize(this.initialWidth,this.initialHeight).setOpacity(0.5);
	this.cropWrapper.addListener('mouseover', function() {
		// show final dimensions of the thumbnail after all the actions has been taken
		if (this.cropData.width > this.cropData.height) {
			// ratio of the resizing from the crop area
			var ratio = this.cropData.width / this.thumbnailDimensions.width;
			// resized thumbnail dimensions
			var width = this.thumbnailDimensions.width;
			var height = Math.floor(this.cropData.height / ratio);
		} else {
			// ratio of the resizing from the crop area
			var ratio = this.cropData.height / this.thumbnailDimensions.height;
			// resized thumbnail dimensions
			var width = Math.ceil(this.cropData.width / ratio);
			var height = this.thumbnailDimensions.height;
		}
		if (width > this.thumbnailDimensions.width) {
			ratio = width / this.thumbnailDimensions.width;
			width = this.thumbnailDimensions.width;
			height = Math.ceil(height / ratio);
		} else if (height > this.thumbnailDimensions.height) {
			ratio = height/this.thumbnailDimensions.height;
			width = Math.floor(width / ratio);
			height = this.thumbnailDimensions.height;
		}
		this.tip.setTitle(width +'x'+ height);
	}, this);
    this.initWrapper();
  },
  getCropData: function() {
    return this.cropData;
  },
  initWrapper: function() {
    var parentBox = this;
    var cropBgBox = this.cropBgBox;
    var imageUrl = this.imageUrl;
    var result = this.cropData;
	var croppingImageRatio = this.croppingImageRatio;
    var wrapped = new Ext.Resizable(this.cropWrapped, {
      pinned: true,
      minWidth: this.minWidth,
      minHeight: this.minHeight,
      maxWidth: this.maxWidth,
      maxHeight: this.maxHeight,
      draggable: true,
      preserveRatio: this.preserveRatio,
      handles: 'all',
      constrainTo: this.cropWrapper,
      listeners: {
        'resize': function (box, w, h) {
		  /*alert('Resized: '+ w +'x'+ h);
		  if (croppingImageRatio < 1) {
			alert('Original: '+ w/croppingImageRatio +'x'+ h/croppingImageRatio);
		  }*/
          box.imageOffset = [box.el.getBox().x - cropBgBox.getX(), box.el.getBox().y - cropBgBox.getY()];
		  if (croppingImageRatio < 1) {
		    result.width = Math.ceil(w/croppingImageRatio);
            result.height = Math.ceil(h/croppingImageRatio);
			result.x = Math.ceil(box.imageOffset[0]/croppingImageRatio);
            result.y = Math.ceil(box.imageOffset[1]/croppingImageRatio);
		  } else {
			result.width = w;
            result.height = h;
			result.x = box.imageOffset[0];
            result.y = box.imageOffset[1];
		  }
          box.el.setStyle({
            'background-image':'url('+imageUrl+')',
            'background-position':(-box.imageOffset[0])+'px '+(-box.imageOffset[1])+'px'
          });
          if (parentBox.fireEvent('change', parentBox, result) === false) {
			return parentBox;
          }
        },
        'beforeresize': function () {
          this.getEl().setStyle({background:'transparent'});
        }
      },
      dynamic: true
    });
	this.wrapped = wrapped;
    wrapped.getEl().setStyle({background:'url('+imageUrl+')'});
    wrapped.imageOffset = [0,0];
    wrapped.dd.endDrag = function(){
      wrapped.imageOffset = [wrapped.getEl().getBox().x - cropBgBox.getX(), wrapped.getEl().getBox().y - cropBgBox.getY()];
      if (croppingImageRatio < 1) {
		result.x = Math.ceil(wrapped.imageOffset[0]/croppingImageRatio);
        result.y = Math.ceil(wrapped.imageOffset[1]/croppingImageRatio);
	  } else {
	    result.x = wrapped.imageOffset[0];
        result.y = wrapped.imageOffset[1];
	  }
      wrapped.getEl().setStyle({
        'background-image':'url('+imageUrl+')',
        'background-position':(-wrapped.imageOffset[0])+'px '+(-wrapped.imageOffset[1])+'px'
      });
      if(parentBox.fireEvent('change', parentBox, result) === false){
        return parentBox;
      }
    };
    wrapped.dd.startDrag = function(x, y){
        wrapped.getEl().setStyle({
        'background':'transparent'
      });
    };
	
	if (this.crop_data[0] != undefined) {
		//console.log('Crop data nurodyta:');
		//console.log(this.crop_data);
		var crop_data_proportions_ratio = this.crop_data[2]/this.crop_data[3];
		if (this.preserveRatio) {
			//console.log('preserveRatio = true');
			//console.log(this.thumbnailType);
			var thumb_proportions_ratio = this.thumbnailType.thumbnail_max_w / this.thumbnailType.thumbnail_max_h;
			//console.log('crop_data_proportions_ratio: '+ crop_data_proportions_ratio);
			//console.log('thumb_proportions_ratio: '+ thumb_proportions_ratio);
			//as close as possible
			if (crop_data_proportions_ratio > thumb_proportions_ratio) {
				//console.log('Mazinam ploti');
				//cia reikia mazinti width
				this.crop_data[2] = Math.ceil(this.crop_data[2]/crop_data_proportions_ratio*thumb_proportions_ratio)+'';
				//this.crop_data[2] = Math.ceil(this.thumbnailType.thumbnail_max_w/thumb_proportions_ratio*crop_data_proportions_ratio)+'';
				/*
				//var t_h_px_value = this.thumbnailType.thumbnail_max_h/thumb_proportions_ratio;
				var cd_h_px_value = this.crop_data[3]/thumb_proportions_ratio;
				console.log('cd_h_px_value: '+ cd_h_px_value);
				this.crop_data[3] = Math.ceil(cd_h_px_value * crop_data_proportions_ratio)+'';
				*/
			}
			else if (crop_data_proportions_ratio < thumb_proportions_ratio) {
				//console.log('Mazinam auksti');
				//cia reikia mazinti height
				this.crop_data[3] = Math.ceil(this.crop_data[3]/thumb_proportions_ratio*crop_data_proportions_ratio)+'';
				//this.crop_data[3] = Math.ceil(this.thumbnailType.thumbnail_max_h/thumb_proportions_ratio*crop_data_proportions_ratio)+'';
				/*
				var cd_w_px_value = this.crop_data[2]/thumb_proportions_ratio;
				console.log('cd_w_px_value: '+ cd_w_px_value);
				this.crop_data[2] = Math.ceil(cd_w_px_value * crop_data_proportions_ratio)+'';*/
			}
			
			//we need to modify axis data if crop area sticks out of image area
			var ca_outter_w = (parseInt(this.crop_data[0]) + parseInt(this.crop_data[2]));
			var ca_outter_h = (parseInt(this.crop_data[1]) + parseInt(this.crop_data[3]));
			/*//check y axis
			if (ca_outter_h > this.originalImageSize.height) {
				console.log('move y axis up');
				//move y axis up
				var difference = Math.ceil(ca_outter_h - this.originalImageSize.height);
				this.crop_data[1] = this.crop_data[1] - difference + '';
			}
			//check x axis
			if (ca_outter_w > this.originalImageSize.width) {
				console.log('move x axis up');
				//move x axis up
				var difference = Math.ceil(ca_outter_w - this.originalImageSize.width);
				this.crop_data[0] = this.crop_data[0] - difference + '';
			}*/
			
			var resize_to_w = Math.ceil(this.crop_data[2]*this.croppingImageRatio);
			var resize_to_h = Math.ceil(this.crop_data[3]*this.croppingImageRatio);
			
			//console.log('Modified crop data:');
			//console.log(this.crop_data);
		}
		else {
			//console.log('preserveRatio = false');
			var resize_to_w = this.crop_data[2]*this.croppingImageRatio;
			var resize_to_h = this.crop_data[3]*this.croppingImageRatio;
		}
		//set position
		var xy = this.cropWrapped.getXY();
		this.cropWrapped.setXY([
			xy[0]+Math.ceil(this.crop_data[0]*this.croppingImageRatio),
			xy[1]+Math.ceil(this.crop_data[1]*this.croppingImageRatio)
		]);
		//set size
		wrapped.resizeTo(resize_to_w, resize_to_h);
		setTimeout(function(){
			//parentBox.getEl().highlight();
			parentBox.cropWrapped.frame();
		}, 300);
	}
	else if (this.preserveRatio) {
		//console.log('crop data nenurodyta, preserveRatio = true');
		var rated_thumb_w = this.thumbnailType.thumbnail_max_w * this.croppingImageRatio;
		var rated_thumb_h = this.thumbnailType.thumbnail_max_h * this.croppingImageRatio;
		//console.log('Rated thumb: '+ rated_thumb_w +' x '+ rated_thumb_h);
		var proportions_w_ratio = this.initialWidth / rated_thumb_w;
		var proportions_h_ratio = this.initialHeight / rated_thumb_h;
		//console.log('Proportions of width = '+ proportions_w_ratio +' and height = '+ proportions_h_ratio);
		rated_width = rated_thumb_w * Math.min(proportions_w_ratio, proportions_h_ratio);
		rated_height = rated_thumb_h * Math.min(proportions_w_ratio, proportions_h_ratio);
		//console.log('Rated: '+ rated_width +' x '+ rated_height);
		wrapped.resizeTo(Math.ceil(rated_width), Math.ceil(rated_height));
	}
  }
});
