Ext.ns('PC.view_factory', 'PC.view_factory');
PC.view_factory = {
	
	get_shortcut_field: function(config, params) {
		
		var shortcut_field_callback = false;
		if (params) {
			if (params.callback) {
				shortcut_field_callback = params.callback;
			}
		}
		
		var callback = function(value){
			field.setValue(value);
			if (shortcut_field_callback) {
				shortcut_field_callback(value, field);
			}
		};
		
		var field_config = {
			fieldLabel: PC.i18n.menu.shortcut_to.replace(/\s/, '&nbsp;'),
			ref: '../../../../../../_fld_redirect',
			id: 'db_fld_redirect',
			selectOnFocus: true,
			trigger1Class: 'x-form-folder-trigger',
			onTrigger1Click: function() {
				//console.log(PC.admin._editor_ln_select.get('db_fld_redirect'));
				var field = this;
				var page_selector_params = {
					callback: callback,
					select_node_path: this.getValue()
				}
				if (params && params.page_selector_params) {
					Ext.apply(page_selector_params, params.page_selector_params);
				}
				Show_redirect_page_window(callback, page_selector_params);
			},
			trigger2Class: 'x-form-remove-trigger',
			listeners: {
				afterrender: function(field) {
					//initialize drop target on this field
					new Ext.dd.DropTarget(field.el.dom, {
						ddGroup: 'tree_pages',
						notifyEnter: function(ddSource, e, data) {
							if (PC.tree.IsNodeDeleted(ddSource.dragData.node)) return ddSource.proxy.dropNotAllowed;
							if (ddSource.dragData.node.id == PC.global.pid) return ddSource.proxy.dropNotAllowed;
							return ddSource.proxy.dropAllowed;
						},
						notifyOver: function(ddSource, e, data) {
							return ddSource.proxy.dropStatus;
						},
						notifyDrop: function(ddSource, e, data) {
							if (PC.tree.IsNodeDeleted(ddSource.dragData.node)) return false; // deny from recycle bin
							if (ddSource.dragData.node.id == PC.global.pid) return false; // deny self
							field.setValue(ddSource.dragData.node.attributes.id);
							return true;
						}
					});
				},
				change: function(field, value, old) {
					if (value == PC.global.page.id.originalValue) {
						field.setValue('');
						alert('You cannot redirect this page to itself');
					}
				}
			}
		//},{
		//	fieldLabel: PC.i18n.last_update,
		//	xtype: 'textfield',
		//	ref: '../../../../../../_fld_last_update',
		//	readOnly: true,
		//	id: 'db_fld_last_update'
		};
		if (config) {
			Ext.apply(field_config, config);
		}
		field_config.onTrigger2Click = function() {
			Ext.getCmp(field_config.id).setRawValue('');
		};
		var field = new Ext.form.TwinTriggerField(field_config);
		return field;
	}
	
}


