Ext.namespace('PC.dialog');

PC.dialog.styles = {
	padding_changed: function(cb, val, oldval) {
		//init
		var w = PC.dialog.styles.window;
		var f = w._form;
		if (!w._rec) return;
		var s = w._adv.getStore();
		var i = s.find('property', /^padding$/i);
		var rec = s.getAt(i);
		
		//refferences
		var current = cb.ref;
		
		var _top = f._padding.innerCt._top;
		var _right = f._padding.innerCt._right;
		var _bottom = f._padding.innerCt._bottom;
		var _left = f._padding.innerCt._left;
		
		//currenty not set, assign this value to all sides
		if (i == -1) {
			if (val) {
				_top.setValue(val);
				_right.setValue(val);
				_bottom.setValue(val);
				_left.setValue(val);
			} else return;
		}
		//format padding stylesheet
		var sides = new Array();
		sides[0] = _top.getValue() + 'px';
		sides[1] = _right.getValue() + 'px';
		sides[2] = _bottom.getValue() + 'px';
		sides[3] = _left.getValue() + 'px';
		var padding = sides.join(' ');
		//update style declaration
		if (i == -1) {
			s.addSorted(new s.recordType({
				property: 'padding',
				value: padding
			}));
		} else {
			rec.set('value', padding);
		}
		w._adv._update_rec();
	},
	margin_changed: function(cb, val, oldval) {
		//init
		var w = PC.dialog.styles.window;
		var f = w._form;
		if (!w._rec) return;
		var s = w._adv.getStore();
		var i = s.find('property', /^margin$/i);
		var rec = s.getAt(i);
		
		//refferences
		var current = cb.ref;
		
		var _top = f._margin.innerCt._top;
		var _right = f._margin.innerCt._right;
		var _bottom = f._margin.innerCt._bottom;
		var _left = f._margin.innerCt._left;
		
		//currenty not set, assign this value to all sides
		if (i == -1) {
			if (val) {
				_top.setValue(val);
				_right.setValue(val);
				_bottom.setValue(val);
				_left.setValue(val);
			} else return;
		}
		//format margin stylesheet
		var sides = new Array();
		sides[0] = _top.getValue() + 'px';
		sides[1] = _right.getValue() + 'px';
		sides[2] = _bottom.getValue() + 'px';
		sides[3] = _left.getValue() + 'px';
		var margin = sides.join(' ');
		//update style declaration
		if (i == -1) {
			s.addSorted(new s.recordType({
				property: 'margin',
				value: margin
			}));
		} else {
			rec.set('value', margin);
		}
		w._adv._update_rec();
	},
	border_changed: function(cb, val, oldval) {
		//init
		var w = PC.dialog.styles.window;
		var f = w._form;
		if (!w._rec) return;
		var s = w._adv.getStore();
		
		//refferences
		var current = cb.ref;
		
		switch (current) {
			case '_style':
				var i = s.find('property', /^border-style$/i);
				var rec = s.getAt(i);
				var _style = f._border.innerCt._style;
				if (val == '') {
					f._border.innerCt._size.disable();
					f._border.innerCt._color.disable();
				} else {
					f._border.innerCt._size.enable();
					f._border.innerCt._color.enable();
				}
				if (i == -1) {
					s.addSorted(new s.recordType({
						property: 'border-style',
						value: _style.getValue()
					}));
				} else rec.set('value', _style.getValue());
				break;
			case '_size':
				var i = s.find('property', /^border-width$/i);
				var rec = s.getAt(i);
				var _size = f._border.innerCt._size;
				if (i == -1) {
					s.addSorted(new s.recordType({
						property: 'border-width',
						value: _size.getValue()+'px'
					}));
				} else rec.set('value', _size.getValue()+'px');
				break;
		}
		w._adv._update_rec();
	},
	show: function() {
		this.ln = PC.i18n.dialog.styles;
		var dialog = this;
		dialog._overlay_not_first = false;
		if (!PC.dialog.styles.window) {
			PC.dialog.styles.form_items = [
				{	ref: '_border_collapse',
					fieldLabel: PC.i18n.dialog.styles.form.border_collapse,
					hidden: true,
					xtype: 'checkbox',
					listeners: {
						check: function(checkbox, checked) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^border-collapse$/i);
							if (i == -1) {
								if (checked) {
									s.addSorted(new s.recordType({
										property: 'border-collapse',
										value: 'collapse'
									}));
								}
							} else {
								if (checked) {
									s.getAt(i).set('value', 'collapse');
								} else {
									s.removeAt(i);
								}
							}
							w._adv._update_rec();
						}
					}
				},
				{	ref: '_vertical_align', hidden: true,
					fieldLabel: PC.i18n.dialog.styles.form.vertical_align,
					xtype: 'combo', mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['value', 'display'],
						idIndex: 0,
						data: [
							['', '-'],
							['top', this.ln.top],
							['middle', this.ln.middle],
							['bottom', this.ln.bottom]
						]
					},
					displayField: 'display',
					valueField: 'value',
					value: '',
					triggerAction: 'all',
					listeners: {
						change: function(cb, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^vertical-align$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'vertical-align',
										value: val.trim()
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val.trim());
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					}
				},
				{	ref: '_border',
					fieldLabel: PC.i18n.dialog.styles.form.border,
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
							triggerAction: 'all',
							listeners: {
								change: PC.dialog.styles.border_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
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
								change: PC.dialog.styles.border_changed,
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
								select: function(cf, val) {
									if (!val) return;
									var w = PC.dialog.styles.window;
									if (!w._rec) return;
									var s = w._adv.getStore();
									var i = s.find('property', /^border-color$/i);
									if (i == -1) {
										s.addSorted(new s.recordType({
											property: 'border-color',
											value: val
										}));
									} else {
										s.getAt(i).set('value', val);
									}
									w._adv._update_rec();
								},
								change: function(cf, val, oldval) {
									if (val) return cf.onSelect(cf, val);
									var w = PC.dialog.styles.window;
									if (!w._rec) return;
									var s = w._adv.getStore();
									var i = s.find('property', /^border-color$/i);
									if (i != -1) {
										s.removeAt(i);
										w._adv._update_rec();
									}
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
						}
					]
				},
				{	ref: '_margin', hidden: true,
					fieldLabel: PC.i18n.dialog.styles.form.margin,
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
								change: PC.dialog.styles.margin_changed,
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
								change: PC.dialog.styles.margin_changed,
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
								change: PC.dialog.styles.margin_changed,
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
								change: PC.dialog.styles.margin_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						}
					]
				},
				{	ref: '_padding', hidden: true,
					fieldLabel: PC.i18n.dialog.styles.form.padding,
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
								storeId: 'padding_top',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: PC.dialog.styles.padding_changed,
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
								storeId: 'padding_right',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: PC.dialog.styles.padding_changed,
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
								storeId: 'padding_bottom',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: PC.dialog.styles.padding_changed,
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
								storeId: 'padding_left',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: PC.dialog.styles.padding_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						}
					]
				},
				{	ref: '_font_family',
					fieldLabel: PC.i18n.dialog.styles.form.font,
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['display', 'font'],
						idIndex: 0,
						data: [
							// Arial, Courier, Cursive, Fantasy, Monospace, Sans-serif, Serif, Times
							['', ''],
							['Arial', 'Arial'],
							['Calibri', 'Calibri, Arial, Helvetica, sans-serif'],
							['Cursive', 'Cursive'],
							['Georgia', 'Georgia, Times'],
							['Monospace', 'Monospace'],
							['Segoe\xA0UI', 'Segoe UI, Trebuchet MS, Tahoma, Arial'],
							['Tahoma', 'Tahoma, Arial'],
							['Trebuchet\xA0MS', 'Trebuchet MS, Arial'],
							['Times', 'Times'],
							['Verdana', 'Verdana, Arial']
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="font-family:{font}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'font',
					value: '',
					triggerAction: 'all',
					listeners: {
						change: function(cb, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^font-family$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'font-family',
										value: val.trim()
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val.trim());
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					}
				},
				{	ref: '_font_size',
					fieldLabel: PC.i18n.dialog.styles.form.font_size,
					xtype: 'combo', 
					mode: 'local',
					allowDecimals: false,
					store: {
						xtype: 'arraystore',
						storeId: 'padding_left',
						fields: ['value', 'display'],
						idIndex: 0,
						data: [[8,8],[9,9],[10,10],[11,11],[12,12],[14,14],[16,16],[18,18],[20,20]]
					},
					valueField: 'value',
					displayField: 'display',
					value: 10,
					triggerAction: 'all',
					listeners: {
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						},
						change: function(nf, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^font-size$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'font-size',
										value: val+'px'
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val+'px');
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						}
					}
				},
				{	ref: '_line_height',
					fieldLabel: PC.i18n.dialog.styles.form.line_height,
					xtype: 'combo', 
					mode: 'local',
					allowDecimals: false,
					store: {
						xtype: 'arraystore',
						storeId: 'padding_left',
						fields: ['value', 'display'],
						idIndex: 0,
						data: new function() {
							var options = [['normal', dialog.ln.form.line_height_normal]];
							for (var a=10;a<=30;a++) {
								options.push([a+'px', a+'px']);
							}
							return options;
						}
					},
					tpl: '<tpl for="."><div class="x-combo-list-item">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'value',
					displayField: 'display',
					triggerAction: 'all',
					listeners: {
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						},
						change: function(nf, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^line-height$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'line-height',
										value: val
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val);
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						}
					}
				},
				{	ref: '_color',
					xtype: 'colorfield',
					fieldLabel: PC.i18n.dialog.styles.form.color,
					regex: /.*/,
					allowBlank: true,
					value: '',
					listeners: {
						select: function(cf, val) {
							if (!val) return;
							//if (console && console.log) console.log('select: '+val);
							var w = dialog.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^color$/i);
							if (i == -1) {
								s.addSorted(new s.recordType({
									property: 'color',
									value: val
								}));
							} else {
								s.getAt(i).set('value', val);
							}
							w._adv._update_rec();
						},
						change: function(cf, val, oldval) {
							if (val) return cf.onSelect(cf, val);
							//if (console && console.log) console.log('change: '+val);
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^color$/i);
							if (i != -1) {
								s.removeAt(i);
								w._adv._update_rec();
							}
						}
						/*
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^color$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'color',
										value: val
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val);
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						*/
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
				{	ref: '_background_color',
					xtype: 'colorfield',
					fieldLabel: PC.i18n.dialog.styles.form.background_color,
					regex: /.*/,
					allowBlank: true,
					value: '',
					listeners: {
						select: function(cf, val) {
							if (!val) return;
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^background-color$/i);
							if (i == -1) {
								s.addSorted(new s.recordType({
									property: 'background-color',
									value: val
								}));
							} else {
								s.getAt(i).set('value', val);
							}
							w._adv._update_rec();
						},
						change: function(cf, val, oldval) {
							if (val) return cf.onSelect(cf, val);
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^background-color$/i);
							if (i != -1) {
								s.removeAt(i);
								w._adv._update_rec();
							}
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
				},
				{	ref: '_font_weight',
					fieldLabel: PC.i18n.dialog.styles.form.font_weight,
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['weight', 'display'],
						idIndex: 0,
						data: [
							['', ''],
							['bold', PC.i18n.dialog.styles.weight.bold],
							['normal', PC.i18n.dialog.styles.weight.normal]
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="font-weight:{weight}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'weight',
					value: '',
					triggerAction: 'all',
					listeners: {
						change: function(cb, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^font-weight$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'font-weight',
										value: val.trim()
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val.trim());
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					}
				},
				{	ref: '_font_style',
					fieldLabel: PC.i18n.dialog.styles.form.font_style,
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['style', 'display'],
						idIndex: 0,
						data: [
							['', ''],
							['italic', PC.i18n.dialog.styles.style.italic],
							['oblique', PC.i18n.dialog.styles.style.oblique],
							['normal', PC.i18n.dialog.styles.style.normal]
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="font-style:{style}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'style',
					value: '',
					triggerAction: 'all',
					listeners: {
						change: function(cb, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^font-style$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'font-style',
										value: val.trim()
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val.trim());
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					}
				},
				{	ref: '_text_decoration',
					fieldLabel: PC.i18n.dialog.styles.form.text_decoration,
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['decor', 'display'],
						idIndex: 0,
						data: [
							['', ''],
							['underline', PC.i18n.dialog.styles.decor.underline],
							['line-through', PC.i18n.dialog.styles.decor.line_through],
							['overline', PC.i18n.dialog.styles.decor.overline],
							['none', PC.i18n.dialog.styles.decor.none]
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="text-decoration:{decor}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'decor',
					value: '',
					triggerAction: 'all',
					listeners: {
						change: function(cb, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^text-decoration$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'text-decoration',
										value: val.trim()
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val.trim());
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					}
				},
				{	ref: '_text_align',
					fieldLabel: PC.i18n.dialog.styles.form.text_align,
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
							['right', PC.i18n.align.right],
							['justify', PC.i18n.align.justify]
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="text-align:{align}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'align',
					value: '',
					triggerAction: 'all',
					listeners: {
						change: function(cb, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^text-align$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'text-align',
										value: val.trim()
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val.trim());
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					}
				},
				{	ref: '_text_indent', hidden: true,
					fieldLabel: PC.i18n.dialog.styles.form.text_indent,
					xtype: 'numberfield',
					allowDecimals: false,
					listeners: {
						change: function(nf, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^text-indent$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'text-indent',
										value: val+'px'
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val+'px');
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						}
					}
				},
				{	ref: '_text_transform',
					fieldLabel: PC.i18n.dialog.styles.form.text_transform,
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['xform', 'display'],
						idIndex: 0,
						data: [
							['', ''],
							['uppercase', PC.i18n.dialog.styles.xform.uppercase],
							['lowercase', PC.i18n.dialog.styles.xform.lowercase],
							['capitalize', PC.i18n.dialog.styles.xform.capitalize]
						]
					},
					displayField: 'display',
					tpl: '<tpl for="."><div class="x-combo-list-item" style="text-transform:{xform}">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
					valueField: 'xform',
					value: '',
					triggerAction: 'all',
					listeners: {
						change: function(cb, val, oldval) {
							var w = PC.dialog.styles.window;
							if (!w._rec) return;
							var s = w._adv.getStore();
							var i = s.find('property', /^text-transform$/i);
							if (i == -1) {
								if (val)
									s.addSorted(new s.recordType({
										property: 'text-transform',
										value: val.trim()
									}));
							} else {
								if (val)
									s.getAt(i).set('value', val.trim());
								else
									s.removeAt(i);
							}
							w._adv._update_rec();
						},
						select: function(cb, rec, idx) {
							cb.fireEvent('change', cb, cb.value, cb.originalValue);
						}
					}
				},
				//this item must always be last. It is used as an anchor to know when all form is fully rendered.
				{	ref: '_mock_renderer',
					hidden: true,
					listeners: {
						afterrender: function() {
							//re-invoke form update function when form is loaded first time (because of its 'first time form overlay')
							var sm = PC.dialog.styles.window._grid.selModel;
							setTimeout(function(){
								sm.fireEvent('selectionchange', sm);
							}, 10);
						}
					}
				}
			];
			PC.dialog.styles.form = {
				ref: '../../_form',
				title: PC.i18n.styles,
				layout: 'form',
				padding: 4,
				autoScroll: true,
				bodyCssClass: 'x-border-layout-ct',
				labelWidth: 190,
				labelAlign: 'right',
				defaults: {
					anchor: '94%'
				},
				items: PC.dialog.styles.form_items,
				_display_form: function(elements) {
					//hide or show form elements
					var w = PC.dialog.styles.window;
					var f = w._form;
					//shared styles
					if (elements.has('background_color')) f._background_color.show(); else f._background_color.hide();
					//border
					if (elements.has('border')) f._border.show();
					else f._border.hide();
					//margin
					if (elements.has('margin')) f._margin.show();
					else f._margin.hide();
					//padding
					if (elements.has('padding')) f._padding.show();
					else f._padding.hide();
					//text styles
					if (elements.has('font_family')) f._font_family.show(); else f._font_family.hide();
					if (elements.has('font_size')) f._font_size.show(); else f._font_size.hide();
					if (elements.has('line_height')) f._line_height.show(); else f._line_height.hide();
					if (elements.has('color')) f._color.show(); else f._color.hide();
					if (elements.has('font_weight')) f._font_weight.show(); else f._font_weight.hide();
					if (elements.has('font_style')) f._font_style.show(); else f._font_style.hide();
					if (elements.has('text_decoration')) f._text_decoration.show(); else f._text_decoration.hide();
					if (elements.has('text_align')) f._text_align.show(); else f._text_align.hide();
					if (elements.has('text_indent')) f._text_indent.show(); else f._text_indent.hide();
					if (elements.has('text_transform')) f._text_transform.show(); else f._text_transform.hide();
					//table styles
					if (elements.has('border_collapse')) f._border_collapse.show(); else f._border_collapse.hide();
					if (elements.has('vertical_align')) f._vertical_align.show(); else f._vertical_align.hide();
				},
				_load: function() {
					var w = PC.dialog.styles.window;
					var f = w._form;
					if (!w._rec) f._border_collapse.setValue(false);
					f._vertical_align.setValue('');
					//border
					f._border.innerCt._style.setValue('');
					f._border.innerCt._size.setValue('');
					f._border.innerCt._color.setValue('');
					f._border.innerCt._size.disable();
					f._border.innerCt._color.disable();
					//margin
					f._margin.innerCt._top.setValue('');
					f._margin.innerCt._right.setValue('');
					f._margin.innerCt._bottom.setValue('');
					f._margin.innerCt._left.setValue('');
					//padding
					f._padding.innerCt._top.setValue('');
					f._padding.innerCt._right.setValue('');
					f._padding.innerCt._bottom.setValue('');
					f._padding.innerCt._left.setValue('');
					//texts
					f._font_family.setValue('');
					f._font_size.setValue('');
					f._line_height.setValue('');
					f._color.onSelect(f._color, '');
					f._background_color.onSelect(f._background_color, '');
					f._font_weight.setValue('');
					f._font_style.setValue('');
					f._text_decoration.setValue('');
					f._text_align.setValue('');
					f._text_indent.setValue('');
					f._text_transform.setValue('');
					if (w._rec) {
						//display suitable form for the element styles
						if (w._rec.data.tag == 'img') {
							if (PC.dialog.styles.current_form_type != 'img') {
								PC.dialog.styles.current_form_type = 'img';
								f._display_form(['','border','margin']);
							}
						} else if (w._rec.data.tag == 'table') {
							if (PC.dialog.styles.current_form_type != 'table') {
								PC.dialog.styles.current_form_type = 'table';
								f._display_form(['','border_collapse','border','background_color','margin','font_family','font_size','line_height','color','font_weight','font_style','text_decoration','text_align','text_transform']);
							}
						} else if (w._rec.data.tag == 'tr') {
							if (PC.dialog.styles.current_form_type != 'tr') {
								PC.dialog.styles.current_form_type = 'tr';
								f._display_form(['','background_color','vertical_align']);
							}
						} else if (w._rec.data.tag == 'td') {
							if (PC.dialog.styles.current_form_type != 'td') {
								PC.dialog.styles.current_form_type = 'td';
								f._display_form(['','border','padding']);
							}
						} else {
							if (true/*PC.dialog.styles.current_form_type != undefined*/) {
								PC.dialog.styles.current_form_type = undefined;
								f._display_form(['','background_color','border','font_family','font_size','line_height','color','font_weight','font_style','text_decoration','text_align','text_transform']);
							}
						}
						//load saved styles
						w._adv.getStore().each(function(rec) {
							switch (rec.get('property').toLowerCase()) {
								case 'border-collapse':
									f._border_collapse.setValue(rec.get('value')=='collapse'?1:0);
									break;
								case 'vertical-align':
									f._vertical_align.setValue(rec.get('value'));
									break;
								case 'border-style':
									f._border.innerCt._style.setValue(rec.get('value'));
									f._border.innerCt._size.enable();
									f._border.innerCt._color.enable();
									break;
								case 'border-width':
									f._border.innerCt._size.setValue(parseInt(rec.get('value')));
									break;
								case 'border-color':
									f._border.innerCt._color.onSelect(f._color, rec.get('value'));
									//f._border.innerCt._color.setValue(rec.get('value'));
									break;
								case 'margin':
									var sides = rec.get('value').split(' ');
									switch (sides.length) {
										case 0:
											f._margin.innerCt._top.setValue(0);
											f._margin.innerCt._right.setValue(0);
											f._margin.innerCt._bottom.setValue(0);
											f._margin.innerCt._left.setValue(0);
											break;
										case 1:
											f._margin.innerCt._top.setValue(parseInt(sides[0]));
											f._margin.innerCt._right.setValue(parseInt(sides[0]));
											f._margin.innerCt._bottom.setValue(parseInt(sides[0]));
											f._margin.innerCt._left.setValue(parseInt(sides[0]));
											break;
										case 2:
											f._margin.innerCt._top.setValue(parseInt(sides[0]));
											f._margin.innerCt._right.setValue(parseInt(sides[1]));
											f._margin.innerCt._bottom.setValue(parseInt(sides[0]));
											f._margin.innerCt._left.setValue(parseInt(sides[1]));
											break;
										case 3:
											f._margin.innerCt._top.setValue(parseInt(sides[0]));
											f._margin.innerCt._right.setValue(parseInt(sides[1]));
											f._margin.innerCt._bottom.setValue(parseInt(sides[2]));
											f._margin.innerCt._left.setValue(0);
											break;
										default: //4 or more
											f._margin.innerCt._top.setValue(parseInt(sides[0]));
											f._margin.innerCt._right.setValue(parseInt(sides[1]));
											f._margin.innerCt._bottom.setValue(parseInt(sides[2]));
											f._margin.innerCt._left.setValue(parseInt(sides[3]));
									}
									break;
								case 'padding':
									var sides = rec.get('value').split(' ');
									switch (sides.length) {
										case 0:
											f._padding.innerCt._top.setValue(0);
											f._padding.innerCt._right.setValue(0);
											f._padding.innerCt._bottom.setValue(0);
											f._padding.innerCt._left.setValue(0);
											break;
										case 1:
											f._padding.innerCt._top.setValue(parseInt(sides[0]));
											f._padding.innerCt._right.setValue(parseInt(sides[0]));
											f._padding.innerCt._bottom.setValue(parseInt(sides[0]));
											f._padding.innerCt._left.setValue(parseInt(sides[0]));
											break;
										case 2:
											f._padding.innerCt._top.setValue(parseInt(sides[0]));
											f._padding.innerCt._right.setValue(parseInt(sides[1]));
											f._padding.innerCt._bottom.setValue(parseInt(sides[0]));
											f._padding.innerCt._left.setValue(parseInt(sides[1]));
											break;
										case 3:
											f._padding.innerCt._top.setValue(parseInt(sides[0]));
											f._padding.innerCt._right.setValue(parseInt(sides[1]));
											f._padding.innerCt._bottom.setValue(parseInt(sides[2]));
											f._padding.innerCt._left.setValue(0);
											break;
										default: //4 or more
											f._padding.innerCt._top.setValue(parseInt(sides[0]));
											f._padding.innerCt._right.setValue(parseInt(sides[1]));
											f._padding.innerCt._bottom.setValue(parseInt(sides[2]));
											f._padding.innerCt._left.setValue(parseInt(sides[3]));
									}
									break;
								case 'font-family':
									f._font_family.setValue(rec.get('value'));
									break;
								case 'font-size':
									m = rec.get('value').match(/(\d+)px/i);
									if (m) f._font_size.setValue(m[1]);
									break;
								case 'line-height':
									//console.log(m);
									//f._line_height.setValue(m[1]);
									f._line_height.setValue(rec.get('value'));
									/*
									m = rec.get('value').match(/((\d+)px|normal)/i);
									if (m) {
										alert(m[1]);
										
									}*/
									break;
								case 'color':
									f._color.onSelect(f._color, rec.get('value'));
									break;
								case 'background-color':
									f._background_color.onSelect(f._background_color, rec.get('value'));
									break;
								case 'font-weight':
									f._font_weight.setValue(rec.get('value'));
									break;
								case 'font-style':
									f._font_style.setValue(rec.get('value'));
									break;
								case 'text-decoration':
									f._text_decoration.setValue(rec.get('value'));
									break;
								case 'text-align':
									f._text_align.setValue(rec.get('value'));
									break;
								case 'text-indent':
									m = rec.get('value').match(/(\d+)px/i);
									if (m) f._text_indent.setValue(m[1]);
									break;
								case 'text-transform':
									f._text_transform.setValue(rec.get('value'));
									break;
							}
						});
					}
				}
			};
			PC.dialog.styles.please_select_style_first = {
				xtype: 'panel',
				ref: '../../_please_select_style_first',
				title: PC.i18n.styles,
				bodyCssClass: 'x-border-layout-ct',
				padding: 20,
				bodyStyle: 'text-align:center;',
				id: 'please_select_style_first',
				html: dialog.ln.select_style_class
			};
			PC.dialog.styles.window = new PC.ux.Window({
				_site: PC.global.site,
				//closeAction: 'hide',
				modal: true,
				title: PC.i18n.styles,
				width: 780,
				height: 500,
				layout: 'border',
				border: false,
				items: [
					{	layout: 'hbox',
						region: 'center',
						layoutConfig: {
							align: 'stretch'
						},
						border: false,
						items: [
							{	ref: '../_grid',
								width: 310,
								xtype: 'editorgrid',
								bodyCfg: {
									cls: 'x-panel-body',
									style: 'border-right: 0'
								},
								bodyBorder: true,
								store: {
									xtype: 'arraystore',
									fields: ['_class', 'tag', 'style', 'locked'],
									//idIndex: 0,
									data: []
								},
								defaults: {
									sortable: false
								},
								columns: [
									{	header: PC.i18n.dialog.styles.cls,
										menuDisabled: true,
										width: 120,
										dataIndex: '_class',
										renderer: function(val, meta, rec, row, col, stor) {
											//stor.getAt(row).
											if (rec.data.locked) {
												val = '&nbsp; '+val;
												meta.attr = 'style="color:#aaa;"';
											}
											return val;
										},
										editor: {
											xtype: 'textfield',
											ref: '../../../../_class',
											validator: function(val) {
												var grd = PC.dialog.styles.window._grid;
												if (grd.selModel.getSelected().data.locked) return false;
												if (val.match(/ /)) return PC.i18n.dialog.styles.error.cls.whitespace;
												if (val.match(/^\s*$/)) return PC.i18n.dialog.styles.error.cls.empty;
												if (val.match(/[;:{}]/)) return PC.i18n.dialog.styles.error.cls.badchars;
												var id_idx = grd.store.findExact('_class', val.trim());
												if (id_idx != -1)
													if (grd.store.getAt(id_idx) != grd.selModel.getSelected())
														return PC.i18n.dialog.styles.error.cls.exists;
												return true;
											},
											listeners: {
												afterrender: function(ed) {
													ed.gridEditor.on('canceledit', function(ed, val, origval) {
														if (ed.record && !origval)
															PC.dialog.styles.window._grid.store.remove(ed.record);
													});
													if (Ext.isGecko)
														ed.gridEditor.on('startedit', function(be, val) {
															this.field.selectText();
														});
													ed.gridEditor.on('complete', function(ed, val, origval) {
														if (ed.record)
															ed.record.set('_class', val.trim());
													});
												}
											}
										}
									},
									{	header: PC.i18n.dialog.styles.tag,
										menuDisabled: true,
										width: 120,
										dataIndex: 'tag',
										renderer: function(val, meta, rec, row, col, stor) {
											if (rec.data.locked) {
												meta.attr = 'style="color:#aaa;"';
											}
											return val;
										},
										editor: {
											xtype: 'combo',
											ref: '../../../../_tag',
											mode: 'local',
											store: {
												xtype: 'arraystore',
												fields: ['tag', 'name'],
												idIndex: 0,
												data: [
													['', '('+PC.i18n.dialog.styles.any+')']
												]
											},
											valueField: 'tag',
											displayField: 'name',
											triggerAction: 'all',
											validator: function(val) {
												var grd = PC.dialog.styles.window._grid;
												if (grd.selModel.getSelected().data.locked) return false;
											},
											value: '',
											listeners: {
												render: function(cb) {
													var s = cb.getStore();
													Ext.iterate(PC.i18n.dialog.styles.tags, function(tag, name) {
														this.add(new this.recordType({
															tag: tag,
															name: tag+' ('+name+')'
														}));
													}, s);
													//s.add(new s.recordType({ tag:'div' , name:'div'  }));
													//s.add(new s.recordType({ tag:'span', name:'span' }));
												}
											}
										}
									}
								],
								tbar: {
									style: 'border-right:0',
									items: [
										{	ref: '../_add_btn',
											text: PC.i18n.add,
											iconCls: 'icon-add',
											handler: function() {
												var grd = PC.dialog.styles.window._grid;
												var rec = new grd.store.recordType({
													'_class': '',
													tag: '',
													style: '',
													locked: 0,
													all: 0
												});
												grd.store.add(rec);
												idx = grd.store.indexOf(rec);
												grd.getSelectionModel().selectRow(idx);
												grd.startEditing(idx, grd.getColumnModel().findColumnIndex('_class'));
											}
										},
										{	ref: '../_edit_btn',
											text: PC.i18n.edit,
											iconCls: 'icon-edit',
											disabled: true,
											hidden: true,
											handler: function() {
												var grd = PC.dialog.styles.window._grid;
												var sel = grd.getSelectionModel().getSelected();
												if (sel) {
													var idx = grd.store.indexOf(sel);
													grd.getSelectionModel().selectRow(idx);
													// determine column to edit
													var cm = grd.getColumnModel();
													var col = '_class';
													Ext.each(cm.config, function(c, idx, all) {
														col = c.dataIndex;
														return false;
													});
													if (grd._lastcol)
														col = grd._lastcol;
													grd.startEditing(idx, cm.findColumnIndex(col));
												}
											}
										},
										{	ref: '../_del_btn',
											text: PC.i18n.del,
											iconCls: 'icon-delete',
											disabled: true,
											handler: function() {
												Ext.MessageBox.show({
													title: PC.i18n.msg.title.confirm,
													msg: PC.i18n.msg.confirm_delete,
													buttons: Ext.MessageBox.YESNO,
													icon: Ext.MessageBox.WARNING,
													fn: function(clicked) {
														if (clicked == 'yes') {
															var grd = PC.dialog.styles.window._grid;
															grd.getSelectionModel().each(function(rec) {
																if ((rec.data.tag == 'tr' || rec.data.tag == 'td') && rec.data.locked) {
																	var tag_class = rec.data._class.substr(0, rec.data._class.length-3);
																	rec = grd.store.getAt(grd.store.findExact('_class', tag_class));
																	grd.store.remove(rec);
																	//tag of 'table' type deleted, so we need to delete tr and td records also
																	//remove td record
																	grd.store.removeAt(grd.store.findExact('_class', rec.data._class +' td'));
																	//remove tr record
																	grd.store.removeAt(grd.store.findExact('_class', rec.data._class +' tr'));
																} else {
																	grd.store.remove(rec);
																	if (rec.data.tag == 'table') {
																		//tag of 'table' type deleted, so we need to delete tr and td records also
																		//remove td record
																		grd.store.removeAt(grd.store.findExact('_class', rec.data._class +' td'));
																		//remove tr record
																		grd.store.removeAt(grd.store.findExact('_class', rec.data._class +' tr'));
																	}
																}
															});
														}
													}
												});
											}
										}
									]
								},
								selModel: new Ext.grid.RowSelectionModel({
									moveEditorOnEnter: false,
									singleSelect: true,
									listeners: {
										selectionchange: function(sm) {
											var w = dialog.window;
											var tabs = w._tabpanel;
											if (w._form == undefined) {
												tabs.hideTabStripItem(w._please_select_style_first);
												tabs.insert(0, PC.dialog.styles.form);
												tabs.setActiveTab(0);
												return;
											}
											if (!sm.getSelected()) {
												tabs.unhideTabStripItem(w._please_select_style_first);
												tabs.hideTabStripItem(w._form);
												tabs.setActiveTab(w._please_select_style_first);
											}
											else {
												tabs.hideTabStripItem(w._please_select_style_first);
												tabs.unhideTabStripItem(w._form);
												tabs.setActiveTab(w._form);
											}
											//return;
											//remove "please select style first" overlay and render form
											/*if (!PC.dialog.styles._overlay_not_first) {
												PC.dialog.styles._overlay_not_first = true;
												alert('its first style select!');
												var tabs = PC.dialog.styles.window._tabpanel;
												var panel = tabs.getItem('please_select_style_first');
												if (panel) tabs.remove(panel);
												tabs.insert(0, PC.dialog.styles.form);
												return;
											}*/
											var w = PC.dialog.styles.window;
											w._rec = w._grid.getSelectionModel().getSelected();
											w._preview._update_rec();
											w._adv.getStore().loadData([]);
											w._grid._edit_btn.setDisabled(!w._rec);
											w._grid._del_btn.setDisabled(!w._rec);
											w._adv._add_btn.disable();
											w._adv._edit_btn.disable();
											w._adv._del_btn.disable();
											if (w._rec) {
												var css = w._rec.get('style');
												var prop_rx = /([^:;\s]+)\s*:\s*(([^\"\':;{}]|(["'])[^\4]*\4)*)(;|$)/gim;
												var m;
												while (m = prop_rx.exec(css)) {
													w._adv.store.addSorted(new w._adv.store.recordType({
														property: m[1],
														value: m[2].trim()
													}));
												}
												w._adv._add_btn.enable();
											}
											w._form._load();
										}
									}
								}),
								listeners: {
									containerclick: function(g, e) {
										if (e.target == g.view.scroller.dom)
											this.getSelectionModel().clearSelections();
									},
									containerdblclick: function(g, e) {
										if (e.target == g.view.scroller.dom)
											g._add_btn.handler();
									},
									keypress: function(e) {
										var grd = PC.dialog.styles.window._grid;
										if (e.getKey() === e.INSERT) grd._add_btn.handler();
										if (e.getKey() === e.ENTER) grd._edit_btn.handler();
										if (e.getKey() === e.F2) {
											var sel = grd.getSelectionModel().getSelected();
											if (sel) {
												var idx = grd.store.indexOf(sel);
												grd.getSelectionModel().selectRow(idx);
												grd.startEditing(grd.store.indexOf(sel), grd.getColumnModel().findColumnIndex('property'));
											}
										}
										if (e.getKey() === e.DELETE) grd._del_btn.handler();
									},
									beforeedit: function(ev) {
										var grid = ev.grid;
										grid._lastcol = ev.field;
										//do not allow to edit locked records (substyles)
										var tag = grid.store.getAt(ev.row).data.tag;
										if (ev.record.data.locked) {
											return false;
										}
									},
									afteredit: function(ev) {
										var grid = ev.grid;
										var record = ev.record;
										if (ev.field == 'tag') {
											//if tag was changed to the 'table', we need to create locked tr and td records
											if (ev.value == 'table') {
												if (ev.originalValue != 'table') {
													//add tr tag
													var rec = new grid.store.recordType({
														'_class': record.data._class +' tr',
														tag: 'tr',
														style: '',
														all: 0,
														locked: 1
													});
													grid.store.insert(ev.row+1, [rec]);
													//add td tag
													rec = new grid.store.recordType({
														'_class': record.data._class +' td',
														tag: 'td',
														style: '',
														all: 0,
														locked: 1
													});
													grid.store.insert(ev.row+2, [rec]);
												}
											} else if (ev.originalValue == 'table') {
												//tag was changed from 'table' to another tag type, so we need to delete unneeded tr and td records
												//remove td record
												grid.store.removeAt(grid.store.findExact('_class', record.data._class +' td'));
												//remove tr record
												grid.store.removeAt(grid.store.findExact('_class', record.data._class +' tr'));
											}
										} else if (ev.field == '_class') {
											if (record.data.tag == 'table') {
												//rename tr and td tag classes respectively
												var rec = grid.store.getAt(grid.store.findExact('_class', ev.originalValue +' td'));
												rec.data._class = ev.value +' td';
												grid.store.fireEvent('update', grid.store, rec, Ext.data.Record.EDIT);
												rec = grid.store.getAt(grid.store.findExact('_class', ev.originalValue +' tr'));
												rec.data._class = ev.value +' tr';
												grid.store.fireEvent('update', grid.store, rec, Ext.data.Record.EDIT);
											}
										}
									}
								}
							},
							{	ref: '../_tabpanel',
								xtype: 'tabpanel',
								flex: 1,
								activeTab: 0,
								items: [
									//style parameters
									PC.dialog.styles.please_select_style_first,
									{	xtype: 'editorgrid',
										title: PC.i18n.dialog.styles.advanced,
										ref: '../../_adv',
										border: false,
										store: {
											xtype: 'arraystore',
											fields: ['property', 'value'],
											idIndex: 0,
											data: []
										},
										columns: [
											{
												header: PC.i18n.dialog.styles.property,
												width: 120,
												dataIndex: 'property',
												sortable: true,
												menuDisabled: true,
												editor: {
													xtype: 'textfield',
													//completeOnEnter: false,
													validator: function(val) {
														if (val.match(/^\s*$/)) return PC.i18n.dialog.styles.error.prop.empty;
														if (val.match(/[;:{}]/)) return PC.i18n.dialog.styles.error.prop.badchars;
														var grd = PC.dialog.styles.window._adv;
														var id_idx = grd.store.findExact('property', val.trim());
														if (id_idx != -1)
															if (grd.store.getAt(id_idx) != grd.selModel.getSelected())
																return PC.i18n.dialog.styles.error.prop.exists;
														return true;
													},
													listeners: {
														afterrender: function(ed) {
															ed.gridEditor.on('canceledit', function(ed, val, origval) {
																if (ed.record && !origval)
																	PC.dialog.styles.window._adv.store.remove(ed.record);
															});
															if (Ext.isGecko)
																ed.gridEditor.on('startedit', function(be, val) {
																	this.field.selectText();
																});
															ed.gridEditor.on('complete', function(ed, val, origval) {
																if (ed.record)
																	ed.record.set('property', val.trim());
															});
														}
													}
												}
											},{
												header: PC.i18n.dialog.styles.value,
												width: 120,
												dataIndex: 'value',
												sortable: false,
												menuDisabled: true,
												editor: {
													xtype: 'textfield',
													//completeOnEnter: false,
													validator: function(val) {
														if (!val.match(/^(([^\"\':;{}]|(["'])[^\3]*\3)+)*[\s;]*$/)) return PC.i18n.dialog.styles.error.value.badchars;
														return true;
													},
													listeners: {
														afterrender: function(ed) {
															if (Ext.isGecko)
																ed.gridEditor.on('startedit', function(be, val) {
																	this.field.selectText();
																});
															ed.gridEditor.on('complete', function(ed, val, origval) {
																if (ed.record)
																	ed.record.set('value', val.trim().replace(/[\s;]+$/, ''));
															});
														}
													}
												}
											}
										],
										tbar: [
											{
												text: PC.i18n.add,
												iconCls: 'icon-add',
												ref: '../_add_btn',
												disabled: true,
												handler: function() {
													if (!PC.dialog.styles.window._rec) return;
													var grd = PC.dialog.styles.window._adv;
													var rec = new grd.store.recordType({
														property: '',
														value: ''
													});
													grd.store.add(rec);
													idx = grd.store.indexOf(rec);
													grd.getSelectionModel().selectRow(idx);
													grd.startEditing(idx, grd.getColumnModel().findColumnIndex('property'));
												}
											},{
												text: PC.i18n.edit,
												iconCls: 'icon-edit',
												ref: '../_edit_btn',
												disabled: true,
												hidden: true,
												handler: function() {
													var grd = PC.dialog.styles.window._adv;
													var sel = grd.getSelectionModel().getSelected();
													if (sel) {
														var idx = grd.store.indexOf(sel);
														grd.getSelectionModel().selectRow(idx);
														// determine column to edit
														var cm = grd.getColumnModel();
														var col = 'property';
														Ext.each(cm.config, function(c, idx, all) {
															col = c.dataIndex;
															return false;
														});
														if (grd._lastcol)
															col = grd._lastcol;
														grd.startEditing(idx, cm.findColumnIndex(col));
													}
												}
											},{
												text: PC.i18n.del,
												iconCls: 'icon-delete',
												ref: '../_del_btn',
												disabled: true,
												handler: function() {
													Ext.MessageBox.show({
														title: PC.i18n.msg.title.confirm,
														msg: PC.i18n.msg.confirm_delete,
														buttons: Ext.MessageBox.YESNO,
														icon: Ext.MessageBox.WARNING,
														fn: function(clicked) {
															if (clicked == 'yes') {
																var grd = PC.dialog.styles.window._adv;
																grd.getSelectionModel().each(function(rec) {
																	grd.store.remove(rec);
																});
																grd._update_rec();
																PC.dialog.styles.window._form._load();
															}
														}
													});
												}
											}
										],
										selModel: new Ext.grid.RowSelectionModel({
											moveEditorOnEnter: false,
											singleSelect: false,
											listeners: {
												selectionchange: function(sm) {
													var n = sm.getCount();
													sm.grid._edit_btn.setDisabled(!n);
													sm.grid._del_btn.setDisabled(!n);
												}
											}
										}),
										listeners: {
											containerclick: function(g, e) {
												if (e.target == g.view.scroller.dom)
													this.getSelectionModel().clearSelections();
											},
											containerdblclick: function(g, e) {
												if (e.target == g.view.scroller.dom)
													g._add_btn.handler();
											},
											keypress: function(e) {
												var grd = PC.dialog.styles.window._adv;
												if (e.getKey() === e.INSERT) grd._add_btn.handler();
												if (e.getKey() === e.ENTER) grd._edit_btn.handler();
												if (e.getKey() === e.F2) {
													var sel = grd.getSelectionModel().getSelected();
													if (sel) {
														var idx = grd.store.indexOf(sel);
														grd.getSelectionModel().selectRow(idx);
														grd.startEditing(grd.store.indexOf(sel), grd.getColumnModel().findColumnIndex('property'));
													}
												}
												if (e.getKey() === e.DELETE) grd._del_btn.handler();
											},
											beforeedit: function(e) {
												var grd = PC.dialog.styles.window._adv;
												grd._lastcol = e.field;
											},
											afteredit: function(e) {
												e.grid._update_rec();
												PC.dialog.styles.window._form._load();
											}
										},
										_update_rec: function() {
											var w = PC.dialog.styles.window;
											if (w._rec) {
												var st = [];
												w._adv.getStore().each(function(rec) {
													st.push(rec.get('property')+':'+rec.get('value')+';');
												});
												var css = st.join(' ');
												w._rec.set('style', css);
												w._preview._update_rec();
											}
										}
									}
								]
							}
						]
					},
					{	ref: '_preview',
						region: 'south',
						split: true,
						height: 80,
						autoScroll: true,
						bodyCfg: {
							cls: 'x-panel-body',
							style: 'padding:8px;'
						},
						_update_rec: function() {
							var w = PC.dialog.styles.window;
							var h = '';
							if (w._rec) {
								var store = w._grid.getStore();
								//var tag = 'div';
								var styles = PC.utils.htmlspecialchars(w._rec.get('style'));
								var tag = w._rec.data.tag;
								//console.log(w._rec.data);
								var tag_class = w._rec.data._class;
								if (tag == 'img') {
									var update_str = '<img style="float:left;'+styles+'" src="images/test-img.jpg" alt="" />'
									+'<img style="float:right;'+styles+'" src="images/test-img-2.jpg" alt="" />'
									+'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin aliquet molestie tellus nec lobortis. Suspendisse a imperdiet nisl. Ut a magna non augue auctor varius. Sed mattis leo quis est viverra eget egestas nisi ornare. Phasellus a orci turpis. In hac habitasse platea dictumst. Ut adipiscing leo metus, eu commodo eros.'
									+'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas eget dignissim ligula. Quisque elementum pretium quam a aliquam.';
								}
								else if (tag == 'table' || tag == 'tr' || tag == 'td') {
									if ((tag == 'tr' || tag == 'td') && w._rec.data.locked) {
										tag_class = tag_class.substr(0, tag_class.length-3);
										styles = store.getAt(store.findExact('_class', tag_class));
										if (styles) {
											styles = styles.get('style');
										}
										else styles = '';
									}
									var table_td = false;
									var table_tr = false;
									Ext.each(store.data.items, function(r, i) {
										if (r.data._class == tag_class + ' tr') table_tr = r;
										else if (r.data._class == tag_class + ' td') table_td = r;
									});
									if (table_td && table_tr) {
										var tr_styles = PC.utils.htmlspecialchars(table_tr.get('style'));
										var td_styles = PC.utils.htmlspecialchars(table_td.get('style'));
										var update_str = ' <table style="'+styles+'">'
										+'<tr style="'+tr_styles+'">'
											+'<td style="'+td_styles+'">Lorem ipsum</td>'
											+'<td style="'+td_styles+'">consectetur adipiscing elit.</td>'
											+'<td style="'+td_styles+'">dolor sit amet</td>'
											+'<td style="'+td_styles+'">Proin aliquet molestie tellus</td>'
										+'</tr>'
										+'<tr style="'+tr_styles+'">'
											+'<td style="'+td_styles+'">dolor sit amet</td>'
											+'<td style="'+td_styles+'">Lorem ipsum</td>'
											+'<td style="'+td_styles+'">Proin aliquet molestie tellus</td>'
											+'<td style="'+td_styles+'">consectetur adipiscing elit.</td>'
										+'</tr>'
										+'</table>';
									}
								}
								else if (tag == 'p') {
									var update_str = '<p style="'+styles+'">' +
									PC.utils.htmlspecialchars(PC.i18n.dialog.styles.pangram) +
									'</p>';
									//triple paragraphs
									var update_str = update_str + update_str + update_str;
								}
								else {
									var update_str = '<div style="'+styles+'">' +
									PC.utils.htmlspecialchars(PC.i18n.dialog.styles.pangram) +
									'</div>';
								}
								w._preview.update(update_str);
							} else
								w._preview.update('');
						}
					}
				],
				buttons: [
					{xtype: 'tbfill'},
					{	ref: '../_ok_btn',
						text: PC.i18n.save,
						icon: 'images/disk.png',
						disabled: true,
						handler: function() {
							var w = PC.dialog.styles.window;
							var s = w._grid.getStore();
							var o = [];
							s.each(function(rec) {
								o.push([rec.get('_class'), rec.get('tag'), rec.get('style'), rec.get('all'), rec.get('locked')]);
							});
							Ext.Ajax.request({
								url: 'ajax.styles.php',
								params: {
									site: w._site,
									styles: Ext.encode(o)
								},
								method: 'POST',
								callback: function(opts, success, rspns) {
									if (success && rspns.responseText) {
										try {
											var data = Ext.decode(rspns.responseText);
											//w.close(); setTimeout(function(){PC.dialog.styles.show()}, 150);
											//return; //mock aplinka
											
											//PC.global.page = {};
											//PC.admin._editor_ln_select.disable();
											//PC.global.pid = 0;
											
											//PC.admin.restartTinyMCEs();
											var save_save_prompt = save_prompt();
											if (save_save_prompt) {
												reload_admin();
											}
											
										
											//w.close();
											return; // OK
										} catch(e) {};
									}
									Ext.MessageBox.show({
										title: PC.i18n.error,
										msg: PC.i18n.msg.error.data.save,
										buttons: Ext.MessageBox.OK,
										icon: Ext.MessageBox.ERROR
									});
								}
							});
						}
					},
					{	text: PC.i18n.close,
						handler: function() {
							PC.dialog.styles.window.close();
						}
					}
				],
				listeners: {
					destroy: function() {
						delete PC.dialog.styles.window;
					}
				},
				_load_form: function() {
					var w = PC.dialog.styles.window;
				}
			});
		}
		PC.dialog.styles.window.show();
		Ext.Ajax.request({
			url: 'ajax.styles.php',
			params: {site: PC.global.site},
			method: 'POST',
			callback: function(opts, success, rspns) {
				var w = PC.dialog.styles.window;
				if (!w) return;
				if (success && rspns.responseText) {
					try {
						var data = Ext.decode(rspns.responseText);
						// *** LOAD DATA ***
						var s = w._grid.getStore();
						//console.log(data);
						s.loadData(data);
						w._ok_btn.enable();
						return; //OK
					} catch(e) {};
				}
				Ext.MessageBox.show({
					title: PC.i18n.error,
					msg: PC.i18n.msg.error.data.load,
					buttons: Ext.MessageBox.OK,
					icon: Ext.MessageBox.ERROR
				});
				w.close();
			}
		});
	},
	getSelected: function() {
		return this.window._grid.selModel.getSelected();
	}
};