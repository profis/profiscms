Ext.namespace('PC.ux');

PC.ux.FlagCombo = function(config) {
	Ext.applyIf(config, {
		tpl: '<tpl for="."><div class="x-combo-list-item"><div class="x-flag-combo-icon" style="background-position:{[PC.utils.getFlagOffsets(values.'+config.valueField+')]}"></div><div class="x-flag-off">{'+config.displayField+'}</div></div></tpl>'
	});
	// call parent constructor
	PC.ux.FlagCombo.superclass.constructor.call(this, config);
	
	this.on({
		render: {scope:this, fn:function() {
			var wrap = this.el.up('div.x-form-field-wrap');
			wrap.applyStyles({position:'relative'});
			this.el.addClass('x-flag-combo-input');
			this.flag = Ext.DomHelper.insertFirst(wrap, {
				tag: 'div', cls:'x-flag-combo-icon'
			});
		}}
	});
};

Ext.extend(PC.ux.FlagCombo, Ext.form.ComboBox, {
	setIconCls: function() {
		if (this.flag)
			this.flag.style.backgroundPosition = PC.utils.getFlagOffsets(this.getValue());
	},
	setValue: function(value) {
		PC.ux.FlagCombo.superclass.setValue.call(this, value);
		this.setIconCls();
	}
});

Ext.ComponentMgr.registerType('profis_flagcombo', PC.ux.FlagCombo);