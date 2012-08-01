PC.dialog.media = {
	default_width: 640, default_height: 364,
	show: function(media) {
		var dialog = this;
		dialog.counter = 0; //edit counter
		dialog.media = media;
		dialog.current_media_url = '';
		dialog.current_media = false;
		dialog.current_default_width = dialog.current_default_height = undefined
		dialog.ln = PC.i18n.dialog.media;
		var ln = dialog.ln;
		dialog.preview_empty = '<br /><br />'+ ln.enter_media_url +'<br /><img src="images/player_play.png" alt="" /><br /><br />',
		dialog.bookmark = tinymce.activeEditor.selection.getBookmark('simple');
		
		this.general = {
			title: this.ln.general,
			layout: 'form',
			border: false,
			padding: '6px 3px 0 3px',
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor:'100%', xtype:'textfield'},
			items: [
				// MEDIA URL
				{	fieldLabel: this.ln.url,
					xtype:'trigger', ref: '../../_url',
					triggerClass: 'x-form-search-trigger',
					selectOnFocus: true,
					onTriggerClick: function() {
						var field = this;
						var params = {
							save_fn: function(url, rec, callback, params){
								field.setValue(url);
								field.fireEvent('change');
								callback();
							},
							thumbnail_type: null
						};
						var src = field.getValue();
						if (/^gallery\//.test(src)) {
							params.select_id = src.substring(src.lastIndexOf('/')+1);
						}
						PC.dialog.gallery.show(params);
					},
					listeners: {
						change: function(tf, value, old) {
							dialog.identify(true);
						}
					},
					emptyText: this.ln.put_link_here
				},
				// POSTER
				{	fieldLabel: this.ln.poster,
					xtype:'trigger', ref: '../../_poster',
					triggerClass: 'x-form-search-trigger',
					disabled: true,
					selectOnFocus: true,
					onTriggerClick: function() {
						var field = this;
						var params = {
							save_fn: function(url, rec, callback, params){
								field.setValue(url);
								field.fireEvent('change');
								callback();
							},
							thumbnail_type: null
						};
						var src = field.getValue();
						if (/^gallery\//.test(src)) {
							params.select_id = src.substring(src.lastIndexOf('/')+1);
						}
						PC.dialog.gallery.show(params);
					},
					listeners: {
						change: function(tf, value, old) {
							dialog.identify(true);
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					},
					emptyText: this.ln.put_poster_here
				},
				// PLAYER SKIN
				{	fieldLabel: this.ln.skin,
					xtype:'trigger', ref: '../../_skin',
					triggerClass: 'x-form-search-trigger',
					disabled: true,
					selectOnFocus: true,
					onTriggerClick: function() {
						var field = this;
						var params = {
							save_fn: function(url, rec, callback, params){
								field.setValue(url);
								field.fireEvent('change');
								callback();
							},
							thumbnail_type: null
						};
						var src = field.getValue();
						if (/^gallery\//.test(src)) {
							params.select_id = src.substring(src.lastIndexOf('/')+1);
						}
						PC.dialog.gallery.show(params);
					},
					listeners: {
						change: function(tf, value, old) {
							dialog.identify(true);
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					},
					emptyText: this.ln.put_poster_here
				},
				{	xtype: 'fieldset',
					labelWidth: 120,
					style: {
						padding: '5px 0',
						margin: '0',
						border: '2px solid #D2DCEB'
					},
					items: [
						{	fieldLabel: this.ln.dimensions,
							ref: '../../../_dimensions',
							xtype: 'compositefield',
							defaults: {xtype:'textfield',width:50},
							items: [
								{	ref: '_width',
									xtype: 'numberfield',
									minValue: 1, maxValue: 10000,
									value: this.default_width,
									listeners: {
										change: function(field, value, old) {
											if (dialog.window._proportions.getValue()) {
												var d = dialog.window._dimensions.innerCt;
												var ratio = value/old;
												var new_h = d._height.getValue()*ratio;
												d._height.setValue(Math.round(new_h));
												dialog.window._proportions.el.frame();
											}
										}
									}
								},
								{xtype:'label',width:10,text:'x',style:'padding: 3px 0 0 2px'},
								{	ref: '_height',
									xtype: 'numberfield',
									minValue: 1, maxValue: 10000,
									value: this.default_height,
									listeners: {
										change: function(field, value, old) {
											if (dialog.window._proportions.getValue()) {
												var d = dialog.window._dimensions.innerCt;
												var ratio = value/old;
												var new_w = d._width.getValue()*ratio;
												d._width.setValue(Math.round(new_w));
												dialog.window._proportions.el.frame();
											}
										}
									}
								},
								{	icon: 'images/arrow_undo.png',
									width: 20,
									ref: '_restore',
									xtype: 'button',
									handler: function() {
										var d = dialog.window._dimensions.innerCt;
										d._width.setValue(dialog.Get_default_width());
										d._height.setValue(dialog.Get_default_height());
									}
								}
							]
						},
						{	ref: '../../../_proportions',
							boxLabel: this.ln.proportions,
							checked: true,
							xtype: 'checkbox'
						}
					]
				},
				{	ref: '../../_preview',
					xtype: 'panel',
					html: this.preview_empty,
					style: 'margin-top:3px;text-align: center;'
				}
			]
		};
		this.advanced = {
			title: this.ln.advanced,
			layout: 'form',
			padding: '6px 3px 3px 3px',
			border: false,
			autoScroll: true,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor: '96%', xtype:'textfield'},
			items: [
				{	fieldLabel: this.ln.id,
					ref: '../../_id'
				},
				{	ref: '../../_border',
					fieldLabel: this.ln.border,
					xtype: 'compositefield',
					border: false,
					autoHeight: true,
					style: 'padding:0',
					defaultType: 'textfield',
					defaults: {
						hideLabel: true,
						flex: 1
					},
					items: [
						{	xtype: 'combo', ref: '_size', disabled: true,
							mode: 'local',
							width: 45,
							store: {
								xtype: 'arraystore',
								storeId: 'margin_top',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10]]
							},
							editable: false,
							valueField: 'value',
							displayField: 'display',
							value: 1,
							triggerAction: 'all'
						},
						{	xtype: 'combo', ref: '_style',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_right',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [
									['', ''],
									['solid',this.ln.solid],
									['dotted',this.ln.dotted],
									['dashed',this.ln.dashed]
								]
							},
							tpl: '<tpl for="."><div class="x-combo-list-item">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: function(cb, value, old){
									if (value == '') {
										dialog.window._border.innerCt._size.disable();
										dialog.window._border.innerCt._color.disable();
									} else {
										dialog.window._border.innerCt._size.enable();
										dialog.window._border.innerCt._color.enable();
									}
								},
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'colorfield', ref: '_color', disabled: true,
							regex: /.*/,
							allowBlank: true,
							value: '',
							detectFontColor: function() {
								if (this.menu && this.menu.picker.rawValue) {
									var val = this.menu.picker.rawValue;
								} else {
									var pcol = PC.utils.color2Hex(this.value);
									//if (console && console.log) console.log(pcol);
									if (pcol) {
										var val = [
											parseInt(pcol.slice(0, 2), 16),
											parseInt(pcol.slice(2, 4), 16),
											parseInt(pcol.slice(4, 6), 16)
										];
									} else {
										this.el.setStyle('color', 'black');
										this.el.setStyle('background', 'white');
										return;
									}
								}
								var avg = val[0]*0.299 + val[1]*0.587 + val[2]*0.114;
								this.el.setStyle('color', (avg >= 128) ? 'black' : 'white');
							}
						}
					]
				},
				{	ref: '../../_margin',
					fieldLabel: this.ln.margin,
					xtype: 'compositefield',
					border: false,
					autoHeight:true,
					style: 'padding:0',
					defaultType: 'textfield',
					defaults: {
						hideLabel: true,
						flex: 1
					},
					items: [
						{	xtype: 'combo', ref: '_top',
							style: 'border-top: 2px solid #CC3333;',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_top',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_right',
							style: 'border-right: 2px solid #CC3333;',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_right',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_bottom',
							style: 'border-bottom: 2px solid #CC3333',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_bottom',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_left',
							style: 'border-left: 2px solid #CC3333',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_left',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						}
					]
				},
				{	ref: '../../_style',
					fieldLabel: this.ln.style,
					value: 'text-align:center;'
				}
			]
		};
		this.tabs = {
			xtype: 'tabpanel',
			activeTab: 0,
			flex: 1,
			items: [this.general, this.advanced],
			border: false
		};
		this.window = new Ext.Window({
			title: this.ln.title,
			layout: 'vbox',
			layoutConfig: {
				align: 'stretch'
			},
			width: 445,
			height: 590,
			resizable: false,
			border: false,
			items: this.tabs,
			buttonAlign: 'left',
			buttons: [
				{xtype:'tbfill'},
				{	ref: '../_ok',
					text: this.ln.insert,
					handler: function() {
						dialog.identify();
					}
				},
				{	text: Ext.Msg.buttonText.cancel,
					handler: function() {
						dialog.window.close();
					}
				}
			]
		});
		this.window.show();
		if (media != undefined) {
			//enable update mode
			this.window._ok.setText(this.ln.update);
			this.window.setTitle(this.ln.title_update);
			this.update = true;
			this.media = media;
			//get settings
			var w = media.getAttribute('width');
			var h = media.getAttribute('height');
			var properties = tinymce.activeEditor.plugins.media._parse(media.title)
			var id = properties.id;
			var url = properties.src;
			var poster = properties.poster;
			var skin = properties.skin;
			var style = media.getAttribute('_mce_style');
			if (style) {
				//parse margin
				var margin_start = style.lastIndexOf('margin:');
				if (margin_start >= 0) {
					var margin_end = style.indexOf(';', margin_start);
					var margin = style.substring(margin_start+7, margin_end).replace(/^\s+/,"");
					var sides = margin.split(' ');
					//cut margin from the styles
					style = style.substring(0, margin_start) + style.substring(margin_end+1);
					//update margin values
					margin = dialog.window._margin.innerCt;
					switch (sides.length) {
						case 0:
							margin._top.setValue(0);
							margin._right.setValue(0);
							margin._bottom.setValue(0);
							margin._left.setValue(0);
							break;
						case 1:
							sides[0] = parseInt(sides[0]);
							margin._top.setValue(sides[0]);
							margin._right.setValue(sides[0]);
							margin._bottom.setValue(sides[0]);
							margin._left.setValue(sides[0]);
							break;
						case 2:
							margin._top.setValue(parseInt(sides[0]));
							margin._right.setValue(parseInt(sides[1]));
							margin._bottom.setValue(parseInt(sides[0]));
							margin._left.setValue(parseInt(sides[1]));
							break;
						case 3:
							margin._top.setValue(parseInt(sides[0]));
							margin._right.setValue(parseInt(sides[1]));
							margin._bottom.setValue(parseInt(sides[2]));
							margin._left.setValue(0);
							break;
						default: //4 or more
							margin._top.setValue(parseInt(sides[0]));
							margin._right.setValue(parseInt(sides[1]));
							margin._bottom.setValue(parseInt(sides[2]));
							margin._left.setValue(parseInt(sides[3]));
					}
				}
				//parse border
				var border_start = style.lastIndexOf('border:');
				if (border_start >= 0) {
					var border_end = style.indexOf(';', border_start);
					var border = style.substring(border_start+7, border_end).replace(/^\s+/,"");
					var sides = border.split(' ');
					//update border values
					border = dialog.window._border.innerCt;
					if (sides.length == 3) {
						//cut border from the styles
						style = style.substring(0, border_start) + style.substring(border_end+1);
						border._size.setValue(parseInt(sides[0])).enable();
						border._style.setValue(sides[1]);
						border._color.setValue(sides[2]).enable();
					}
				}
				//parse dimensions
				var start = style.lastIndexOf('width:');
				if (start >= 0) {
					var end = style.indexOf(';', start);
					var w = parseInt(style.substring(start+7, end).replace(/^\s+/,""));
				}
				var start = style.lastIndexOf('height:');
				if (start >= 0) {
					var end = style.indexOf(';', start);
					var h = parseInt(style.substring(start+7, end).replace(/^\s+/,""));
				}
				//prevent "display: block;" from showing up in the field
				style = style.replace(/display:\s*block;/, '');
			}
			//update form
			this.window._dimensions.innerCt._width.setValue(w);
			this.window._dimensions.innerCt._height.setValue(h);
			this.window._id.setValue(id);
			this.window._url.setValue(url);
			this.window._poster.setValue(poster);
			this.window._skin.setValue(skin);
			this.window._url.fireEvent('change', this.window._url, url, this.window._url.getValue());
			this.window._style.setValue((style?style.replace(/^\s+/,"").replace(/\s+$/,""):''));
		}
		else this.update = false;
	},
	Get_default_width: function(){
		return (this.current_default_width!=undefined?this.current_default_width:this.default_width);
	},
	Get_default_height: function(){
		return (this.current_default_height!=undefined?this.current_default_height:this.default_height);
	},
	get_style: function(preview_mode) {
		var styles = 'display:block;';
		if (!preview_mode) {
			//border
			var border_style = this.window._border.innerCt._style.getValue();
			var border_size = this.window._border.innerCt._size.getValue();
			var border_color = this.window._border.innerCt._color.getValue();
			if (border_style != '' && border_style != undefined) {
				if (border_size < 1) border_size = 1;
				if (border_color == '' || border_color == undefined) {
					border_color = '#000';
				}
				styles += 'border:'+border_size+'px '+border_style+' '+border_color+';';
			}
			
			//margin
			var margin = this.window._margin.innerCt;
			var sides = new Array();
			sides[0] = parseInt((margin._top.getValue()>0?margin._top.getValue():0)) + 'px'
			sides[1] = parseInt((margin._right.getValue()>0?margin._right.getValue():0)) + 'px'
			sides[2] = parseInt((margin._bottom.getValue()>0?margin._bottom.getValue():0)) + 'px'
			sides[3] = parseInt((margin._left.getValue()>0?margin._left.getValue():0)) + 'px'
			styles += 'margin:'+sides.join(' ')+';';
			styles += this.window._style.getValue();
		}
		return styles;
	},
	identify: function(preview_mode) {
		var dialog = this;
		var src = this.window._url.getValue();
		
		dialog.counter++;
		
		var from_gallery = new RegExp('^gallery\\/'+ PC.global.ADMIN_DIR +'\\/id\\/[0-9]+$','i').test(src);
		
		dialog.current_media = (src == dialog.current_media_url);
		
		if (src.length && !dialog.current_media) {
			dialog.current_media_url = src;
			if (from_gallery) {
				//src.substring();
				var file_id = src.substring(src.lastIndexOf('/')+1);
				Ext.Ajax.request({
					url: 'ajax.gallery.php?action=get_file',
					method: 'POST',
					params: {id: file_id},
					success: function(result){
						var json_result = Ext.util.JSON.decode(result.responseText);
						if (json_result.success) {
							var normal_src = json_result.filedata.path +(json_result.filedata.path.length?'/':'')+ json_result.filedata.filename;
							dialog.current_media_normal_url = normal_src;
							dialog.post_identify(normal_src, src, preview_mode, true);
						}
						else {
							dialog.show_error(dialog.ln.unable_to_identify);
						}
					},
					failure: function(){
						dialog.show_error(dialog.ln.unable_to_identify);
					}
				});
			}
			else {
				dialog.current_media_normal_url = src;
				dialog.post_identify(src, src, preview_mode, from_gallery);
			}
		}
		else dialog.post_identify(dialog.current_media_normal_url, src, preview_mode, from_gallery);
	},
	post_identify: function(normal_src, src, preview_mode, from_gallery){
		var dialog = this;
		var poster = dialog.window._poster;
		var skin = dialog.window._skin;
		poster.disable();
		skin.disable();
		if (!dialog.current_media) {
			dialog.current_default_width = dialog.current_default_height = undefined;
			if (dialog.counter > 1) {
				this.window._dimensions.innerCt._width.setValue(dialog.Get_default_width());
				this.window._dimensions.innerCt._height.setValue(dialog.Get_default_height());
			}
		}
		
		//get settings
		if (preview_mode) var w = 425, h = 349;
		else {
			var w = this.default_width, h = this.default_height;
			w = this.window._dimensions.innerCt._width.getValue();
			h = this.window._dimensions.innerCt._height.getValue();
		}
		
		var id = this.window._id.getValue();
		var styles = this.get_style(preview_mode);
		
		var extension = normal_src.substring(normal_src.lastIndexOf('.')+1);
		if (from_gallery) var full_src = (preview_mode?PC.global.BASE_URL:'') + src;
		else {
			full_src = src;
			if (!/^http:\/\//.test(full_src)) {
				full_src = 'http://'+ full_src;
			}
		}
		
		if (/^(avi|mpg|mpeg|mp4|wmv|mkv|flv)$/.test(extension)) {
			poster.enable();
			skin.enable();
			if (!preview_mode) {
				var source = '<object '+(id!=undefined?'id="'+ id +'" ':'')+(styles.length >0?'style="'+ styles +'" ':'')+'width="'+ w +'" height="'+ h +'">'
					+'<param name="src" value="'+ full_src +'" />'
					+(poster.getValue().length?'<param name="poster" value="'+ poster.getValue() +'" />':'')
					+(skin.getValue().length?'<param name="skin" value="'+ skin.getValue() +'" />':'')
					+'<embed src="'+ full_src +'" width="'+ w +'" height="'+ h +'"></embed>'
				+'</object>';
			}
			else {
				//src = 'gallery/'+ json_result.filedata.path +'/'+ json_result.filedata.filename;
				var id = 'pc_media_item';
				var source = "<div id=\""+ id +"\" class=\"pc_media_player\" style=\"width:"+ w +"px;height:"+ h +"px;"+ (styles.length >0?styles:'') +"\"></div>";
			}
			dialog.update_code(source, preview_mode);
			if (preview_mode) {
				var flashvars = {
					m: 'video',
					uid: id,
					file: full_src//PC.global.BASE_URL + src
				};
				if (poster.getValue().length) flashvars.poster = PC.global.BASE_URL + poster.getValue();
				if (skin.getValue().length) flashvars.st = PC.global.BASE_URL + skin.getValue();
				
				var params = {id:id, wmode:"transparent", allowFullScreen:"true", allowScriptAccess:"always"};
				new swfobject.embedSWF(PC.global.BASE_URL + "media/uppod/uppod.swf", id, w, h, "9.0.115", false, flashvars, params);
				/*jwplayer(id).setup({
					flashplayer: PC.global.BASE_URL +"media/jwplayer/player.swf",
					file: PC.global.BASE_URL + src,
					height: h,
					width: w
				});*/
			}
		}
		else if (/youtube.com/.test(src)) {
			var start = src.indexOf('v=');
			if (start < 0) {
				start = src.indexOf('v/');
				if (start < 0) {
					start = src.indexOf('embed/');
					if (start < 0) return false;
				}
			}
			else {
				var end = src.indexOf('&', start);
				if (end < 1 ) {
					src = src.substring(start+2);
				}
				else src = src.substring(start+2, end);
				if (src.length < 1) return false;
				src = 'http://www.youtube.com/v/'+ src;
			}
			var source = '<object '+(id!=undefined?'id="'+ id +'" ':'')+(styles.length >0?'style="'+ styles +'" ':'')+'width="'+ w +'" height="'+ h +'">'
				+'<param name="movie" value="'+ src +'"></param>'
				+'<param name="allowFullScreen" value="true"></param>'
				+'<param name="allowscriptaccess" value="always"></param>'
				+'<embed src="'+ src +'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'+ w +'" height="'+ h +'"></embed>'
			+'</object>';
			dialog.update_code(source, preview_mode);
			return source;
		}
		else if (extension == 'swf') {
			var generate_source = function() {
				var source = '<object';
				var attributes = Ext.util.JSON.decode('{'+ dialog.media.title +'}');
				var attributesStr = '';
				var paramEls = '';
				Ext.iterate(attributes, function(name, value){
					attributesStr += ' '+ name +'="'+ value +'"';
					paramEls += '<param name="'+name+'" value="'+value+'" />';
					//el.setAttribute(name, value);
				});
				attributesStr += ' width="'+ w +'"';
				attributesStr += ' height="'+ h +'"';
				
				source += attributesStr;
				source += ' classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"';
				source += ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9.0.115"';
				if (id != undefined) source += ' id="'+ id +'"';
				if (styles.length > 0) source += ' style="'+ styles +'"';

				var embed = '<embed type="application/x-shockwave-flash"'+ attributesStr + ' />';

				source += '>'+ paramEls + embed +'</object>';
				
				dialog.update_code(source, preview_mode);
				return source;
				
				/*var source = '<object classid="" codebase="" '+(id!=undefined?'id="'+ id +'" ':'')+(styles.length >0?'style="'+ styles +'" ':'')+'width="'+ w +'" height="'+ h +'">'
					+'<param name="src" value="'+ full_src +'"></param>'
					+'<embed src="'+ full_src +'" type="application/x-shockwave-flash" width="'+ w +'" height="'+ h +'"></embed>'
				+'</object>';*/
			}
			if (from_gallery && !dialog.current_media) {
				Ext.Ajax.request({
					url: 'ajax.gallery.php?action=get_flash_size',
					method: 'POST',
					params: {path: normal_src},
					success: function(result){
						var json_result = Ext.util.JSON.decode(result.responseText);
						if (json_result.success) {
							w = json_result.width;
							h = json_result.height;
							dialog.current_default_width = w;
							dialog.current_default_height = h;
							if (dialog.counter > 1) {
								dialog.window._dimensions.innerCt._width.setValue(w);
								dialog.window._dimensions.innerCt._height.setValue(h);
							}
							generate_source();
						}
						else generate_source();
					},
					failure: generate_source
				});
			}
			else return generate_source();
		}
		else {
			var err = true;
			dialog.show_error(dialog.ln.not_supported);
			dialog.window._preview.update(dialog.preview_empty);
		}
	},
	show_error: function(err){
		Ext.Msg.show({
			title: this.ln.error,
			msg: err,
			buttons: Ext.Msg.OK
		});
	},
	update_code: function(source, preview_mode) {
		//alert(source);
		//alert(preview_mode);
		var dialog = this;
		//update preview
		if (preview_mode) return dialog.window._preview.update(source);
		//insert generated object
		if (tinymce.isIE) tinymce.activeEditor.selection.moveToBookmark(dialog.bookmark);
		if (dialog.update) {
			dialog.media.parentNode.removeChild(dialog.media);
		}
		//if (!tinymce.isIE) tinymce.activeEditor.selection.moveToBookmark(dialog.bookmark);
		tinymce.activeEditor.execCommand('mceInsertContent', false, source);
		dialog.window.close();
	}
};