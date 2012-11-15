//dialog
Ext.ns('PC.dialog');
PC.dialog.colorpicker = {
	show: function(x, y, splitbutton) {
		this.ln = PC.i18n.dialog.colorpicker;
		this.bookmark = tinymce.activeEditor.selection.getBookmark('simple');
		this.splitbutton = splitbutton;
		var dialog = this;
		if (this.window) {
			this.window.show();
			this.window.setPagePosition(x, y);
			return;
		}
		this.colors = '000000,993300,333300,003300,003366,000080,333399,333333,800000,FF6600,808000,008000,008080,0000FF,666699,808080,FF0000,FF9900,99CC00,339966,33CCCC,3366FF,800080,999999,FF00FF,FFCC00,FFFF00,00FF00,00FFFF,00CCFF,993366,C0C0C0,FF99CC,FFCC99,FFFF99,CCFFCC,CCFFFF,99CCFF,CC99FF,FFFFFF'.split(',');
		this.window = new PC.ux.Window({
			pc_temp_window: true,
			width: 150,
			layout: 'form',
			bodyCssClass: 'x-border-layout-ct',
			style: 'padding:5px;',
			closeAction: 'hide',
			resizable: false,
			closable: false,
			draggable:false,
			shadow:false,
			border: false,
			defaults: {hideLabel:true},
			items: [
				{	xtype:'panel',
					id: 'color-picker-colours',
					bodyCssClass: 'x-border-layout-ct',
					border: false,
					style: 'margin-bottom:5px',
					listeners: {
						render: function(p) {
							var color = '';
							for (var i=0; dialog.colors[i]!=undefined; i++) {
								color = dialog.colors[i];
								p.update(p.body.dom.innerHTML +'<span class="color" _mce_color="#'+ color +'" style="background:#'+ color +'">&nbsp;</span>');
							}
							Ext.select('#color-picker-colours span').on('click', function(){
								tinymce.activeEditor.selection.moveToBookmark(dialog.bookmark);
								dialog.splitbutton.setColor(this.getAttribute('_mce_color'));
							});
						}
					}
				},
				{	ref: '_colorpicker',
					xtype: 'compositefield',
					defaults: {xtype:'textfield', width:100},
					items: [
						{	xtype: 'colorfield', ref: '_color',
							emptyText: this.ln.pick_custom,
							regex: /.*/,
							allowBlank: true,
							fieldLabel: this.ln.custom_color,
							value: '',
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
						{	icon: 'images/tick.png',
							width: 20,
							ref: '_setcolor',
							xtype: 'button',
							handler: function() {
								tinymce.activeEditor.selection.moveToBookmark(dialog.bookmark);
								var c = dialog.window._colorpicker.innerCt._color.getValue();
								dialog.splitbutton.setColor(c);
							}
						}
					]
				}
			],
			listeners: {
				deactivate: function(w){
					w.hide();
				}
			}
		});
		
		this.window.show();
		this.window.setPagePosition(x, y);
	}
};