Ext.namespace('PC.ux');

PC.ux.crudField = Ext.extend(Ext.form.CompositeField, {
	
	crud: false,
	
	
	constructor: function(config) {
		if (!config) {
			config = {};
		}
		config.items = [{xtype     : 'hidden', width     : 20}, config.crud];
		PC.ux.crudField.superclass.constructor.call(this, config);
	},
			
	getValue: function() {
		return this.crud.get_store_data(true);
	},
			
	isDirty: function() {
		var dirty = false;
		debugger;
		return dirty;
	}
	
});

Ext.ComponentMgr.registerType('profis_crud_field', PC.ux.crudField);

