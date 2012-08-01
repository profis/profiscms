Ext.ns('PC.dialog');
PC.dialog.anchor = {
	show: function() {
		this.ln = PC.i18n.dialog.anchor;
		var dialog = this;
		this.form = {
			layout: 'form',
			border: false,
			padding: '6px 12px 0 3px',
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 80,
			labelAlign: 'right',
			defaults: {anchor:'100%', xtype:'textfield'},
			items: [
				{	ref: '../_anchor',
					fieldLabel: this.ln.anchor_field
				}
			]
		};
		this.window = new Ext.Window({
			title: this.ln.title,
			layout: 'vbox',
			layoutConfig: {
				align: 'stretch'
			},
			width: 300,
			height: 100,
			resizable: false,
			border: false,
			items: this.form,
			buttonAlign: 'left',
			buttons: [
				{xtype:'tbfill'},
				{	ref: '../_action',
					text: this.ln.insert,
					handler: function() {
						var ed = dialog.editor, elm, name = dialog.window._anchor.getValue();
						if (dialog.action != 'update')
							ed.selection.collapse(1);
						dialog.editor.selection.moveToBookmark(dialog.bookmark);
						elm = ed.dom.getParent(ed.selection.getNode(), 'A');
						if (elm)
							elm.name = name;
						else
							ed.execCommand('mceInsertContent', 0, ed.dom.createHTML('a', {name : name, 'class' : 'mceItemAnchor'}, ''));
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
		var ed = this.editor = tinymce.activeEditor;
		var elm = ed.dom.getParent(ed.selection.getNode(), 'A');
		var v = ed.dom.getAttrib(elm, 'name');
		if (v) {
			this.action = 'update';
			this.window.setTitle(this.ln.title_update);
			this.window._action.setText(this.ln.update);
			this.window._anchor.setValue(v);
		}
		this.bookmark = ed.selection.getBookmark('simple');
	}
};