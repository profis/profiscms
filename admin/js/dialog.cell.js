Ext.ns('PC.dialog');
PC.dialog.tablecell = {
	show: function(cell) {
		this.ln = PC.i18n.dialog.tablecell;
		var dialog = this;
		this.general = {
			title: this.ln.general,
			layout: 'form',
			border: false,
			padding: '6px 9px 0 3px',
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor:'100%', xtype:'textfield'},
			items: [
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
							['left', PC.i18n.align.left],
							['center', PC.i18n.align.center],
							['right', PC.i18n.align.right]
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="text-align:{align}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'align',
					value: '',
					triggerAction: 'all'
				},
				{	fieldLabel: this.ln.valign,
					ref: '../../_valign',
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['align', 'display'],
						idIndex: 0,
						data: [
							['', '-'],
							['top', this.ln.top],
							['middle', this.ln.middle],
							['bottom', this.ln.bottom]
						]
					},
					displayField: 'display',
					//tpl: '<tpl for="."><div class="x-combo-list-item" style="text-align:{align}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'align',
					value: '',
					triggerAction: 'all'
				},
				/*{	ref: '../../_width',
					fieldLabel: this.ln.width,
					xtype: 'numberfield',
					minValue: 1, maxValue: 1000,
					value: 600
				},*/
				{	ref: '../../_wh',
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
							xtype: 'numberfield',
							minValue: 1, maxValue: 1000,
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
							minValue: 1, maxValue: 1000,
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
				/*{	ref: '../../_height',
					fieldLabel: this.ln.height,
					xtype: 'numberfield',
					minValue: 1, maxValue: 1000,
					value: 600
				},*/
				{	ref: '../../_class',
					fieldLabel: this.ln._class,
					xtype: 'combo', 
					mode: 'local',
					store: PC.utils.Get_classes_array('table'),
					triggerAction: 'all'
				},
				{	ref: '../../_wrap',
					checked: true,
					boxLabel: this.ln.wrap,
					xtype: 'checkbox'
				}
			]
		};
		this.advanced = {
			title: this.ln.advanced,
			layout: 'form',
			padding: '6px 9px 0 3px',
			border: false,
			autoScroll: true,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor: '100%', xtype:'textfield'},
			items: [
				{	fieldLabel: this.ln.id,
					ref: '../../_id'
				},
				{	fieldLabel: this.ln.style,
					ref: '../../_style'
				},
				{	fieldLabel: this.ln.bg_image,
					xtype:'trigger', ref: '../../_bgimage',
					triggerClass: 'x-form-search-trigger',
					selectOnFocus: true,
					onTriggerClick: function() {
						var field = this;
						var params = {
							callee: 'image',
							save_fn: function(url){
								field.setValue(url);
							}
						};
						var src = field.getValue();
						if (/^gallery\//.test(src)) {
							params.select_id = src.substring(src.lastIndexOf('/')+1);
						}
						PC.dialog.gallery.show(params);
					}
				},
				{	xtype: 'colorfield', ref: '../../_background',
					regex: /.*/,
					allowBlank: true,
					fieldLabel: this.ln.bg_color,
					value: '',
					listeners: {
						select: function(cf, val) {
							
						},
						change: function(cf, val, oldval) {
							
						}
					},
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
				},
				{	xtype: 'colorfield', ref: '../../_border',
					regex: /.*/,
					allowBlank: true,
					fieldLabel: this.ln.border_color,
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
			width: 400,
			height: 230,
			resizable: false,
			border: false,
			items: this.tabs,
			buttonAlign: 'left',
			buttons: [
				{	ref: '../_action', hidden: true,
					xtype: 'combo',
					mode: 'local',
					forceSelection: true,
					editable: false,
					store: {
						xtype: 'arraystore',
						fields: ['value', 'display'],
						idIndex: 0,
						data: [
							['cell', this.ln.target_cell],
							['row', this.ln.target_row],
							['all', this.ln.target_all]
						]
					},
					displayField: 'display',
					valueField: 'value',
					value: 'cell',
					triggerAction: 'all'
				},
				{xtype:'tbfill'},
				{	text: 'Update',
					handler: function() {
						dialog.update();
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
		this.window.show();
		//open cell
		var ed = tinymce.activeEditor;
		var el = ed.selection.getStart();
		this.cell = ed.dom.getParent(el, "td,th");
		this.row = ed.dom.getParent(el, "tr");
		this.table = ed.dom.getParent(el, "table");
		//general
		this.window._align.setValue(ed.dom.getAttrib(this.cell, 'align'));
		this.window._valign.setValue(ed.dom.getAttrib(this.cell, 'valign'));
		var _w = this.getStyle(this.cell, 'width', 'width');
		if (_w != '') {
			this.window._wh.innerCt._width.setValue(this.trimSize(this.getStyle(this.cell, 'width', 'width')));
			this.window._wh.innerCt._width_unit.setValue(this.trimUnit(this.getStyle(this.cell, 'width', 'width')));
		}
		var _h = this.getStyle(this.cell, 'height', 'height');
		if (_h != '') {
			this.window._wh.innerCt._height.setValue(this.trimSize(this.getStyle(this.cell, 'height', 'height')));
			this.window._wh.innerCt._height_unit.setValue(this.trimUnit(this.getStyle(this.cell, 'height', 'height')));
		}
		this.window._class.setValue(ed.dom.getAttrib(this.cell, 'class'));
		this.window._wrap.setValue((this.cell.getAttribute('nowrap')!='nowrap'));
		//advanced
		this.window._id.setValue(ed.dom.getAttrib(this.cell, 'id'));
		var st = ed.dom.parseStyle(ed.dom.getAttrib(this.cell, "style"));
		st = ed.dom.serializeStyle(st);
		//remove width/height from styles
		st = st.replace(/[^\-](width|height):.+?;/gi, '');
		this.window._style.setValue(st);
		this.window._bgimage.setValue(this.getStyle(this.cell, 'background', 'backgroundImage').replace(new RegExp("url\\(['\"]?([^'\"]*)['\"]?\\)", 'gi'), "$1"));
		this.window._background.setValue(this.convertRGBToHex(this.getStyle(this.cell, 'bgcolor', 'backgroundColor')));
		this.window._border.setValue(this.convertRGBToHex(this.getStyle(this.cell, 'bordercolor', 'borderLeftColor')));
		if (!ed.dom.hasClass(this.cell, 'mceSelected')) {
			this.window._action.show();
		}
	},
	update: function() {
		var dialog = this;
		//get all settings
		this.update_settings = {
			align: this.window._align.getValue(),
			valign: this.window._valign.getValue(),
			width: this.window._wh.innerCt._width.getValue(),
			width_unit: this.window._wh.innerCt._width_unit.getValue(),
			height: this.window._wh.innerCt._height.getValue(),
			height_unit: this.window._wh.innerCt._height_unit.getValue(),
			_class: this.window._class.getValue(),
			wrap: this.window._wrap.getValue(),
			id: this.window._id.getValue(),
			style: this.window._style.getValue(),
			bgimage: this.window._bgimage.getValue(),
			background: this.window._background.getValue(),
			border: this.window._border.getValue()
		};
		//switch update type (current cell, all cells in rows, all cells in table)
		//cell is selected
		var ed = tinymce.activeEditor;
		if (ed.dom.hasClass(this.cell, 'mceSelected')) {
			// Update all selected cells
			tinymce.each(ed.dom.select('td.mceSelected,th.mceSelected'), function(td) {
				dialog.update_cell(td);
			});
			ed.addVisual();
			ed.nodeChanged();
			ed.execCommand('mceEndUndoLevel');
			return;
		}
		
		ed.execCommand('mceBeginUndoLevel');
		switch (this.window._action.getValue()) {
			case "cell":
				this.update_cell(this.cell);
				break;
			case "row":
				var cell = this.row.firstChild;

				if (cell.nodeName != "TD" && cell.nodeName != "TH")
					cell = this.next_cell(cell);
				do {
					cell = this.update_cell(cell, true);
				} while ((cell = this.next_cell(cell)) != null);
				break;
			case "all":
				var rows = this.table.getElementsByTagName("tr");
				for (var i=0; i<rows.length; i++) {
					var cell = rows[i].firstChild;
					if (cell.nodeName != "TD" && cell.nodeName != "TH")
						cell = this.next_cell(cell);
					do {
						cell = this.update_cell(cell, true);
					} while ((cell = this.next_cell(cell)) != null);
				}
				break;
		}
		ed.addVisual();
		ed.nodeChanged();
		ed.execCommand('mceEndUndoLevel');
	},
	next_cell: function(elm) {
		while ((elm = elm.nextSibling) != null) {
			if (elm.nodeName == "TD" || elm.nodeName == "TH")
				return elm;
		}
		return null;
	},
	update_cell: function(td, skip_id) {
		var ed = tinymce.activeEditor;
		var inst = ed;
		var doc = inst.getDoc();
		var dom = ed.dom;

		if (!skip_id) td.setAttribute('id', this.update_settings.id);

		td.setAttribute('align', this.update_settings.align);
		td.setAttribute('valign', this.update_settings.valign);
		td.setAttribute('style', ed.dom.serializeStyle(ed.dom.parseStyle(this.update_settings.style)));
		ed.dom.setAttrib(td, 'class', this.update_settings._class);
		
		if (this.update_settings.wrap) td.removeAttribute('nowrap');
		else td.setAttribute('nowrap', 'nowrap');

		//clear deprecated attributes
		ed.dom.setAttrib(td, 'width', '');
		ed.dom.setAttrib(td, 'height', '');
		ed.dom.setAttrib(td, 'bgcolor', '');
		ed.dom.setAttrib(td, 'background', '');

		// Set styles
		if (this.update_settings.width!='') {
			td.style.width = this.update_settings.width+this.update_settings.width_unit;
		}
		else td.style.width = "";
		if (this.update_settings.height!='') {
			td.style.height = this.update_settings.height+this.update_settings.height_unit;
		}
		else td.style.height = "";
		//td.style.height = (this.update_settings.height!=''?this.update_settings.height+this.update_settings.height_unit:undefined);
		if (this.update_settings.border != "") {
			td.style.borderColor = this.update_settings.border;
			td.style.borderStyle = td.style.borderStyle == "" ? "solid" : td.style.borderStyle;
			td.style.borderWidth = td.style.borderWidth == "" ? "1px" : td.style.borderWidth;
		} else
			td.style.borderColor = '';

		td.style.backgroundColor = this.update_settings.background;

		if (this.update_settings.bgimage != "")
			td.style.backgroundImage = "url('" + this.update_settings.bgimage + "')";
		else
			td.style.backgroundImage = '';

		dom.setAttrib(td, 'style', dom.serializeStyle(dom.parseStyle(td.style.cssText)));
		
		return td;
	},
	trimSize: function(size) {
		return size.replace(/([0-9\.]+)px|(%|in|cm|mm|em|ex|pt|pc)/, '$1');
	},
	trimUnit: function(size) {
		return size.replace(/([0-9\.]+)(px|%|in|cm|mm|em|ex|pt|pc)/, '$2');
	},
	getStyle: function(elm, attrib, style) {
		var val = tinymce.activeEditor.dom.getAttrib(elm, attrib);

		if (val != '')
			return '' + val;

		if (typeof(style) == 'undefined')
			style = attrib;

		return tinymce.activeEditor.dom.getStyle(elm, style);
	},
	convertRGBToHex: function(col) {
		var re = new RegExp("rgb\\s*\\(\\s*([0-9]+).*,\\s*([0-9]+).*,\\s*([0-9]+).*\\)", "gi");

		var rgb = col.replace(re, "$1,$2,$3").split(',');
		if (rgb.length == 3) {
			r = parseInt(rgb[0]).toString(16);
			g = parseInt(rgb[1]).toString(16);
			b = parseInt(rgb[2]).toString(16);

			r = r.length == 1 ? '0' + r : r;
			g = g.length == 1 ? '0' + g : g;
			b = b.length == 1 ? '0' + b : b;

			return "#" + r + g + b;
		}

		return col;
	}
};