Ext.ns('PC.dialog');
PC.dialog.search = {
	show: function() {
		this.ln = PC.i18n.dialog.search;
		this.bookmark = tinymce.activeEditor.selection.getBookmark('simple');
		var dialog = this;
		this.form = {
			ref: '_f',
			layout: 'form',
			flex: 1,
			padding: '6px 3px 3px 3px',
			border: false,
			autoScroll: true,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 80,
			labelAlign: 'right',
			defaults: {anchor: '94%'},
			items: [
				{	ref: '_findwhat',
					fieldLabel: this.ln.find_what,
					xtype: 'textfield',
					listeners: {
						
					}
				},
				{	ref: '_replacement',
					hidden: true,
					fieldLabel: this.ln.replace_with,
					xtype: 'textfield',
					listeners: {
						
					}
				},
				{	fieldLabel: this.ln.direction,
					xtype: 'compositefield',
					border: false,
					defaults: {
						width: 80
					},
					items: [
						{	id: 'search-direction',
							boxLabel: this.ln.up,
							xtype: 'radio', name: 'direction',
							listeners: {
								check: function(cb, state) {}
							}
						},
						{	boxLabel: this.ln.down,
							xtype: 'radio', name: 'direction', checked: true,
							listeners: {
								check: function(cb, state) {}
							}
						}
					]
				},
				{	ref: '_cs',
					boxLabel: this.ln.match_case,
					xtype: 'checkbox',
					listeners: {
						check: function(cb, state) {}
					}
				}
			]
		};
		this.tabs = new Ext.TabPanel({
			activeTab: 0,
			items: [{id:'search-tab-find',title: this.ln.find,height:0},{id:'search-tab-replace',title: this.ln.replace,height:0}],
			border: false,
			listeners: {
				tabchange: function(tp, tab){
					if (tab.id == 'search-tab-replace') {
						dialog.window._f._replacement.show();
						dialog.window._replace.show();
						dialog.window._replaceall.show();
					}
					else {
						dialog.window._f._replacement.hide();
						dialog.window._replace.hide();
						dialog.window._replaceall.hide();
					}
				}
			}
		});
		this.window = new PC.ux.Window({
			title: this.ln.title,
			layout: 'vbox',
			layoutConfig: {
				align: 'stretch'
			},
			width: 400, height: 200,
			resizable: false,
			border: false,
			items: [this.tabs, this.form],
			buttonAlign: 'left',
			buttons: [
				{	ref: '../_findnext',
					text: this.ln.find_next,
					handler: function() {
						dialog.search_next('none');
					}
				},
				{	ref: '../_replace', hidden: true,
					text: this.ln.replace,
					handler: function() {
						dialog.search_next('current');
					}
				},
				{	ref: '../_replaceall', hidden: true,
					text: this.ln.replace_all,
					handler: function() {
						dialog.search_next('all');
					}
				},
				{xtype: 'tbfill'},
				{	ref: '../_cancel',
					text: Ext.Msg.buttonText.cancel,
					handler: function() {
						dialog.window.close();
					}
				}
			]
		});
		this.window.show();
	},
	search_next: function(a) {
		var ed = tinymce.activeEditor, se = ed.selection, r = se.getRng(), f, m = this.lastMode, s, b, fl = 0, w = ed.getWin(), wm = ed.windowManager, fo = 0;

		// Get input
		f = this.window._f;
		s = f._findwhat.getValue();
		b = document.getElementById('search-direction').checked;
		ca = f._cs.getValue();
		rs = f._replacement.getValue();

		if (tinymce.isIE) {
			tinymce.activeEditor.selection.moveToBookmark(this.bookmark);
			r = ed.getDoc().selection.createRange();
		}

		if (s == '')
			return;

		function fix() {
			// Correct Firefox graphics glitches
			r = se.getRng().cloneRange();
			ed.getDoc().execCommand('SelectAll', false, null);
			se.setRng(r);
		};

		function replace() {
			if (tinymce.isIE)
				ed.selection.getRng().duplicate().pasteHTML(rs); // Needs to be duplicated due to selection bug in IE
			else
				ed.getDoc().execCommand('InsertHTML', false, rs);
		};

		// IE flags
		if (ca)
			fl = fl | 4;
		var replaced = 0;
		switch (a) {
			case 'all':
				// Move caret to beginning of text
				ed.execCommand('SelectAll');
				ed.selection.collapse(true);

				if (tinymce.isIE) {
					while (r.findText(s, b ? -1 : 1, fl)) {
						r.scrollIntoView();
						r.select();
						replace(); replaced++;
						fo = 1;

						if (b) {
							r.moveEnd("character", -(rs.length)); // Otherwise will loop forever
						}
					}
					//tinyMCEPopup.storeSelection();
				} else {
					while (w.find(s, ca, b, false, false, false, false)) {
						replace(); replaced++;
						fo = 1;
					}
				}

				if (fo) {
					Ext.Msg.show({
					   title: this.ln.title,
					   msg: this.ln.replaced+': '+ replaced,
					   buttons: Ext.Msg.OK,
					   animEl: this.window._replaceall.el,
					   icon: Ext.MessageBox.INFO
					});
				}
				else {
					Ext.Msg.show({
					   title: this.ln.title,
					   msg: this.ln.not_found,
					   buttons: Ext.Msg.OK,
					   animEl: f._findwhat,
					   icon: Ext.MessageBox.INFO
					});
				}
				this.bookmark = tinymce.activeEditor.selection.getBookmark('simple');
				return;

			case 'current':
				if (!ed.selection.isCollapsed())
					replace();
				this.bookmark = tinymce.activeEditor.selection.getBookmark('simple');
				break;
		}

		se.collapse(b);
		r = se.getRng();

		if (tinymce.isIE) {
			r = ed.getDoc().selection.createRange();
		}

		// Whats the point
		if (!s)
			return;

		if (tinymce.isIE) {
			if (r.findText(s, b ? -1 : 1, fl)) {
				r.scrollIntoView();
				r.select();
			} else {
				Ext.Msg.show({
				   title: this.ln.title,
				   msg: this.ln.not_found,
				   buttons: Ext.Msg.OK,
				   animEl: f._findwhat,
				   icon: Ext.MessageBox.INFO
				});
			}
			//tinyMCEPopup.storeSelection();
		} else {
			if (!w.find(s, ca, b, false, false, false, false)) {
				Ext.Msg.show({
				   title: this.ln.title,
				   msg: this.ln.not_found,
				   buttons: Ext.Msg.OK,
				   animEl: f._findwhat,
				   icon: Ext.MessageBox.INFO
				});
			}
			else
				fix();
		}
		this.bookmark = tinymce.activeEditor.selection.getBookmark('simple');
	}
};