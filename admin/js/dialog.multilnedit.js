Ext.namespace('PC.dialog');

PC.dialog.multilnedit = {
	show: function(params) {
		this.params = params;
		var width = 300;
		if (params.window_width) {
			width = params.window_width;
		}
		var save_button_label = PC.i18n.save;
		if (params.save_button_label) {
			save_button_label = params.save_button_label;
		}
		var dialog = this;
		if (!PC.dialog.styles.multilnedit) {
			var flds = [];
			if (!this.params.no_ln_fields) {
				PC.global.ln_select.getStore().each(function(rec) {
					var ln = rec.get('ln_id');
					var i = {
						_ln: ln,
						//fieldLabel: ln.toUpperCase(),
						fieldLabel: rec.get('ln_name'),
						ref: '../_'+ln,
						anchor: '100%',
						value: (params.values!=undefined?(params.values.hasOwnProperty(ln) ? params.values[ln] : ''):''),
						listeners: {
							specialkey: function(fld, e) {
								if (e.getKey() == e.ENTER) {
									dialog.Save();
								}
							}
						}
					};
					if (ln == PC.global.ln) flds.unshift(i);
					else flds.push(i);
				});
			}
			
			if (typeof params.fields == 'object') if (params.fields != null) {
				var flds = flds.concat(params.fields);
			}
			var bbar_items = [{xtype:'tbfill'}];
			var buttons = [];
			buttons.push();
			if (params.pre_buttons) {
				buttons = params.pre_buttons;
			}
			buttons.push({	text: save_button_label,
				icon: 'images/disk.png',
				handler: this.Save
			});
			bbar_items = bbar_items.concat(buttons);
			var cfg = {
				width: width,
				modal: true,
				layout: 'form',
				labelWidth: 70,
				labelAlign: 'right',
				padding: '6px 6px 2px',
				defaultType: 'textfield',
				autoScroll: true,
				items: flds,
				listeners: {
					beforerender: function(dialog) {
						var hookParams = {
							dialog: dialog,
							data: params,
							create_mode: params.create_mode,
							node: params.node
						}
						if (params.node != undefined) hookParams.node = params.node;
						PC.hooks.Init('dialog.multilnedit.beforerender', hookParams);
					},
					show: function() {
						var first_field = this.items.items[0];
						first_field.focus(true);
						var refocused = false;
						first_field.addListener('blur', function(field){
							if (!refocused) {
								refocused = true;
								field.focus(true, 1);
							}
						});
					},
					destroy: function() {
						delete PC.dialog.styles.multilnedit;
					}
				},
				bbar: bbar_items
			};
			Ext.apply(cfg, params);
			PC.dialog.styles.multilnedit = new PC.ux.Window(cfg);
			if (params.center_window) {
				PC.dialog.styles.multilnedit.on('show',function(){
					PC.dialog.styles.multilnedit.center();
				});
				//PC.dialog.styles.multilnedit.render();
				//PC.dialog.styles.multilnedit.center();
			}
			PC.dialog.styles.multilnedit.show();
		}
		
	},
	Save: function() {
		var d = PC.dialog.multilnedit;
		var w = PC.dialog.styles.multilnedit;
		if (typeof d.params.Save == 'function') {
			var data = {names: {}, other: {}};
			var form_is_valid = true;
			w.items.each(function(i){
				//debugger;
				if (!i.isValid(false)) {
					form_is_valid = false;
				}
				if (i._ln != undefined) {
					data['names'][i._ln] = i.getValue();
					return true;
				}
				if (i._fld != undefined) {
					//identify type of getting value
					var val;
					if (typeof i.getFieldValue == 'function') {
						val = i.getFieldValue();
					}
					else {
						if (i._get_raw_value) {
							val = i.getRawValue();
						}
						else {
							val = i.getValue();
						}
						
					}
					//identify field and save values
					if (i._subfld != undefined) {
						data['other'][i._fld][i._subfld] = val;
					}
					else {
						data['other'][i._fld] = val;
					}
				}
			});
			//if (form_is_valid && d.params.Save(data, w, d)) w.close();
			if (form_is_valid) {
				if (d.params.Save && d.params.close_in_callback) {
					d.params.Save(data, w, d, function() {w.close();});
				}
				else if (d.params.Save && d.params.Save(data, w, d)) {
					w.close();
				}
				
			}
		}
		else alert('No handler for data saving is defined');
	}
};