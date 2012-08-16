PC.editors = {
	Default: 'page',
	Current: ['page', 'page'],
	Data: {},
	list: {},
	Defaults: {flex: 1},
	Get: function(ctrl, editor) {
		if (ctrl == undefined) {
			var ctrl = this.Current[0];
			var editor = this.Current[1];
		}
		else if (!editor) var editor = ctrl;
		
		if (!this.Exists(ctrl, editor)) return false;
		
		var id = this.FormatID(ctrl, editor);
		var cmp = Ext.getCmp(id);
		
		if (cmp) return cmp;
		else if (this.list[ctrl][editor] != undefined) {
			if (typeof this.list[ctrl][editor] == 'function') var params = this.list[ctrl][editor]();
			else var params = this.list[ctrl][editor];
			Ext.applyIf(params, this.Defaults);
			params.id = this.FormatID(ctrl, editor);
			return params;
		}
		//requested editor was not found in the list or its config was not specified
		return {items: [{xtype: 'panel', html: 'Editor <b>"'+ ctrl +'"</b> not available.'}]};
	},
	/**
	 * Params must include these options: config, Load(), Save(), IsDirty()
	 */
	Register: function(ctrl, editor, params) {
		if (params == undefined) return false;
		if (!editor) var editor = ctrl;
		if (this.list[ctrl] == undefined) this.list[ctrl] = {};
		else if (this.list[ctrl][editor] != undefined) return false;
		//this editor is not listed yet, so we can register it
		this.list[ctrl][editor] = params;
		return true;
	},
	FormatID: function(ctrl, editor) {
		if (ctrl == undefined) {
			var ctrl = this.Current[0];
			var editor = this.Current[1];
		}
		else if (!editor) var editor = ctrl;
		return 'pc_editor_'+ ctrl +'_'+ editor;
	},
	Exists: function(ctrl, editor) {
		if (ctrl == undefined) return false;
		if (!editor) var editor = ctrl;
		if (this.list[ctrl] == undefined) return false;
		return (this.list[ctrl][editor] != undefined);
	},
	IsCurrent: function(ctrl, editor) {
		if (ctrl == undefined) return false;
		if (!editor) var editor = ctrl;
		return (this.Current[0] == ctrl && this.Current[1] == editor);
	},
	SetCurrent: function(ctrl, editor) {
		if (ctrl == undefined) return false;
		if (!editor) var editor = ctrl;
		this.Current = [ctrl, editor];
		return true;
	},
	Change: function(ctrl, editor, callback) {
		if (ctrl == undefined) ctrl = this.Default;
		if (!editor) var editor = ctrl;
		if (!this.IsCurrent(ctrl, editor)) {
			var cmp = this.Get(ctrl, editor);
			//hide previous editor
			//var currentCmp = this.Get();
			//if (currentCmp) currentCmp.hide();
			//show requested editor
			var adminArea = Ext.getCmp('pc_editor_area');
			var id = this.FormatID(ctrl, editor);
			
			if (cmp.rendered !== true) {
				adminArea.add(cmp);
				adminArea.doLayout();
			}
			adminArea.layout.setActiveItem(id);
			this.SetCurrent(ctrl, editor);
		}
		//run callback if specified and return true
		if (typeof callback == 'function') callback();
		return true;
	},
	Load: function(data, freshLoad, callback) {
		if (data == undefined) return false;
		var id = PC.pages.ParseID(data.id);
		var ctrl = 'page';
		if (id.controller != undefined/* || data.controller != ''*/) {
			//if (id.controller != undefined) {
				var ctrl = id.controller;
			/*}
			else {
				var ctrl = data.controller;
			}*/
		}
		//identify editor
		var editor = undefined;
		if (PC.hooks.Count('core/editors/identify/'+ ctrl)) {
			var params = {
				id: id.id,
				ctrl: ctrl,
				data: data.data,
				editor: undefined
			}
			PC.hooks.Init('core/editors/identify/'+ ctrl, params);
			editor = params.editor;
		}
		if (!editor) var editor = ctrl;
		//change to identified editor
		var afterChange = function() {
			PC.editors.Fill(data, null, freshLoad, callback);
		}
		//call Unloader
		this.Unload(data);
		if (this.Exists(ctrl, editor)) var r = this.Change(ctrl, editor, afterChange);
		else var r = this.Change('page', 'page', afterChange);
		return r;
	},
	Unload: function(newData) {
		var params = {
			data: PC.editors.Data,
			newData: newData
		};
		PC.hooks.Init('core/editors/unload', params);
	},
	Fill: function(data, ln, freshLoad, callback) {
		if (ln == undefined || ln == null) var ln = PC.global.ln;
		if (data != undefined || data != null) this.Data = data;
		else if (this.Data == undefined) return false;
		var editor = this.Get();
		if (!freshLoad) this.Store();
		this.Clear();
		if (typeof editor.Load == 'function') {
			editor.Load(editor, this.Data, ln, freshLoad, callback);
			PC.global.ln = ln;
		}
		else if (typeof callback == 'function') callback();
	},
	Clear: function(editor) {
		if (editor == undefined) var editor = this.Get();
		if (typeof editor.Clear == 'function') return editor.Clear(editor);
		return false;
	},
	Store: function(callback) {
		var editor = this.Get();
		if (typeof editor.Store == 'function') {
			editor.Store(editor, this.Data.data, callback);
		}
		else if (typeof callback == 'function') callback(false);
	},
	Save: function(callback) {
		Ext.MessageBox.show({
			title: PC.i18n.msg.title.saving,
			msg: PC.i18n.msg.saving,
			width: 300,
			wait: true,
			waitConfig: {interval:100}
		});
		PC.editors.Store();
		var editor = PC.editors.Get();
		if (typeof editor.Save == 'function') {
			editor.Save(callback);
		}
		else if (typeof callback == 'function') callback(false);
		Ext.Msg.hide();
	}
}