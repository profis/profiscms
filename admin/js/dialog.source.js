Ext.ns('PC.dialog');
PC.dialog.source = {
	_init_wrapping: false,
	show: function() {
		this.ln = PC.i18n.dialog.source;
		var dialog = this;
		this.window = new PC.ux.Window({
			title: this.ln.title,
			modal: true, layout: 'border',
			width: 900, height: 500,
			resizable: true,
			maximizable: true,
			border: false,
			items: {
				ref: 'textarea',
				xtype: 'textarea',
				region: 'center',
				layout: 'fit',
				style: 'font-family:"Courier New",Courier,monospace;font-size:12px;line-height:0.5cm;',
				value: tinymce.activeEditor.getContent({source_view : true}),
				listeners: {
					render: function(textarea) {
						dialog.setWrap(dialog._init_wrapping);
					}
				}
			},
			tbar: new Ext.Toolbar({
				style: {padding:'4px'},
				items: [
					{	xtype: 'checkbox',
						boxLabel: this.ln.wrap,
						checked: this._init_wrapping,
						handler: function(cb, state) {
							dialog.setWrap(state);
						}
					}
				]
			}),
			buttons: [
				{xtype: 'tbfill'},
				{	text: Ext.Msg.buttonText.ok,
					handler: function() {
						tinymce.activeEditor.setContent(dialog.window.textarea.getValue(), {source_view: true});
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
	},
	setWrap: function(on) {
		if (on) {
			this.window.textarea.el.set({wrap:'soft'});
			this.window.textarea.el.setStyle({'white-space':'normal'});
		}
		else {
			this.window.textarea.el.set({wrap:'off'});
			this.window.textarea.el.setStyle({'white-space':'pre'});
		}
	}
};