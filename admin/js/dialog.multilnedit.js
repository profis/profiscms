Ext.namespace('PC.dialog');

PC.dialog.multilnedit = {
	show: function(o) {
		this.o = o;
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
					value: o.values.hasOwnProperty(ln) ? o.values[ln] : '',
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
				/*tbar: [
					{	ref: '../_ok_btn',
						text: PC.i18n.save,
						icon: 'images/disk.png',
						handler: this.Save
					},
					{	text: PC.i18n.mod.dictionary.translate,
						iconCls: 'icon-google',
						handler: function() {
							if (google.language)
								PC.dialog.multilnedit.google_translate();
							else
								google.load('language', '1', {callback: PC.dialog.multilnedit.google_translate});
						}
					}
				],*/
				/*buttons: [
					{
						text: Ext.Msg.buttonText.ok,
						ref: '../_ok_btn',
						handler: function() {
							
						}
					},{
						text: Ext.Msg.buttonText.cancel,
						handler: function() {
							PC.dialog.styles.multilnedit.close();
						}
					}
				],*/
				listeners: {
					beforerender: function(dialog) {
						PC.hooks.Init('dialog.multilnedit.beforerender', {
							dialog: dialog,
							data: o,
							create_mode: o.create_mode,
							node: o.node
						});
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
			Ext.apply(cfg, o);
			PC.dialog.styles.multilnedit = new Ext.Window(cfg);
			PC.dialog.styles.multilnedit.show();
		}
	},
	Save: function() {
		var d = PC.dialog.multilnedit;
		var w = PC.dialog.styles.multilnedit;
		if (typeof d.o.callback == 'function') {
			var vals = {content: {}};
			w.items.each(function(i) {
				if (i._ln != undefined) vals['content'][i._ln] = { name: i.getValue() };
				else {
					if (i._fld != undefined) {
						//identify type of getting value
						if (typeof i.getFieldValue == 'function') {
							var val = i.getFieldValue();
						}
						else var val = i.getValue();
						//identify field and save values
						if (i._subfld != undefined) {
							vals[i._fld][i._subfld] = val;
						}
						else {
							vals[i._fld] = val;
						}
					}
				}
			});
			if (d.o.callback(vals, w)) w.close();
		} else
			alert('callback is not defined');
	},
	google_translate: function() {
		var translate_from = '';
		var translate_from_ln = '';
		Ext.each(PC.dialog.styles.multilnedit.items.items, function(item) {
			var value = item.getValue();
			if (value != '' && value != undefined) {
				translate_from = item.getValue();
				translate_from_ln = item._ln;
				return false;
			}
		});
		Ext.each(PC.dialog.styles.multilnedit.items.items, function(item) {
			var value = item.getValue();
			if (item._ln != translate_from_ln && (value == '' || value == undefined)) {
				google.language.translate(translate_from, translate_from_ln, item._ln, function(result) {
					if (result.translation) {
						item.setValue(result.translation);
					}
				});
			}
		});
	}
};