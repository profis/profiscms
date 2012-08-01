Ext.namespace('PC.ux');

PC.ux.SiteCombo = function(config) {
	var data = PC.global.SITES;
	if (config.optionAll) data.unshift([0, PC.i18n.all]);
	Ext.applyIf(config, {
		fieldLabel: PC.i18n.site,
		anchor: '100%',
		mode: 'local',
		store: {
			xtype: 'arraystore',
			fields: ['id', 'name', 'dir', 'langs'],
			idIndex: 0,
			data: data
		},
		displayField: 'name',
		valueField: 'id',
		editable: false,
		forceSelection: true,
		value: PC.global.site,
		triggerAction: 'all'
	});
	// call parent constructor
	PC.ux.SiteCombo.superclass.constructor.call(this, config);
};

Ext.extend(PC.ux.SiteCombo, Ext.form.ComboBox, {
	//
});

Ext.ComponentMgr.registerType('profis_sitecombo', PC.ux.SiteCombo);
