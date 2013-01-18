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





PC.hooks.Register('core/auth/get_perm_window/' + 'core', function(params) {
	params.show_window_after_data_load = true;
});


PC.auth.perms.editors.Register('core', 'page_nodes', {
	data: {
		Load: function(window, data) {
			PC.plugin.auth.core_page_access_permission_data = data;
			var checked_nodes = [];
			if (data && data.sites && data.sites[PC.global.site]) {
				Ext.iterate(data.sites[PC.global.site], function(cat){
					checked_nodes.push(cat);
				});
			}
			//window._tree.checked_nodes_id_string = 'pc_shop/category/';
			window._tree.checked_nodes = checked_nodes;
		},
		Get: function(window) {
			if (!PC.plugin.auth.core_page_access_permission_data.sites) {
				PC.plugin.auth.core_page_access_permission_data.sites = {};
			}
			if (!PC.plugin.auth.core_page_access_permission_data.sites[PC.global.site]) {
				PC.plugin.auth.core_page_access_permission_data.sites[PC.global.site] = []
			}
			PC.plugin.auth.core_page_access_permission_data.sites[PC.global.site] = window._tree.checked_nodes;
			return PC.plugin.auth.core_page_access_permission_data;
		}
	},
	window: {
		Get: function(perm_data) {
			var tree_params = {
				additionalBaseParams: {
					//plugin_only: 'pc_shop',
					pc_shop: {
						categories_only: true
					}
				}
			};
			var window_config = Show_redirect_page_window(false, {return_only_window_config: true, tree_params: tree_params});
			return window_config;
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