Ext.namespace('PC.ux');

PC.ux.LnCombo = function(config) {
	var site = config.site;
	var ln = config.ln;
	if (!site) {
		site = PC.global.site;
	}
	if (!ln) {
		ln = PC.global.ln;
	}
	Ext.applyIf(config, {
		fieldLabel: PC.i18n.show_menu_in,
		labelStyle: 'font-weight:bold;',
		anchor: '100%',
		mode: 'local',
		store: {
			xtype: 'arraystore',
			fields: ['ln_id', 'ln_name'],
			idIndex: 0,
			data: PC.global.site_select.getStore().getById(site).get('langs') // fix me
		},
		displayField: 'ln_name',
		valueField: 'ln_id',
		editable: false,
		forceSelection: true,
		value: ln,
		triggerAction: 'all'
	});
	// call parent constructor
	PC.ux.LnCombo.superclass.constructor.call(this, config);
};

Ext.extend(PC.ux.LnCombo, PC.ux.FlagCombo, {
	//
});

Ext.ComponentMgr.registerType('profis_lncombo', PC.ux.LnCombo);