Ext.ns('PC.auth');
PC.auth = {};

var auth = PC.auth;

//Permissions
PC.auth.perms = {
	//Editors
	editors: {
		list: {},
		Register: function(plugin, name, config) {
			/* Plugins list is not loaded yet!
			if (PC.plugins[plugin] == undefined) return false;
			*/
			if (typeof config != 'object' || config == null) return false;
			if (typeof this.list[plugin] != 'object') {
				this.list[plugin] = {};
			}
			this.list[plugin][name] = config;
			return true;
		},
		Has: function(plugin, name) {
			if (plugin == '') plugin = 'core';
			if (this.list[plugin] == undefined) return false;
			if (this.list[plugin][name] == undefined) return false;
			return true;
		},
		Get: function(plugin, name) {
			if (plugin == '') plugin = 'core';
			if (!this.Has(plugin, name)) return false;
			return this.list[plugin][name];
		}
	}
}