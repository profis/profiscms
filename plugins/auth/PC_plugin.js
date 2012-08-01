//config
var module_name = 'auth';
var api = PC.global.BASE_URL +'plugins/'+ module_name +'/PC_api.php';
//localization
/*PC.utils.localize('mod.'+ module_name, {
	en: {
		no_date: 'Without date'
	},
	lt: {
		no_date: 'Be datos'
	},
	ru: {
		no_date: 'Без даты'
    }
});*/
/** Auth permissions
 * Test editor (Dev mode)
 */
PC.auth.perms.editors.Register(module_name, 'groups', {
	data: {
		Load: function(window, data) {
			if (data._test != undefined) window._test.setValue(data._test);
			if (data._test2 != undefined) window._test2.setValue(data._test2);
			if (data._test3 != undefined) window._test3.setValue(data._test3);
		},
		Get: function(window) {
			var data = {};
			data._test = window._test.getValue();
			data._test2 = window._test2.getValue();
			data._test3 = window._test3.getValue();
			return data;
		}
	},
	window: {
		Get: function(perm_data) {
			var config = {
				items: [
					{	ref: '_test',
						fieldLabel: '_test'
					},
					{	ref: '_test2',
						fieldLabel: '_test2'
					},
					{	ref: '_test3',
						fieldLabel: '_test3'
					}
				]
			};
			return config;
		}
	}
});
/** Core permissions
 * Plugin access editor
 */
PC.auth.perms.editors.Register('core', 'plugins', {
	data: {
		Load: function(window, data) {
			//uncheck all checkboxes
			Ext.iterate(PC.global.plugins, function(plugin){
				if (window['_plugin_'+ plugin[1]] != undefined) {
					window['_plugin_'+ plugin[1]].setValue(false);
				}
			});
			//check active
			if (data.access != undefined) {
				Ext.iterate(data.access, function(plugin_name){
					if (window['_plugin_'+ plugin_name] != undefined) {
						window['_plugin_'+ plugin_name].setValue(true);
					}
				});
			}
		},
		Get: function(window) {
			var data = {};
			var active = [];
			Ext.iterate(PC.global.plugins, function(plugin){
				if (window['_plugin_'+ plugin[1]] != undefined) {
					if (window['_plugin_'+ plugin[1]].getValue()) {
						active.push(plugin[1]);
					}
				}
			});
			return {access:active};
		}
	},
	window: {
		Get: function(perm_data) {
			var items = [];
			Ext.iterate(PC.global.plugins, function(plugin){
				items.push({
					ref: '_plugin_'+ plugin[1],
					boxLabel: plugin[2]
				});
			});
			var config = {
				width: 250,
				hideLabels: true,
				defaults: {
					xtype: 'checkbox'
				},
				items: items
			};
			return config;
		}
	}
});