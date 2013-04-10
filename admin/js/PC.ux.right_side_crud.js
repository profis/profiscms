PC.ux.right_side_crud = Ext.extend(PC.ux.crud, {
	width: 810,
	form_width: 300,
	layout: 'hbox',
	layoutConfig: {
		align: 'stretch'
	},
	
	ajax_edit_response_success_handler: function (data, form_data) {
		//PC.ux.right_side_crud.superclass.ajax_add_response_success_handler.call(data, this);
		PC.ux.right_side_crud.superclass.ajax_edit_response_success_handler.defer(0, this, [data, form_data]);
		if (this.ln.update && this.ln.update.success) {
			Ext.MessageBox.show({
				//title: new_email,
				msg: this.ln.update.success,
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.INFO
			});
		}
		
	},
	
	get_button_handler_for_save: function() {
		var handler = Ext.createDelegate(function() {
			if (!this.edit_record) {
			   return;
			}
			if(!this.edit_form.getForm().isValid()){
				return;
			}
			var data = {names: {}, other: {}};
			data.other = this.edit_form.getForm().getValues();
			
			Ext.Ajax.request({
				url: this.api_url + 'edit',
				method: 'POST',
				params: {id: this.edit_record.id, data: Ext.util.JSON.encode(data)},
				callback: this.get_ajax_edit_response_handler()
			});
		}, this);
				
		return handler;
	},
	
	get_edit_form: function() {
		this.edit_form = new Ext.form.FormPanel({
			ref: '_f',
			width: this.form_width,
			layout: 'form',
			padding: 6,
			border: false,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 100,
			labelAlign: 'right',
			defaults: {xtype: 'textfield', anchor: '100%'},
			items: this.get_edit_form_fields(),
			frame: true,
			buttonAlign: 'center',
			buttons: [
				{	text: PC.i18n.save,
					iconCls: 'icon-save',
					ref: '../../_btn_save',
					handler: this.get_button_handler_for_save()
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
	
	fill_form_fields: function(record) {
		Ext.iterate(this.edit_form.items.items, function(field) {
			if (field.name) {
				field.setValue(record.data[field.name]);
			}
		})
	},
	
	get_cell_dblclick_handler: function() {
		
	},
	
	get_grid_config: function() {
		var config = PC.ux.right_side_crud.superclass.get_grid_config.call();
		config.flex = 1;
		return config;
	},
	
	get_items: function() {
		return [
			this.get_grid(),
			this.get_edit_form()
		];
	},
	
	update_buttons: function(select_length, sel_model) {
		PC.ux.right_side_crud.superclass.update_buttons.call(this, select_length);
		if (select_length == 1) {
			this.edit_record = sel_model.getSelected();
			this.fill_form_fields(this.edit_record);
			this.edit_form.el.unmask();
		}
		else {
			this.edit_record = false;
			this.edit_form.getForm().reset();
			this.edit_form.el.mask();
		}
	}
	
});