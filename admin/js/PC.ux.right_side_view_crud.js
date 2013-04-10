PC.ux.right_side_view_crud = Ext.extend(PC.ux.crud, {
	width: 810,
	view_width: 300,
	layout: 'hbox',
	layoutConfig: {
		align: 'stretch'
	},
	
	get_view_xtemplate_empty: function() {
		return [
			'<strong>' + 'Choose item' + ':</strong>'
		]
	},
	
	get_view_xtemplate: function() {
		return [
			'<strong>' + 'Item {id}' + '</strong>'
		]
	},
	
	get_view_xtemplate_functions: function() {
		return {
			non_empty_array: function(value) {
				if (value.length) {
					return true
				}
				return false;
			}
		}
	},
	
	get_view_panel: function() {
		var ln = this.ln;
		var tpl = new Ext.XTemplate(
			[	'<tpl if="show_details==\'no\'">'].concat(
					this.get_view_xtemplate_empty(),
				'</tpl>',
				'<tpl if="show_details==\'yes\'">',
					this.get_view_xtemplate(),
				'</tpl>'
			)
		);
		Ext.apply(tpl, this.get_view_xtemplate_functions());
		this.view_panel = new Ext.BoxComponent({
			padding: '6px 6px 6px 0',
			bodyCssClass: 'x-border-layout-ct',
			split: true,
			border: false,
			width: this.view_width,
			//html: '<span>My Content</span>',
			//tpl: dialog.orders.xtemplate,
			tpl: tpl,
			data: {show_details: 'no'}
		})
		return this.view_panel;
	},
	
	fill_form_fields_: function(record) {
		Ext.iterate(this.edit_form.items.items, function(field) {
			if (field.name) {
				field.setValue(record.data[field.name]);
			}
		})
	},
	
	get_cell_dblclick_handler_: function() {
		
	},
	
	get_grid_config: function() {
		var config = PC.ux.right_side_view_crud.superclass.get_grid_config.call();
		config.flex = 1;
		return config;
	},
	
	get_items: function() {
		return [
			this.get_grid(),
			this.get_view_panel()
		];
	},
	
	update_buttons: function(select_length, sel_model) {
		PC.ux.right_side_view_crud.superclass.update_buttons.call(this, select_length);
		if (select_length == 1) {
			var data = sel_model.getSelected().data;
			data.show_details = 'yes';
			this.view_panel.update(data);
			this.view_panel.el.unmask();
		}
		else {
			var data = {};
			data.show_details = 'no';
			this.view_panel.update(data);
			this.view_panel.el.mask();
		}
	}
	
});