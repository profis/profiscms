Ext.ns('Ext.ux');
Ext.ux.TabTitleEditor = Ext.extend(Object, {
    init: function(c){
        c.on({
            render: this.onRender,
            destroy: this.onDestroy,
            single: true
        });
    },
    onRender: function(c){
        c.titleEditor = new Ext.Editor(new Ext.form.TextField({
            allowBlank: false,
            enterIsSpecial: true
        }), {
            autoSize: 'width',
            completeOnEnter: true,
            cancelOnEsc: true,
            listeners: {
                complete: function(editor, value){
                    var item = this.getComponent(editor.boundEl.id.split(this.idDelimiter)[1]);
                    item.setTitle(value);
                },
                scope: this
            }
        });
        c.mon(c.strip, {
			mousedown: function(e) {
				if (this.titleEditor.editing)
					this.titleEditor.completeEdit();
			},
            dblclick: function(e){
                var t = this.findTargets(e);
                if(t && t.item && !t.close && t.item.titleEditable !== false){
                    this.titleEditor.startEdit(t.el, t.item.title);
					this.titleEditor.field.focus(true);
                } else {
					this.fireEvent('tabspacedblclick', e, this);
				}
            },
            scope: c
        });
		// profis stuff
		c.startEditActive = function() {
			var t = this.getTabEl(this.activeTab);
			if (t && this.activeTab.titleEditable !== false) {
				this.titleEditor.startEdit(t, this.activeTab.title);
				this.titleEditor.field.focus(true);
			}
		};
		// event proxies
		c.titleEditor.on('canceledit', function(ed, val, startval) {
			c.fireEvent('canceledit', c, ed, val, startval);
		});
		c.titleEditor.on('complete', function(ed, val, startval) {
			c.fireEvent('complete', c, ed, val, startval);
		});
    },
    onDestroy: function(c){
        if(c.titleEditor){
            c.titleEditor.destroy();
            delete c.titleEditor;
        }
    }
});
Ext.preg('tabtitleedit', Ext.ux.TabTitleEditor);