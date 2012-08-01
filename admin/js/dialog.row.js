Ext.ns('PC.dialog');
PC.dialog.tablerow = {
	show: function(row) {
		this.ln = PC.i18n.dialog.tablerow;
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
				{	ref: '../../_align',
					fieldLabel: this.ln.alignment,
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['align', 'display'],
						idIndex: 0,
						data: [
							['', ''],
							['left', this.ln.left],
							['center', this.ln.center],
							['right', this.ln.right]
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="text-align:{align}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'align',
					value: '',
					triggerAction: 'all'
				},
				{	ref: '../../_valign',
					fieldLabel: this.ln.valign,
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['align', 'display'],
						idIndex: 0,
						data: [
							['', '-'],
							['top', this.ln.top],
							['center', this.ln.center],
							['bottom', this.ln.bottom]
						]
					},
					displayField: 'display',
					//tpl: '<tpl for="."><div class="x-combo-list-item" style="text-align:{align}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'align',
					value: '',
					triggerAction: 'all'
				},
				{	ref: '../../_class',
					fieldLabel: this.ln._class,
					xtype: 'combo', 
					mode: 'local',
					store: PC.utils.Get_classes_array('table'),
					triggerAction: 'all'
				},
				{	ref: '../../_height',
					fieldLabel: this.ln.height,
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
				{	ref: '../../_background',
					xtype: 'colorfield',
					fieldLabel: this.ln.bg_color,
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
				{	ref: '../../_id',
					fieldLabel: this.ln.id
				},
				{	ref: '../../_style',
					fieldLabel: this.ln.style
				},
				{	ref: '../../_bgimage',
					fieldLabel: this.ln.bg_image,
					xtype:'trigger',
					triggerClass: 'x-form-search-trigger',
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
							['row', this.ln.apply_row],
							['all', this.ln.apply_all],
							['odd', this.ln.apply_odd],
							['even', this.ln.apply_even]
						]
					},
					displayField: 'display',
					valueField: 'value',
					value: 'row',
					triggerAction: 'all'
				},
				{xtype:'tbfill'},
				{	text: this.ln.update,
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
		//open row
		var ed = tinymce.activeEditor;
		var inst = ed;
		var dom = inst.dom;
		
		this.row = dom.getParent(inst.selection.getStart(), "tr");
		this.table = dom.getParent(inst.selection.getStart(), "table");
		
		this.window._align.setValue(ed.dom.getAttrib(this.row, 'align'));
		this.window._valign.setValue(ed.dom.getAttrib(this.row, 'valign'));
		this.window._class.setValue(ed.dom.getAttrib(this.row, 'class'));
		this.window._height.setValue(this.trimSize(this.getStyle(this.row, 'height', 'height')));
		this.window._background.setValue(this.convertRGBToHex(this.getStyle(this.row, 'bgcolor', 'backgroundColor')));
		this.window._id.setValue(ed.dom.getAttrib(this.row, 'id'));
		var st = ed.dom.parseStyle(ed.dom.getAttrib(this.row, "style"));
		this.window._style.setValue(ed.dom.serializeStyle(st));
		this.window._bgimage.setValue(this.getStyle(this.row, 'background', 'backgroundImage').replace(new RegExp("url\\(['\"]?([^'\"]*)['\"]?\\)", 'gi'), "$1"));
		
		//any cells selected
		if (dom.select('td.mceSelected,th.mceSelected', this.row).length == 0) {
			this.window._action.show();
		} else {}//tinyMCEPopup.dom.hide('action');
	},
	update: function() {
		var inst = tinymce.activeEditor, dom = inst.dom;
		var action = this.window._action.getValue();
		
		//get all settings
		this.update_settings = {
			align: this.window._align.getValue(),
			valign: this.window._valign.getValue(),
			_class: this.window._class.getValue(),
			height: this.window._height.getValue(),
			background: this.window._background.getValue(),
			id: this.window._id.getValue(),
			style: this.window._style.getValue(),
			bgimage: this.window._bgimage.getValue()
		};
		
		//update all selected rows
		if (dom.select('td.mceSelected,th.mceSelected', this.row).length > 0) {
			tinymce.each(this.table.rows, function(tr) {
				var i;
				for (i = 0; i < tr.cells.length; i++) {
					if (dom.hasClass(tr.cells[i], 'mceSelected')) {
						this.update_row(tr, true);
						return;
					}
				}
			});
			inst.addVisual();
			inst.nodeChanged();
			inst.execCommand('mceEndUndoLevel');
			return;
		}
		inst.execCommand('mceBeginUndoLevel');
		switch (action) {
			case "row":
				this.update_row(this.row);
				break;
			case "all":
				var rows = this.table.getElementsByTagName("tr");
				for (var i=0; i<rows.length; i++)
					this.update_row(rows[i], true);
				break;
			case "odd":
			case "even":
				var rows = this.table.getElementsByTagName("tr");
				for (var i=0; i<rows.length; i++) {
					if ((i % 2 == 0 && action == "odd") || (i % 2 != 0 && action == "even"))
						this.update_row(rows[i], true, true);
				}
				break;
		}
		inst.addVisual();
		inst.nodeChanged();
		inst.execCommand('mceEndUndoLevel');
	},
	update_row: function(tr_elm, skip_id, skip_parent) {
		var inst = tinymce.activeEditor;
		var formObj = document.forms[0];
		var dom = inst.dom;
		var doc = inst.getDoc();

		// Update row element
		if (!skip_id)
			tr_elm.setAttribute('id', this.update_settings.id);

		tr_elm.setAttribute('align', this.update_settings.align);
		tr_elm.setAttribute('valign', this.update_settings.valign);
		tr_elm.setAttribute('style', dom.serializeStyle(dom.parseStyle(this.update_settings.style)));
		dom.setAttrib(tr_elm, 'class', this.update_settings._class);

		//clear deprecated attributes
		tr_elm.setAttribute('background', '');
		tr_elm.setAttribute('bgcolor', '');
		tr_elm.setAttribute('height', '');

		// Set styles
		tr_elm.style.height = this.update_settings.height+'px';
		tr_elm.style.backgroundColor = this.update_settings.background;

		if (this.window._bgimage.getValue() != "")
			tr_elm.style.backgroundImage = "url('" + this.update_settings.bgimage + "')";
		else
			tr_elm.style.backgroundImage = '';

		dom.setAttrib(tr_elm, 'style', dom.serializeStyle(dom.parseStyle(tr_elm.style.cssText)));
	},
	getStyle: function(elm, attrib, style) {
		var val = tinymce.activeEditor.dom.getAttrib(elm, attrib);

		if (val != '')
			return '' + val;

		if (typeof(style) == 'undefined')
			style = attrib;

		return tinymce.activeEditor.dom.getStyle(elm, style);
	},
	trimSize: function(size) {
		return size.replace(/([0-9\.]+)px|(%|in|cm|mm|em|ex|pt|pc)/, '$1$2');
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