Ext.ns('PC.dialog');
PC.dialog.mergetablecells = {
	_init_wrapping: true,
	show: function(callback) {
                this.ln = PC.i18n.dialog.mergetablecells;
		this.ln_tables = PC.i18n.dialog.tables;
		var dialog = this;
		this.window = new PC.ux.Window({
			title: this.ln.title,
                        layout: 'form',
                        padding: '6px 9px 0 3px',
                        labelAlign: 'right',
			width: 200, 
                        //height: 130,
			resizable: false,
			maximizable: false,
			//border: false,
                        defaults: {anchor: '100%', xtype:'textfield', width: 30},
			items: [
                            {
                                fieldLabel: this.ln_tables.cols,
                                ref: '_cols',
                                value: 1
                            },
                            {
                                fieldLabel: this.ln_tables.rows,
                                ref: '_rows',
                                value: 1
                            }
	
                        ],
			buttons: [
				{xtype: 'tbfill'},
				{	text: Ext.Msg.buttonText.ok,
					handler: function() {
                                            var data = {};
                                            data.cols = dialog.window._cols.getValue();
                                            data.rows = dialog.window._rows.getValue();
                                            callback(data);
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
	}
};