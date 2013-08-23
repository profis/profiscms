
PC.formEmails = Ext.extend(PC.ux.LocalCrud, {
	no_ln_fields: true,
	
	height: 200,
	
	get_store_fields: function() {
		return [
			'name', 'value', 'emails'
		];
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: 'Input name', dataIndex: 'name', width: 70},
			{header: 'Input value', dataIndex: 'value', width: 70},	
			{header: 'Emails', dataIndex: 'emails', width: 200}		
		];
	},
	
	get_grid_config: function() {
		var grid_config =PC.formEmails.superclass.get_grid_config.call(this);
		grid_config.height = 93;
		return grid_config;
	},
	
	get_add_form_fields: function() {
		return [
			{	_fld: 'name',
				name: 'name',
				fieldLabel: 'Input name',
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: false
			},
			{	_fld: 'value',
				name: 'value',
				fieldLabel: 'Input value',
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: false
			},
			{	_fld: 'emails',
				name: 'emails',
				fieldLabel: 'Emails',
				anchor: '100%',
				xtype: 'textarea',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: false
			}
		];
	}
}); 
//debugger;
