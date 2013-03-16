PC.ux.right_side_crud = Ext.extend(PC.ux.crud, {
	width: 810,
	grid_width: 510,
	layout: 'hbox',
	layoutConfig: {
		align: 'stretch'
	},
	
	get_button_handler_for_save: function() {
		var handler = Ext.createDelegate(function() {
			if (!this.edit_record) {
			   return;
			}
			var data = {names: {}, other: {}};
			data.other = this.edit_form.getForm().getValues();
			
			Ext.Ajax.request({
				url: this.api_url + 'edit',
				method: 'POST',
				params: {id: this.edit_record.id, data: Ext.util.JSON.encode(data)},
				callback: this.ajax_add_respone_handler
			});
		}, this);
				
		return handler;
	},
	
	get_edit_form: function() {
		this.edit_form = new Ext.form.FormPanel({
			ref: '_f',
			flex: 1,
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
	
	get_grid_config: function() {
		var config = PC.ux.right_side_crud.superclass.get_grid_config.call();
		config.width = this.grid_width;
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