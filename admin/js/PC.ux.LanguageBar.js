Ext.namespace('PC.ux');

PC.ux.LanguageBar = function(config) {
	//call parent constructor
	PC.ux.LanguageBar.superclass.constructor.call(this, config);
};

Ext.extend(PC.ux.LanguageBar, Ext.Toolbar, {
	listeners: {
		afterrender: function(cmp) {
			cmp.Reload(cmp);
		}
	},
	Reload: function(cmp) {
		if (cmp == undefined) var cmp = this;
		cmp.removeAll();
		var toggleGroup = 'editor_language_'+ cmp.id;
		var langs = Get_site()[3];
		Ext.each(langs, function(lang){
			/* kam cia sitas reikalingas? (po saito kalbu pakeitimo kad is naujo butu Is_dirty?)
			if (PC.global.page != undefined) {
				var store = PC.global.page[lang[0]];
				if (store != undefined) delete store;
			}*/
			cmp.addButton(new Ext.Button({
				allowDepress: false,
				text: '<div style="position: relative;"><div class="x-flag-combo-icon" style="background-position:'+PC.utils.getFlagOffsets(lang[0])+'"></div><div style="padding-left:22px">'+lang[1]+'</div></div>',
				toggleGroup: toggleGroup,
				languageCode: lang[0],
				pressed: (lang[0] == PC.global.ln),
				listeners: {
					click: function(button, ev) {
						PC.editors.Fill(null, button.languageCode);
					}
				}
			}));
		});
	}
});

Ext.ComponentMgr.registerType('pc_languagebar', PC.ux.LanguageBar);
