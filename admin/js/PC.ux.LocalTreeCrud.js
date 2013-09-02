Ext.namespace('PC.ux');

PC.ux.LocalTreeCrud = Ext.extend(Ext.Panel, {
	layout: {
        type: 'hbox',
        align: 'stretch'
    },
	tree_width: 300,
	
	max_node_id: 0,
	
	//autoHeight: true,
	//height: 300,
    constructor: function(config) {
		this.ln = this.get_ln();
		/*
		this.ln = this.get_ln();

		if (config.api_url) {
			this.api_url = config.api_url;
		}

		if (config.ln) {
			Ext.apply(this.ln, config.ln);
			delete config.ln;
		}
		*/
		var tbar = this.get_tbar();
		var items = this.get_items(config);
		var my_config = Ext.apply({
			tbar: tbar,
			items: items
        }, config);
		
        PC.ux.LocalTreeCrud.superclass.constructor.call(this, my_config);
		
		//this.set_titles();
    },
	
	get_ln: function() {
		return PC.i18n.pc_ux_crud;
	},
	
	button_handler_for_add: function() {
		
		this._add_window = new PC.ux.Window({
			modal: true,
			//title: 'Window title',
			//closeAction: 'hide',
			width: 400,
			//height: 400,
			//layout: 'fit',
			layoutConfig: {
				align: 'stretch'
			},
			items: this.get_add_form()
		});
		
		this._add_window.show();
	},
	
	button_handler_for_del: function() {
		Ext.MessageBox.show({
			buttons: Ext.MessageBox.YESNO,
			title: this.ln._delete.confirm_title,
			msg: this.ln._delete.confirm_message,
			icon: Ext.MessageBox.WARNING,
			maxWidth: 320,
			fn: Ext.createDelegate(this.button_handler_for_del_submit, this)
		});
	},
	
	button_handler_for_del_submit: function(btn_id) {
		if (btn_id == 'yes' && this._edit_node) {
			var delNode = this.tree.selModel.selNode;
			this.tree.selModel.selNode.parentNode.removeChild(delNode);
			this.tree_selection_change(false);
		}
	},
	
	button_handler_for_edit_submit: function() {
		var values = this.edit_form.getForm().getValues();
		if (this._edit_node && values.name != '') {
			this._edit_node.attributes._data = values;
			this._edit_node.setText(values.name);
		}
	},
	
	button_handler_for_add_submit: function() {
		var values = this.add_form.getForm().getValues();
		if (values.name != '') {
			this.max_node_id++;
			var newNode = new Ext.tree.TreeNode({
				id: this.max_node_id, 
				text: values.name, 
				leaf: true
			});
			newNode.attributes._data = values;
			this.tree.getRootNode().appendChild(newNode);
			this._add_window.close();
		}
	},
	
	get_button_for_add: function() {
		return {	
			ref: '../action_add',
			text: (this.ln && this.ln.button._add)?this.ln.button._add:PC.i18n.add,
			icon: 'images/add.png',
			handler: Ext.createDelegate(this.button_handler_for_add, this)
		};
	},
	
	get_button_for_del: function() {
		return {	
			ref: '../action_del',
			text: (this.ln && this.ln.button._delete)?this.ln.button._delete:PC.i18n.del,
			icon: 'images/delete.png',
			handler: Ext.createDelegate(this.button_handler_for_del, this),
			disabled: true
		};
	},
	
	get_tbar: function () {
		return [
			this.get_button_for_add(),
			this.get_button_for_del()
		];
	},
	
	get_items: function(config) {
		return [
			this.get_tree(config.json),
			this.get_edit_form()
		];
	},
	
	tree_selection_change: function (node) {
		if (node && node.id) {
			if (node.id != this._edit_id) {
				this._edit_node = node;
				this._edit_id = node.id;
				this.fill_form_fields(node);
				this.edit_form.el.unmask();
				this.action_del.enable();

			}
			
		}
		else {
			this._edit_node = false;
			this._edit_id = false;
			this.edit_form.getForm().reset();
			this.edit_form.el.mask();
			this.action_del.disable();
		}
	},
	
	get_max_node: function(children) {
		var max_node_id = this.max_node_id;
		Ext.each(children, function(node_object, index) {
			if (node_object.id && node_object.id > max_node_id) {
				max_node_id = node_object.id;
			}
		});
		return max_node_id;
	},
	
	get_tree: function(children) {
		
		this.max_node_id = this.get_max_node(children);
		
		this.tree = new Ext.tree.TreePanel( {
			//height: 200,
			width: this.tree_width,
			animate:true,
			enableDD:true,
			loader: new Ext.tree.TreeLoader(), // Note: no dataurl, register a TreeLoader to make use of createNode()
			root: new Ext.tree.AsyncTreeNode({
				text: 'root',
				draggable:false,
				id:'source',
				children: children
			}),
			rootVisible:false,
			listeners: {
				afterrender: function() {
					this.expandAll();
				},
				click: Ext.createDelegate(this.tree_selection_change, this)
			}
		});
		
		return this.tree;
	},
	
	set_children: function(children) {
		this.max_node_id = this.get_max_node(children);
		var node = new Ext.tree.AsyncTreeNode({
			text: 'root',
			draggable:false,
			id:'source',
			children: children
		});
		this.tree.setRootNode(node);
	},
	
	get_add_form_fields: function() {
		return [];
	},
	
	get_edit_form_fields: function(data) {
		var fields = this.get_add_form_fields(true);
		if (data) {
			Ext.each(fields, function(field) {
				if (data[field._fld]) {
					field.value = data[field._fld];
				}
			});
		}
		return fields;
	},
	
	fill_form_fields: function(record) {
		Ext.iterate(this.edit_form.items.items, function(field) {
			if (field.name) {
				field.setValue(record.attributes._data[field.name]);
			}
		});
	},
	
	get_edit_form: function() {
		var items = this.get_edit_form_fields();
		this.edit_form = new Ext.form.FormPanel({
			ref: '_f',
			//width: this.form_width,
			flex: 1,
			layout: 'form',
			padding: 6,
			border: false,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 100,
			labelAlign: 'right',
			defaults: {xtype: 'textfield', anchor: '100%'},
			items: items,
			frame: true,
			buttonAlign: 'center',
			buttons: [
				{	text: PC.i18n.save,
					iconCls: 'icon-save',
					ref: '../../_btn_save',
					handler: Ext.createDelegate(this.button_handler_for_edit_submit, this)
				}
			],
			listeners: {
				afterrender: function(form) {
					form.el.mask();
				}
			}
		});
		return this.edit_form;
	},
	
	get_add_form: function() {
		this.add_form = new Ext.form.FormPanel({
			ref: '_f',
			//width: this.form_width,
			flex: 1,
			layout: 'form',
			padding: 6,
			border: false,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 100,
			labelAlign: 'right',
			defaults: {xtype: 'textfield', anchor: '100%'},
			items: this.get_add_form_fields(),
			frame: true,
			buttonAlign: 'center',
			buttons: [
				{	text: PC.i18n.save,
					iconCls: 'icon-save',
					ref: '../../_btn_save',
					handler: Ext.createDelegate(this.button_handler_for_add_submit, this)
				}
			]

		});
		return this.add_form;
	}
	
	
	
	
});