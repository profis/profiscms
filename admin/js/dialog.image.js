Ext.ns('PC.dialog');
PC.dialog.image = {
	show: function(image) {
		this.ln = PC.i18n.dialog.image;
		var dialog = this;
		this.image = image;
		this.general = {
			title: this.ln.general,
			layout: 'form',
			border: false,
			padding: '6px 12px 0 3px',
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor:'100%', xtype:'textfield'},
			items: [
				{	fieldLabel: this.ln.image_url,
					xtype:'trigger', ref: '../../_url',
					triggerClass: 'x-form-search-trigger',
					onTriggerClick: function() {
						var field = this;
						var params = {
							callee: 'image',
							save_fn: function(url, rec, callback, params){
								field.setValue(url);
								callback();
							}
						};
						var src = dialog.window._url.getValue();
						if (/^gallery\//.test(src)) {
							params.select_id = src.substring(src.lastIndexOf('/')+1);
						}
						PC.dialog.gallery.show(params);
					}/*,
					listeners: {
						change: function(field, value, old) {
							var img = new Image();
							img.src = PC.global.BASE_URL +value;
							img.onload = (function(){
								alert('new dimensions: '+ img.width +'x'+ img.height);
							});
						}
					}*/
				},
				{	boxLabel: this.ln.no_large_on_click,
					xtype: 'checkbox', ref: '../../_nopopup'
				},
				{fieldLabel: this.ln.title, ref: '../../_title'},
				{	fieldLabel: this.ln.description, ref: '../../_alt',
					xtype: 'textarea', height: 32
				},
				{	fieldLabel: this.ln.alignment,
					ref: '../../_align',
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['align', 'display'],
						idIndex: 0,
						data: [
							['', ''],
							['left', this.ln.left],
							['right', this.ln.right]
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="text-align:{align}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'align',
					value: '',
					triggerAction: 'all'
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
									value: 800,
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
									value: 600,
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
										d._width.setValue(dialog.image.naturalWidth);
										d._height.setValue(dialog.image.naturalHeight);
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
				{	ref: '../../_class',
					fieldLabel: this.ln._class,
					xtype: 'combo', 
					mode: 'local',
					store: PC.utils.Get_classes_array('img'),
					triggerAction: 'all'
				},
				{	fieldLabel: this.ln.style,
					ref: '../../_style'
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
								change: dialog.margin_changed,
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
								change: dialog.margin_changed,
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
								change: dialog.margin_changed,
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
								change: dialog.margin_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						}
					]
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
			title: this.ln.image_properties,
			layout: 'vbox',
			layoutConfig: {
				align: 'stretch'
			},
			width: 400,
			height: 300,
			resizable: false,
			border: false,
			items: this.tabs,
			buttonAlign: 'left',
			buttons: [
				{xtype:'tbfill'},
				{	text: Ext.Msg.buttonText.ok,
					handler: function() {
						//check if image previously had lightbox
						dialog.has_lightbox();
						//save settings
						var parent = dialog.image.parentNode;
						var url = dialog.window._url.getValue();
						if (dialog.had_lightbox) {
							var big_url = PC.dialog.gallery.get_large_url(url);
							parent.setAttribute('href', big_url);
							parent.setAttribute('_mce_href', big_url);
						}
						//image src
						dialog.image.setAttribute('src', url);
						dialog.image.setAttribute('_mce_src', url);
						//enable lightbox?
						if (dialog.window._nopopup.getValue()) {
							if (dialog.had_lightbox) {
								//turejo lightboxa bet dabar turi jo nebebuti. trinam A taga is sonu ir tiek
								removeParent(parent);
							}
						}
						else {
							if (!dialog.had_lightbox) {
								//img neturejo linko i didesni savo img varianta, bet galbut turejo linka i ka nors kita.
								//o dabar dialoge buvo paspausta, kad butu naudojamas lightboxas, ka daryti? istrinti sena linka ir vietoj jo prideti lightboxa?
								if (!dialog.had_link) {
									//apgaubti su nauju linku i ligthboxa
									var link = document.createElement('a');
									link.setAttribute('href', PC.dialog.gallery.get_large_url(url));
									parent.insertBefore(link, dialog.image);
									link.appendChild(dialog.image);
									//parent.removeChild(dialog.image);
									dialog.image = link.firstChild;
									parent = dialog.image.parentNode;
								}
							}
						}
						//other
						dialog.image.setAttribute('title', dialog.window._title.getValue());
						dialog.image.setAttribute('alt', dialog.window._alt.getValue());
						//dimensions
						dialog.image.width = dialog.window._dimensions.innerCt._width.getValue();
						dialog.image.height = dialog.window._dimensions.innerCt._height.getValue();
						//advanced
						dialog.image.setAttribute('class', dialog.window._class.getValue());
						dialog.image.setAttribute('id', dialog.window._id.getValue());
						var style = dialog.window._style.getValue();
						//alignment
						if (dialog.window._align.getValue() != '') {
							style = 'float:'+dialog.window._align.getValue()+';'+style;
						}
						//border
						if (dialog.window._border.innerCt._style.getValue() != '') {
							style = 'border:'+ dialog.window._border.innerCt._size.getValue()+'px '+dialog.window._border.innerCt._style.getValue()+' '+dialog.window._border.innerCt._color.getValue()+';'+style;
						}
						//margin
						var margin = dialog.window._margin.innerCt;
						var sides = new Array();
						sides[0] = parseInt((margin._top.getValue()>0?margin._top.getValue():0));
						sides[1] = parseInt((margin._right.getValue()>0?margin._right.getValue():0));
						sides[2] = parseInt((margin._bottom.getValue()>0?margin._bottom.getValue():0));
						sides[3] = parseInt((margin._left.getValue()>0?margin._left.getValue():0));
						if (sides[0] > 0 || sides[1] > 0 || sides[2] > 0 || sides[3] > 0) {
							var margin_sheet = 'margin:'+sides.join('px ')+'px';
							style = margin_sheet+';'+style;
						}
						dialog.image.setAttribute('style', style);
						dialog.image.setAttribute('_mce_style', style);
						tinymce.activeEditor.addVisual();
						dialog.window.close();
					}
				},
				{	text: Ext.Msg.buttonText.cancel,
					handler: function() {
						dialog.window.close();
					}
				}
			]
		});
		dialog.window.show();
		if (dialog.image) {
			dialog.window._url.setValue(dialog.getAttrib(dialog.image, 'src'));
			//check if lightbox is currently enabled
			if (!dialog.has_lightbox()) {
				this.window._nopopup.setValue(true);
				if (dialog.had_link) {
					this.window._nopopup.disable();
				}
			}
			dialog.window._title.setValue(dialog.getAttrib(dialog.image, 'title'));
			dialog.window._alt.setValue(dialog.getAttrib(dialog.image, 'alt'));
			dialog.window._dimensions.innerCt._width.setValue(dialog.image.width);
			dialog.window._dimensions.innerCt._height.setValue(dialog.image.height);
			dialog.window._class.setValue(dialog.getAttrib(dialog.image, 'class'));
			dialog.window._id.setValue(dialog.getAttrib(dialog.image, 'id'));
			//parse style
			var style = dialog.getAttrib(dialog.image, 'style');
			//parse alignment
			var float_start = style.lastIndexOf('float:');
			if (float_start >= 0) {
				var float_end = style.indexOf(';', float_start);
				var _float = style.substring(float_start+6, float_end).replace(/^\s+/,"");
				var sides = _float.split(' ');
				//cut floating from the styles
				style = style.substring(0, float_start) + style.substring(float_end+1);
				dialog.window._align.setValue(_float);
			}
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
						margin._top.setValue(parseInt(sides[0]));
						margin._right.setValue(parseInt(sides[0]));
						margin._bottom.setValue(parseInt(sides[0]));
						margin._left.setValue(parseInt(sides[0]));
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
			//set style
			this.window._style.setValue(style.replace(/^\s+/,"").replace(/\s+$/,""));
		}
	},
	has_lightbox: function() {
		var dialog = this;
		dialog.had_lightbox = false;
		dialog.had_link = false;
		var parent = dialog.image.parentNode;
		if (parent.nodeName == 'A') {
			dialog.had_link = true;
			/*if link href and image src is equal, then we know that this is lightbox link
			  which should be updated accordingly to the image src*/
			var href = parent.getAttribute('href');
			var gallery_link = false;
			if (href.substr(0, 8)=='gallery/') gallery_link = true;
			else if (href.substr(0, PC.global.BASE_URL.length+8) == PC.global.BASE_URL+'gallery/') gallery_link = true;
			if (gallery_link) {
				var src = dialog.image.getAttribute('src');
				var src_id = src.substr(src.lastIndexOf('/'));
				var href_id = href.substr(href.lastIndexOf('/'));
				if (src_id == href_id) {
					dialog.had_lightbox = true;
				}
			}
		}
		return dialog.had_lightbox;
	},
	margin_changed: function(cb, val, oldval) {
		var current = cb.ref;
		var dialog = PC.dialog.image;
		var margin = dialog.window._margin.innerCt;
	},
	getAttrib: function(e, at) {
		var ed = tinymce.activeEditor, dom = ed.dom, v, v2;

		if (ed.settings.inline_styles) {
			switch (at) {
				case 'align':
					if (v = dom.getStyle(e, 'float'))
						return v;

					if (v = dom.getStyle(e, 'vertical-align'))
						return v;

					break;

				case 'hspace':
					v = dom.getStyle(e, 'margin-left')
					v2 = dom.getStyle(e, 'margin-right');

					if (v && v == v2)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;

				case 'vspace':
					v = dom.getStyle(e, 'margin-top')
					v2 = dom.getStyle(e, 'margin-bottom');
					if (v && v == v2)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;

				case 'border':
					v = 0;

					tinymce.each(['top', 'right', 'bottom', 'left'], function(sv) {
						sv = dom.getStyle(e, 'border-' + sv + '-width');

						// False or not the same as prev
						if (!sv || (sv != v && v !== 0)) {
							v = 0;
							return false;
						}

						if (sv)
							v = sv;
					});

					if (v)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;
			}
		}

		if (v = dom.getAttrib(e, at))
			return v;

		return '';
	}
};