Ext.namespace('PC.ux');

PC.ux.LocalCrud = Ext.extend(PC.ux.crud, {
	
	auto_load: false,
	per_page: false,
	
	max_id: 0,
	
	get_grid: function () {
		var plugins = [];
		var store =  this.get_store();
		var columns = this.get_grid_columns();

		var config = {
			store: store,
			sm: this.get_grid_selection_model(),
			plugins: plugins,
			columns: columns,
			listeners: this.get_grid_listeners()
		};
		Ext.apply(config, this.get_grid_config());
		if (this.grid_id) {
			config.id = this.grid_id;
		}
		
		this.grid = new Ext.grid.GridPanel(config);
		//this.grid = new Ext.list.ListView(config);
		this.grid.pc_crud = this;
		return this.grid;
	},
	
	get_store: function(){
		this.store = new Ext.data.JsonStore({
			autoLoad: false,
			//remoteSort: (this.per_page)?true:false,
			root: 'list',
			totalProperty: 'total',
			idProperty: 'id',
			fields: this.get_store_fields()
		});
		return this.store;
	},
	
	
	edit_button_handler: function(data, renameWindow, renameDialog) {
		this.form_data = data;
		this.form_field_container = this.edit_window = renameWindow;
		Ext.apply(this.edit_record.data, data.other);
		this.edit_record.commit();
		this.edit_window.close();
		
	}
	
});