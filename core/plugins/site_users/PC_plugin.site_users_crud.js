
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
		return [
				'id', 'email', 'login', 'name', 'date_registered', 'last_seen', 'confirmation', 'banned', 'flags',
				{name: 'confirmed', mapping: 'confirmation', convert: this.Isset},
				{name: 'status', mapping: 'id', convert: Ext.createDelegate(this.Status_icon, this)},
				{name: '_date_registered', mapping: 'date_registered', convert: this.Time_to_date},
				{name: '_last_seen', mapping: 'last_seen', convert: this.Time_to_date}
		];
	},
	
	get_grid_columns: function() {
		return [
			{header: '&nbsp;', dataIndex: 'status', width: 30},
			{header: this.ln.email, dataIndex: 'email', width: 150},
			{header: this.ln.login, dataIndex: 'login', width: 120},
			{header: this.ln.name, dataIndex: 'name', width: 120},
			{header: this.ln.date_registered, dataIndex: '_date_registered'},
			{header: this.ln.last_login, dataIndex: '_last_seen'}
		];
	},
	
	get_add_form_fields: function() {
		return [
			{	_fld: 'email',
				ref: '_email',
				name: 'email',
				fieldLabel: this.ln.email
			},		
			{	_fld: 'login',
				ref: '_login',
				name: 'login',
				fieldLabel: this.ln.login
			},
			{	_fld: 'name',
				ref: '_name',
				name: 'name',
				fieldLabel: this.ln.name
			},
			{	_fld: 'password',
				ref: '_pass1',
				name: 'password',
				fieldLabel: this.ln.pass_new,
				inputType: 'password'
			},
			{	_fld: 'password2',
				ref: '_pass2',
				name: 'password2',
				fieldLabel: this.ln.pass_repeat,
				inputType: 'password'
			},
			{	_fld: 'banned',
				ref: '_banned',
				name: 'banned',
				xtype: 'checkbox',
				fieldLabel: this.ln.banned,
				inputValue: 1
			}
		];
	}
}); 

