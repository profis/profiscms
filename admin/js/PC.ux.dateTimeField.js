Ext.namespace('PC.ux');

PC.ux.dateTimeField = Ext.extend(Ext.form.CompositeField, {
	
	
	constructor: function(config) {
		if (!config) {
			config = {};
		}
		config.items = [
			{xtype: 'hidden', width     : 20},
			{xtype: 'datefield', ref: '_date_from', format: 'Y-m-d', width     : 100},
			{xtype: 'timefield', ref: '../_time_from', formas: 'H:i', width     : 100}
		];
		PC.ux.dateTimeField.superclass.constructor.call(this, config);
	},
			
	getValue: function() {
		return this.items.items[1].getRawValue() + ' ' + this.items.items[2].getRawValue();
	},
		
	setValue: function(value) {
		var values = value.split(' ');
		if (values.length == 2) {
			this.items.items[1].setRawValue(values[0]);
			this.items.items[2].setValue(values[1]);
		}
	},		
			
	isDirty: function() {
		return;
		var dirty = false;
		debugger;
		return dirty;
	}
	
});

Ext.ComponentMgr.registerType('pc_date_time_field', PC.ux.dateTimeField);

