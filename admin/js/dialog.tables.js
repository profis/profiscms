Ext.ns('PC.dialog');
PC.dialog.tables = {
	cell_side: 30, chooser_cols: 8, chooser_rows: 8,
	chooser_cols_start: 2, chooser_rows_start: 2,
	chooser_body_style: 'background: url(images/table_cell.jpg);',
	show: function(action, editor) {
		this.ln = PC.i18n.dialog.tables;
		var dialog = this;
		//edit mode
		this.edit_mode = false;
		this.table = editor.dom.getParent(editor.selection.getNode(), "table");
		if (action != 'insert') this.edit_mode = this.table?true:false;
		this.chooser_width = this.chooser_cols*this.cell_side;
		this.chooser_height = this.chooser_rows*this.cell_side;
		var dialog = this;
		this.options = this.default_options;
		var options = this.options;
		this.size_chooser = new Ext.Panel({
			layout: 'fit',
			region: 'west',
			width: this.chooser_width+10,
			border: false,
			bodyStyle: 'border: 5px solid #CED9E7',
			items: [
				{	xtype: 'panel',
					border: false,
					bodyStyle: 'cursor:pointer;'+dialog.chooser_body_style,
					items: [
						{	xtype: 'panel',
							ref: '../_chooser',
							border: false,
							bodyStyle: 'background: transparent',
							listeners: {
								render: function(panel) {
									var resizable_area = this.getEl();
									var chooser = new Ext.Resizable(resizable_area, {
										enabled: !dialog.edit_mode,
										pinned: true, handles: 'all',
										//increment
										widthIncrement: dialog.cell_side,
										heightIncrement: dialog.cell_side,
										//min values
										minWidth: dialog.cell_side,
										minHeight: dialog.cell_side,
										//max values
										maxWidth: dialog.chooser_width,
										maxHeight: dialog.chooser_height,
										//init dimensions
										width: dialog.chooser_cols_start*dialog.cell_side,
										height: dialog.chooser_rows_start*dialog.cell_side,
										//other
										dynamic: true,
										bodyStyle: dialog.chooser_body_style,
										listeners: {
											resize: function (chooser, w, h) {
												if (chooser.el.getBox().x != chooser.el.parent().parent().getBox().x
													|| chooser.el.getBox().y != chooser.el.parent().parent().getBox().y ) {
													chooser.el.setXY(chooser.el.parent().parent().getBox());
												}
												dialog.form._rowscols.innerCt._columns.setValue(w/dialog.cell_side);
												dialog.form._rowscols.innerCt._rows.setValue(h/dialog.cell_side);
											}
										}
									});
								}
							}
						}
					],
					listeners: {
						render: function(panel) {
							panel.getEl().addListener('click', function(event, element) {
								var el_xy = Ext.get(element).getXY();
								var width = event.xy[0] - el_xy[0];
								var height = event.xy[1] - el_xy[1];
								var a = Math.round(width/dialog.cell_side);
								var b = Math.round(height/dialog.cell_side);
								//update form fields
								dialog.form._rowscols.innerCt._columns.setValue(a);
								dialog.form._rowscols.innerCt._rows.setValue(b);
								//update chooser dimensions
								var chooser = dialog.size_chooser._chooser.getEl();
								chooser.setWidth(a*dialog.cell_side);
								chooser.setHeight(b*dialog.cell_side);
							});
						}
					}
				}
			]
		});
		this.form = new Ext.FormPanel({
			region: 'center',
			border: false,
			padding: 15,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {
				fieldLabel: '',
				xtype: 'textfield',
				anchor: '100%'
			},
			items: [
				{	ref: '_rowscols',
					fieldLabel: dialog.ln.cols,
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
						{	ref: '_columns',
							xtype: 'numberfield',
							allowDecimals: false,
							value: dialog.chooser_cols_start,
							listeners: {
								change: function(field, value, old){
									if (value < 1) {
										field.setValue(1);
										var width = dialog.cell_side;
									} else var width = value * dialog.cell_side;
									var max = dialog.chooser_cols*dialog.cell_side;
									if (width > max) width = max;
									var chooser = dialog.size_chooser._chooser.getEl();
									chooser.setWidth(width);
								}
							}
						},
						{xtype:'label',text: this.ln.rows+':', width:30, style:'padding-top:4px'},
						{	ref: '_rows',
							xtype: 'numberfield',
							allowDecimals: false,
							value: dialog.chooser_rows_start,
							listeners: {
								change: function(field, value, old){
									if (value < 1) {
										field.setValue(1);
										var height = dialog.cell_side;
									} else var height = value * dialog.cell_side;
									var max = dialog.chooser_rows*dialog.cell_side;
									if (height > max) height = max;
									var chooser = dialog.size_chooser._chooser.getEl();
									chooser.setHeight(height);
								}
							}
						}
					]
				},
				{	ref: '_wh',
					fieldLabel: this.ln.width +', '+ this.ln.height,
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
						{	ref: '_width',
							fieldLabel: this.ln.width,
							value: 100,
							xtype: 'numberfield',
							allowDecimals: false
						},
						{	ref: '_width_unit',
							xtype: 'combo', 
							mode: 'local',
							store: ['px','%'],
							value: '%',
							editable: false,
							triggerAction: 'all'
						},
						{	ref: '_height',
							fieldLabel: this.ln.height,
							xtype: 'numberfield',
							allowDecimals: false
						},
						{	ref: '_height_unit',
							xtype: 'combo', 
							mode: 'local',
							store: ['px','%'],
							value: 'px',
							editable: false,
							triggerAction: 'all'
						}
					]
				},
				{	ref: '_cell',
					fieldLabel: this.ln.cellpadding +', '+ this.ln.cellspacing,
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
						{	ref: '_padding',
							fieldLabel: this.ln.cellpadding,
							xtype: 'numberfield',
							allowDecimals: false,
							value: 2
						},
						{xtype:'label',text:'px',width: 20, style:'padding-top:4px'},
						{	ref: '_spacing',
							fieldLabel: this.ln.cellspacing,
							xtype: 'numberfield',
							allowDecimals: false,
							value: 0
						},
						{xtype:'label',text:'px',style:'padding-top:4px'}
					]
				},
				{	ref: '_alignment',
					fieldLabel: this.ln.alignment,
					xtype: 'combo', 
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['value', 'display'],
						idIndex: 0,
						data: [['',''],['left',this.ln.left],['center',this.ln.center],['right',this.ln.right]]
					},
					tpl: '<tpl for="."><div class="x-combo-list-item" style="text-align:{values.value}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'value',
					displayField: 'display',
					triggerAction: 'all'
				},
				{	ref: '_class',
					fieldLabel: this.ln._class,
					xtype: 'combo', 
					mode: 'local',
					store: PC.utils.Get_classes_array('table'),
					triggerAction: 'all'
				},
				{	ref: '_border',
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
							value: '',
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
								change: function(cb, val, oldval) {
									if (val == '') {
										dialog.form._border.innerCt._size.disable();
										dialog.form._border.innerCt._color.disable();
									} else {
										dialog.form._border.innerCt._size.enable();
										dialog.form._border.innerCt._color.enable();
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
							listeners: {
								change: function(cf, val, oldval) {
									if (val) return cf.onSelect(cf, val);
								}
							},
							detectFontColor: function() {
								if (this.menu && this.menu.picker.rawValue) {
									var val = this.menu.picker.rawValue;
								} else {
									var pcol = PC.utils.color2Hex(this.value);
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
				{	ref: '_bgimage',
					xtype:'trigger', 
					fieldLabel: this.ln.bg_image,
					triggerClass: 'x-form-search-trigger',
					selectOnFocus: true,
					onTriggerClick: function() {
						var field = this;
						var params = {
							callee: 'image',
							thumbnail_type : '',
							save_fn: function(url, rec, callback, params){
								field.setValue(url);
								callback();
							}
						};
						var src = field.getValue();
						if (/^gallery\//.test(src)) {
							params.select_id = src.substring(src.lastIndexOf('/')+1);
						}
						PC.dialog.gallery.show(params);
					}
				},
				{	ref: '_style',
					xtype: 'textarea',
					fieldLabel: this.ln.style,
					height: 45
				}
			]
		});
		this.window = new PC.ux.Window({
			title: this.ln.title,
			modal: true,
			width: this.chooser_width+380, height: this.chooser_height+70,
			layout: 'border',
			resizable: false,
			items: [this.size_chooser, this.form],
			bbar: [
				{xtype:'tbfill'},
				{	ref: '../_insert',
					text: this.ln.insert,
					icon: 'images/add.gif',
					handler: function() {
						var f = dialog.form;
						//reference parameters
						var p = {
							cols: f._rowscols.innerCt._columns.getValue(),
							rows: f._rowscols.innerCt._rows.getValue(),
							w: f._wh.innerCt._width.getValue(),
							w_unit: f._wh.innerCt._width_unit.getValue(),
							h: f._wh.innerCt._height.getValue(),
							h_unit: f._wh.innerCt._height_unit.getValue(),
							cellspacing: f._cell.innerCt._spacing.getValue(),
							cellpadding: f._cell.innerCt._padding.getValue(),
							cls: f._class.getValue(),
							align: f._alignment.getValue(),
							border_style: f._border.innerCt._style.getValue(),
							border_size: f._border.innerCt._size.getValue(),
							border_color: f._border.innerCt._color.getValue(),
							bg_image: f._bgimage.getValue(),
							custom_styles: f._style.getValue()
						};
						//edit mode (update existing table)
						var styles = '';
						if (p.border_style != '' && p.border_style != undefined) {
							if (p.border_size < 1) p.border_size = 1;
							if (p.border_color == '' || p.border_color == undefined) {
								p.border_color = '#000';
							}
							styles += 'border:'+p.border_size+'px '+p.border_style+' '+p.border_color+';';
						}
						var bg_img_style = '';
						if (p.bg_image != '' && p.bg_image != undefined) {
							bg_img_style = 'url('+ p.bg_image +')';
							styles += 'background-image: url('+ p.bg_image +');';
						}
						if (p.custom_styles != '' && p.custom_styles != undefined) {
							styles += p.custom_styles;
						}
						if (dialog.edit_mode) {
							tinymce.activeEditor.execCommand('mceBeginUndoLevel');
							var table = Ext.get(dialog.table);
							table.set({
								width: p.w+p.w_unit,
								height: p.h+p.h_unit,
								cellspacing: p.cellspacing,
								cellpadding: p.cellpadding,
								'class': p.cls,
								align: p.align,
								style: styles,
								_mce_style: styles
							});
							if (bg_img_style != '') {
								table.setStyle('background-image', bg_img_style); 
							}
							tinymce.activeEditor.addVisual();
							tinymce.activeEditor.nodeChanged();
							tinymce.activeEditor.execCommand('mceEndUndoLevel');
							dialog.window.close();
							return;
						}
						//generate new table
						table = dialog.generateTable.apply(p);
						tinyMCE.execInstanceCommand(tinymce.activeEditor.id, "mceInsertContent", false, table);
						tinymce.activeEditor.addVisual();
						dialog.window.close();
					}
				}
			]
		});
		this.window.show();
		//edit mode
		if (this.edit_mode) {
			//action texts
			this.window._insert.setText(this.ln.title_update);
			this.size_chooser.disable();
			//references
			var f = this.form;
			var w = f._wh.innerCt._width;
			var w_unit = f._wh.innerCt._width_unit;
			var h = f._wh.innerCt._height;
			var h_unit = f._wh.innerCt._height_unit;
			var cellspacing = f._cell.innerCt._spacing;
			var cellpadding = f._cell.innerCt._padding;
			var cls = f._class;
			var align = f._alignment;
			var border_style = f._border.innerCt._style;
			var border_size = f._border.innerCt._size;
			var border_color = f._border.innerCt._color;
			var bgimage = f._bgimage;
			var custom_styles = f._style;
			//update fields and chooser
			var table = Ext.get(this.table);
			//c_ - prefix meaning that this value is current
			//cols and rows
			var cols = f._rowscols.innerCt._columns; cols.disable();
			var rows = f._rowscols.innerCt._rows; rows.disable();
			var c_cols = this.table.rows[0].cells.length;
			var c_rows = this.table.rows.length;
			cols.setValue(c_cols);
			rows.setValue(c_rows);
			var chooser = this.size_chooser._chooser.getEl();
			chooser.setWidth(c_cols*dialog.cell_side);
			chooser.setHeight(c_rows*dialog.cell_side);
			//width and height
			var c_width = table.getAttribute('width');
			if (c_width != '' && c_width != undefined) {
				if (c_width.substr(c_width.length-1) == '%') w_unit.setValue('%');
				else w_unit.setValue('px');
				w.setValue(parseInt(c_width));
			}
			var c_height = table.getAttribute('height');
			if (c_height != '' && c_height != undefined) {
				if (c_height.substr(c_height.length-1) == '%') h_unit.setValue('%');
				else h_unit.setValue('px');
				h.setValue(parseInt(c_height));
			}
			//cell spacing, padding
			var c_cell_spacing = table.getAttribute('cellspacing');
			if (c_cell_spacing != '' && c_cell_spacing != undefined) {
				cellspacing.setValue(c_cell_spacing);
			}
			var c_cell_padding = table.getAttribute('cellpadding');
			if (c_cell_padding != '' && c_cell_padding != undefined) {
				cellpadding.setValue(c_cell_padding);
			}
			//class
			var c_class = table.getAttribute('class');
			if (c_class != '' && c_class != undefined) {
				c_class = c_class.replace('mceItemTable','').replace(/^\s+|\s+$/g,"");
				cls.setValue(c_class);
			}
			//alignment
			var c_align = table.getAttribute('align');
			if (c_align != '' && c_align != undefined) {
				align.setValue(c_align);
			}
			//STYLES
			var c_styles = table.getAttribute('style');
			//border
			if (c_styles.length) {
				var s_start = c_styles.indexOf('border:');
				if (s_start >= 0) {
					var s_end = c_styles.indexOf(';', s_start);
					var c_border = c_styles.substring(s_start+8, s_end);
					var s_st_space = c_border.indexOf(' ');
					var s_nd_space = c_border.indexOf(' ', s_st_space+1);
					var s_size = c_border.substring(0, s_st_space);
					var s_style = c_border.substring(s_st_space+1, s_nd_space);
					var s_color = c_border.substr(s_nd_space+1);
					border_size.setValue(parseInt(s_size));
					border_style.setValue(s_style);
					border_color.setValue(colorToHex(s_color));
					border_style.fireEvent('change', border_style, border_style.value, border_style.originalValue);
					c_styles = c_styles.substring(0, s_start)+c_styles.substr(s_end+1);
				}
				//background image
				var s_start = c_styles.indexOf('background-image:');
				if (s_start >= 0) {
					var s_end = c_styles.indexOf(';', s_start);
					var c_bgimage = c_styles.substring(s_start+18, s_end).replace(new RegExp("url\\(['\"]?([^'\"]*)['\"]?\\)", 'gi'), "$1");
					bgimage.setValue(c_bgimage);
					c_styles = c_styles.substring(0, s_start)+c_styles.substr(s_end+1);
				}
				//custom styles
				custom_styles.setValue(c_styles);
			}
		}
	},
	generateTable: function() {
		var t = this;
		var table = '<table';
		if (t.border_style == '' || t.border_style == undefined) {
			table += ' border="0"';
		}
		if (t.w != '' && t.w != undefined) {
			table += ' width="'+t.w+t.w_unit+'"';
		}// else table += ' width="100%"';
		if (t.h != '' && t.h != undefined) {
			table += ' height="'+t.h+t.h_unit+'"';
		}
		if (t.cellspacing >= 0) table += ' cellspacing="'+t.cellspacing+'"';
		if (t.cellpadding >= 0) table += ' cellpadding="'+t.cellpadding+'"';
		if (t.cls != '' && t.cls != undefined) {
			table += ' class="'+t.cls+'"';
		}
		if (t.align != '' && t.align != undefined) {
			table += ' align="'+t.align+'"';
		}
		var styles = '';
		if (t.border_style != '' && t.border_style != undefined) {
			if (t.border_size < 1) t.border_size = 1;
			if (t.border_color == '' || t.border_color == undefined) {
				t.border_color = '#000';
			}
			styles += 'border:'+t.border_size+'px '+t.border_style+' '+t.border_color+';';
		}
		if (t.bg_image != '' && t.bg_image != undefined) {
			styles += 'background-image: url('+ t.bg_image +');';
		}
		if (t.custom_styles != '' && t.custom_styles != undefined) {
			styles += t.custom_styles;
		}
		if (styles != '') {
			table += ' style="'+styles+'"';
		}
		table += ">\n";
		//generate specific number of tr and td tags
		table += Array(t.rows+1).join('<tr>'+Array(t.cols+1).join("<td>&nbsp;</td>")+"</tr>\n");
		table += '</table>';
		return table;
	}
};

//quick add table
PC.dialog.quicktable = {
	cell_side: 30, chooser_cols: 6, chooser_rows: 5,
	chooser_cols_start: 2, chooser_rows_start: 2,
	chooser_body_style: 'background: url(images/table_cell.jpg);',
	show: function(x, y, splitbutton) {
		this.ln = PC.i18n.dialog.tables;
		this.bookmark = tinymce.activeEditor.selection.getBookmark('simple');
		this.splitbutton = splitbutton;
		var dialog = this;
		if (this.window) {
			this.window.show();
			this.window.setPagePosition(x, y);
			return;
		}
		this.chooser_width = this.chooser_cols*this.cell_side;
		this.chooser_height = this.chooser_rows*this.cell_side;
		this.window = new PC.ux.Window({
			pc_temp_window: true,
			closeAction: 'hide',
			width: this.chooser_width+12,
			height: this.chooser_height+42,
			resizable: false,
			closable: false,
			draggable: false,
			shadow: false,
			border: false,
			items: {
				xtype: 'panel',
				layout: 'fit',
				width: this.chooser_width,
				height: this.chooser_height,
				border: false,
				items: [
					{	xtype: 'panel',
						border: false,
						bodyStyle: 'cursor:pointer;'+dialog.chooser_body_style,
						items: [
							{	xtype: 'panel',
								ref: '../_chooser',
								border: false,
								bodyStyle: 'background: transparent',
								listeners: {
									render: function(panel) {
										var resizable_area = this.getEl();
										PC.dialog.quicktable.chooser = new Ext.Resizable(resizable_area, {
											pinned: true, handles: 'all',
											//increment
											widthIncrement: dialog.cell_side,
											heightIncrement: dialog.cell_side,
											//min values
											minWidth: dialog.cell_side,
											minHeight: dialog.cell_side,
											//max values
											maxWidth: dialog.chooser_width,
											maxHeight: dialog.chooser_height,
											//init dimensions
											width: dialog.chooser_cols_start*dialog.cell_side,
											height: dialog.chooser_rows_start*dialog.cell_side,
											//other
											dynamic: true,
											bodyStyle: dialog.chooser_body_style,
											listeners: {
												resize: function (chooser, w, h) {
													if (chooser.el.getBox().x != chooser.el.parent().parent().getBox().x
														|| chooser.el.getBox().y != chooser.el.parent().parent().getBox().y) {
														chooser.el.setXY(chooser.el.parent().parent().getBox());
													}
												}
											}
										});
									}
								}
							}
						],
						listeners: {
							render: function(panel) {
								var el = panel.getEl();
								var last_move = new Date().getTime();
								el.addListener('mousemove', function(ev, el, obj) {
									if ((new Date().getTime()-last_move) < 200) return;
									var box = panel.getBox();
									var x = (ev.xy[0]-box.x);
									var y = (ev.xy[1]-box.y);
									var cols = Math.round(x/dialog.cell_side);
									cols = (cols>=1?cols:1);
									var rows = Math.round(y/dialog.cell_side);
									rows = (rows>=1?rows:1);
									//update chooser size
									var el = dialog.chooser.getEl();
									el.setWidth(cols*dialog.cell_side);
									el.setHeight(rows*dialog.cell_side);
									//update form values
									Ext.getCmp('quicktable_chooser_cols').setValue(cols);
									Ext.getCmp('quicktable_chooser_rows').setValue(rows);
								});
								el.addListener('click', function(event, element) {
									var el_xy = Ext.get(element).getXY();
									var width = event.xy[0] - el_xy[0];
									var height = event.xy[1] - el_xy[1];
									var a = Math.round(width/dialog.cell_side);
									var b = Math.round(height/dialog.cell_side);
									//insert table
									var p = {
										cols: a, rows: b,
										w: 100, w_unit: '%',
										cellspacing: 0,
										cellpadding: 2
									};
									table = PC.dialog.tables.generateTable.apply(p);
									tinymce.activeEditor.selection.moveToBookmark(dialog.bookmark);
									tinyMCE.execInstanceCommand(tinymce.activeEditor.id, "mceInsertContent", false, table);
									tinymce.activeEditor.addVisual();
									dialog.window.hide();
								});
							}
						}
					}
				]
			},
			bbar: new Ext.Toolbar({
				items: [
					{	id: 'quicktable_chooser_cols',
						xtype: 'textfield',
						value: this.chooser_cols_start,
						readOnly: true,
						width: 22
					},
					{xtype:'tbtext', text: 'x'},
					{	id: 'quicktable_chooser_rows',
						xtype: 'textfield',
						value: this.chooser_rows_start,
						readOnly: true,
						width: 22
					},
					{xtype:'tbfill'},
					{	text: this.ln.more,
						icon: 'images/table.png',
						handler: function() {
							tinymce.execCommand('mceInsertTable', undefined, {action:'insert'});
						}
					}
				]
			}),
			listeners: {
				deactivate: function(w){
					w.hide();
				}
			}
		});

		this.window.addListener('deactivate', function(w) {
			w.hide();
		});
		
		this.window.show();
		this.window.setPagePosition(x, y);
	}
};