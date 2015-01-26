
Plugin_site_users_crud = Ext.extend(PC.ux.right_side_crud, {
	api_url: 'api/plugin/site_users/users/',
	
	grid_id: 'Plugin_site_users_crud',
	
	auto_load: true,
	
	no_ln_fields: true,
	
	per_page: 20,
	
	get_store_: function() {
		var store = Plugin_site_users_crud.superclass.get_store.call(this);
		store.setDefaultSort('date_registered', 'DESC');
		return store;
	},
	
	Isset: function(confirmation) {
		if (typeof confirmation == 'string') {
			if (confirmation.length) return true;
		}
		return false;
	},
	
	Status_icon: function(id, n) {
		if (parseInt(n.banned)) {
			return '<img src="images/delete.png" alt="" />';
		}
		if (!this.Isset(n.confirmation)) {
			return '<img src="images/tick.png" alt="" />';
		}
		return '<img src="images/hourglass.png" alt="" />';
	},
	
	Time_to_date: function(time){
		return new Date(time*1000).format('Y-m-d H:i');
	},
	
	get_store_fields: function() {
		var fields = [
				'id', 'email', 'login', 'name', 'date_registered', 'last_seen', 'confirmation', 'banned', 'flags',
				{name: 'confirmed', mapping: 'confirmation', convert: this.Isset},
				{name: 'status', mapping: 'id', convert: Ext.createDelegate(this.Status_icon, this)},
				{name: '_date_registered', mapping: 'date_registered', convert: this.Time_to_date},
				{name: '_last_seen', mapping: 'last_seen', convert: this.Time_to_date}
		];

		for( var i = 0; i < PC_plugin_site_users_meta_fields.length; i++ ) {
			fields.push('meta_' + PC_plugin_site_users_meta_fields[i]);
		}

		return fields;
	},
	
	get_grid_columns: function() {
		var columns = [
			{header: '&nbsp;', dataIndex: 'status', width: 30, sortable: false},
			{header: this.ln.email, dataIndex: 'email', width: 150, sortable: true},
			{header: this.ln.login, dataIndex: 'login', width: 120, sortable: true},
			{header: this.ln.name, dataIndex: 'name', width: 120, sortable: true}
		];
		for( var i = 0; i < PC_plugin_site_users_meta_fields.length; i++ ) {
			columns.push({header: PC_plugin_site_users_meta_fields[i], dataIndex: 'meta_' + PC_plugin_site_users_meta_fields[i], sortable: false});
		}
		columns.push(
			{header: this.ln.date_registered, dataIndex: '_date_registered', sortable: true},
			{header: this.ln.last_login, dataIndex: '_last_seen', sortable: true}
		);
		return columns;
	},
	
	get_add_form_fields: function(edit_mode) {
		var allow_blank_if_edit = false;
		if (edit_mode) {
			allow_blank_if_edit = true;
		}
		var fields = [
			{	_fld: 'email',
				ref: '_email',
				name: 'email',
				fieldLabel: this.ln.email,
				allowBlank: false,
				vtype: 'email'
				//vtypeText: this.ln.error_incorrect_email
			},		
			{	_fld: 'login',
				ref: '_login',
				name: 'login',
				fieldLabel: this.ln.login,
				minLength: 4
			},
			{	_fld: 'name',
				ref: '_name',
				name: 'name',
				fieldLabel: this.ln.name
			}
		];
		for( var i = 0; i < PC_plugin_site_users_meta_fields.length; i++ ) {
			fields.push({
				_fld: 'meta_' + PC_plugin_site_users_meta_fields[i],
				ref: '_meta_' + PC_plugin_site_users_meta_fields[i],
				name: 'meta_' + PC_plugin_site_users_meta_fields[i],
				fieldLabel: PC_plugin_site_users_meta_fields[i]
			});
		}
		fields.push(
			{	_fld: 'password',
				ref: '_password',
				name: 'password',
				fieldLabel: this.ln.pass_new,
				inputType: 'password',
				allowBlank: allow_blank_if_edit
			},
			{	_fld: 'password2',
				ref: '_password2',
				name: 'password2',
				fieldLabel: this.ln.pass_repeat,
				inputType: 'password',
				allowBlank: allow_blank_if_edit,
				vtype: 'password_match',
				vtypeText : this.ln.passwords_doesnt_match
			},
			{	_fld: 'banned',
				ref: '_banned',
				name: 'banned',
				xtype: 'checkbox',
				fieldLabel: this.ln.banned,
				inputValue: 1
			}
		);

		return fields;
	}
}); 

