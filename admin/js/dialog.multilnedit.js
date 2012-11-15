Ext.namespace('PC.dialog');

PC.dialog.multilnedit = {
	show: function(params) {
		this.params = params;
		var dialog = this;
		if (!PC.dialog.styles.multilnedit) {
			var flds = [];
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
			if (typeof params.fields == 'object') if (params.fields != null) {
				var flds = flds.concat(params.fields);
			}
			var cfg = {
				width: 300,
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
				bbar: [
					{xtype:'tbfill'},
					{	text: PC.i18n.save,
						icon: 'images/disk.png',
						handler: this.Save
					}
				]
			};
			Ext.apply(cfg, params);
			PC.dialog.styles.multilnedit = new PC.ux.Window(cfg);
			PC.dialog.styles.multilnedit.show();
		}
	},
	Save: function() {
		var d = PC.dialog.multilnedit;
		var w = PC.dialog.styles.multilnedit;
		if (typeof d.params.Save == 'function') {
			var data = {names: {}, other: {}};
			w.items.each(function(i){
				if (i._ln != undefined) {
					data['names'][i._ln] = i.getValue();
					return true;
				}
				if (i._fld != undefined) {
					//identify type of getting value
					if (typeof i.getFieldValue == 'function') {
						var val = i.getFieldValue();
					}
					else var val = i.getValue();
					//identify field and save values
					if (i._subfld != undefined) {
						data['other'][i._fld][i._subfld] = val;
					}
					else {
						data['other'][i._fld] = val;
					}
				}
			});
			if (d.params.Save(data, w, d)) w.close();
		}
		else alert('No handler for data saving is defined');
	}
};